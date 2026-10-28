<?php

namespace App\Http\Controllers;

use App\Actions\Reviewers\InviteReviewer;
use App\Models\Reviewer;
use App\Models\Site;
use App\Notifications\ReviewerMagicLink;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

/** The people who give feedback on a site: invite, send the link again, remove. */
class ReviewerController extends Controller
{
    public function store(Request $request, Site $site, InviteReviewer $invite): RedirectResponse
    {
        Gate::authorize('update', $site);

        $input = $request->validate([
            'email' => ['required', 'email', 'max:255'],
            'name' => ['nullable', 'string', 'max:100'],
        ]);

        $reviewer = $invite->execute($site, $input['email'], $input['name'] ?? null);

        return back()->with('status', "Invitation sent to {$reviewer->email}.");
    }

    public function resend(Site $site, Reviewer $reviewer): RedirectResponse
    {
        Gate::authorize('update', $site);
        abort_unless($reviewer->site_id === $site->id, 404);

        $reviewer->notify(new ReviewerMagicLink(invited: true));

        return back()->with('status', "New link sent to {$reviewer->email}.");
    }

    /** Removes the reviewer and signs them out of every browser; their feedback stays. */
    public function destroy(Site $site, Reviewer $reviewer): RedirectResponse
    {
        Gate::authorize('update', $site);
        abort_unless($reviewer->site_id === $site->id, 404);

        $reviewer->delete();

        return back()->with('status', "{$reviewer->email} can no longer give feedback on {$site->name}.");
    }
}
