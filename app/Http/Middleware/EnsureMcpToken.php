<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;
use Symfony\Component\HttpFoundation\Response;

/** The MCP server only takes real personal access tokens carrying the `mcp` ability, never a session. */
class EnsureMcpToken
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        abort_unless(
            $user?->currentAccessToken() instanceof PersonalAccessToken && $user->tokenCan('mcp'),
            Response::HTTP_FORBIDDEN,
        );

        return $next($request);
    }
}
