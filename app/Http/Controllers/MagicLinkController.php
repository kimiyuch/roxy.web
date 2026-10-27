<?php

namespace App\Http\Controllers;

use App\Actions\Reviewers\IssueLoginCode;
use App\Models\Reviewer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * The magic link from the email (signed, see ReviewerMagicLink). Roxy can't write to the
 * client site's localStorage, so it sends the reviewer there with a one-time code in the
 * fragment; the widget picks it up and trades it for a token. The fragment never reaches
 * a server log or a Referer header.
 */
class MagicLinkController extends Controller
{
    public function __invoke(Request $request, Reviewer $reviewer, IssueLoginCode $issue): RedirectResponse
    {
        $site = $reviewer->site;
        $to = $request->query('to');

        $target = is_string($to) && $site->allowsOrigin($this->origin($to)) ? $to : $site->start_url;

        return redirect()->away(Str::before($target, '#').'#roxy='.$issue->execute($reviewer));
    }

    private function origin(string $url): ?string
    {
        $parts = parse_url($url);

        if (! isset($parts['scheme'], $parts['host'])) {
            return null;
        }

        return $parts['scheme'].'://'.$parts['host'].(isset($parts['port']) ? ':'.$parts['port'] : '');
    }
}
