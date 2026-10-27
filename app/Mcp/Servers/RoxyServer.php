<?php

namespace App\Mcp\Servers;

use App\Mcp\Tools;
use Laravel\Mcp\Server;
use Laravel\Mcp\Server\Attributes\Instructions;
use Laravel\Mcp\Server\Attributes\Name;
use Laravel\Mcp\Server\Attributes\Version;

/** Feedback from client reviewers, for the agent that fixes it. Every call runs as the token's user. */
#[Name('Roxy')]
#[Version('0.1.0')]
#[Instructions(<<<'MD'
    Roxy collects feedback that clients and reviewers leave on websites through a widget: what they wrote, the page URL, the element they pointed at (CSS selector, HTML, text, position), the viewport, recent console errors, failed network requests and screenshots.

    Workflow:
    1. list_sites shows the sites you can reach and the repository each one is built from. Check that it matches the codebase you're working in.
    2. list_feedback (default: open) gives a summary per entry; get_feedback returns the full entry with screenshots as images.
    3. Find the code from the URL path (route / template) and the element selector, then fix it.
    4. resolve_feedback with a short note on what you changed. The note is written for the client, so keep it plain. Only resolve what you actually fixed; if something is unclear, leave it open and ask the user.
    5. discard_feedback deletes an entry for good (spam, duplicates). Confirm with the user first.
    MD)]
class RoxyServer extends Server
{
    protected array $tools = [
        Tools\ListSites::class,
        Tools\ListFeedback::class,
        Tools\GetFeedback::class,
        Tools\ResolveFeedback::class,
        Tools\DiscardFeedback::class,
    ];
}
