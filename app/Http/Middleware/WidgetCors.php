<?php

namespace App\Http\Middleware;

use App\Models\Site;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CORS for the widget API, per site: only the site's `allowed_origins` get an answer,
 * and preflights are handled here. Not a security boundary on its own (any non-browser
 * client can send any Origin); the reviewer token is.
 *
 * Resolves `{site}` by its public key itself, because it runs ahead of route model
 * binding and the rate limiters (see the priority list in bootstrap/app.php).
 */
class WidgetCors
{
    public function handle(Request $request, Closure $next): Response
    {
        $site = Site::where('key', (string) $request->route('site'))->first();

        if (! $site) {
            return response()->json(['message' => 'Unknown site.'], Response::HTTP_NOT_FOUND);
        }

        $request->route()->setParameter('site', $site);
        $origin = $request->headers->get('Origin');

        // Browsers send Origin on every cross-origin request, so a missing one means a
        // same-origin page (Roxy's own test page) or a non-browser client: no CORS needed.
        if ($origin === null) {
            return $request->isMethod('OPTIONS') ? response()->noContent() : $next($request);
        }

        if (! $site->allowsOrigin($origin)) {
            return response()->json(['message' => 'Origin not allowed.'], Response::HTTP_FORBIDDEN);
        }

        $response = $request->isMethod('OPTIONS')
            ? response()->noContent()->withHeaders([
                'Access-Control-Allow-Methods' => 'GET, POST, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Authorization, Content-Type, Accept',
                'Access-Control-Max-Age' => '7200',
            ])
            : $next($request);

        $response->headers->set('Access-Control-Allow-Origin', $origin);
        $response->headers->set('Vary', 'Origin', false);

        return $response;
    }
}
