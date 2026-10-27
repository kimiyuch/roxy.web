<?php

namespace App\Http\Middleware;

use App\Models\ReviewerToken;
use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the widget's Bearer token to a reviewer of the route's site and puts it
 * on the request as the `reviewer` attribute. A token for another site is a 401.
 */
class AuthenticateReviewer
{
    public function handle(Request $request, Closure $next): Response
    {
        /** @var Site $site */
        $site = $request->route('site');
        $plain = $request->bearerToken();

        $token = $plain
            ? ReviewerToken::with('reviewer')->where('token_hash', hash('sha256', $plain))->first()
            : null;

        if (! $token || $token->reviewer->site_id !== $site->id) {
            return response()->json(['message' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! $token->last_used_at || $token->last_used_at->lt(now()->subMinutes(5))) {
            $token->update(['last_used_at' => now()]);
            $token->reviewer->forceFill(['last_seen_at' => now()])->save();
        }

        $token->reviewer->setRelation('site', $site);
        $request->attributes->set('reviewer', $token->reviewer);
        $request->attributes->set('reviewer_token', $token);

        return $next($request);
    }
}
