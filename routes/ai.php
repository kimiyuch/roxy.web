<?php

use App\Http\Middleware\EnsureMcpToken;
use App\Mcp\Servers\RoxyServer;
use Laravel\Mcp\Facades\Mcp;

// The agent's way in: a Sanctum token with the `mcp` ability, scoped to the user's account.
Mcp::web('/mcp', RoxyServer::class)
    ->middleware(['auth:sanctum', EnsureMcpToken::class, 'throttle:mcp']);
