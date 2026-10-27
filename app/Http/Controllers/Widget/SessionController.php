<?php

namespace App\Http\Controllers\Widget;

use App\Actions\Reviewers\ExchangeLoginCode;
use App\Http\Controllers\Controller;
use App\Models\Site;
use App\Notifications\ReviewerMagicLink;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

/** Logging the widget in and out: code exchange, magic link by email, logout. */
class SessionController extends Controller
{
    /** Trade the one-time code from the magic link (`#roxy=…`) for a widget token. */
    public function store(Request $request, Site $site, ExchangeLoginCode $exchange): JsonResponse
    {
        $input = $request->validate(['code' => ['required', 'string', 'max:100']]);

        $result = $exchange->execute($site, $input['code'], $request->userAgent());

        if (! $result) {
            return response()->json(['message' => 'This link has expired. Ask for a new one.'], 422);
        }

        return response()->json([
            'token' => $result['token'],
            'reviewer' => MeController::reviewer($result['reviewer']),
        ], 201);
    }

    /**
     * `?feedback` on any page: email a fresh magic link back to that page. Always 202,
     * so the form doesn't reveal who's a reviewer.
     */
    public function link(Request $request, Site $site): Response
    {
        $input = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'url' => ['nullable', 'url', 'max:2048'],
        ]);

        $reviewer = $site->reviewers()->where('email', Str::lower(trim($input['email'])))->first();

        $reviewer?->notify(new ReviewerMagicLink(to: $input['url'] ?? null));

        return response()->noContent(202);
    }

    public function destroy(Request $request): Response
    {
        $request->attributes->get('reviewer_token')->delete();

        return response()->noContent();
    }
}
