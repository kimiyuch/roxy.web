<?php

namespace App\Http\Controllers;

use App\Actions\Sites\DeleteSite;
use App\Enums\FeedbackStatus;
use App\Http\Requests\SiteRequest;
use App\Models\Reviewer;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

/** Sites in the dashboard: list, add, settings, install snippet, delete. Reviewers: ReviewerController. */
class SiteController extends Controller
{
    /** The sites list on its own, nothing selected yet. */
    public function index(Request $request): Response
    {
        return $this->page($request);
    }

    /** The list with the "Add site" form next to it. */
    public function create(Request $request): Response
    {
        return $this->page($request, creating: true);
    }

    public function store(SiteRequest $request): RedirectResponse
    {
        $site = $request->user()->account->sites()->create($request->siteAttributes());

        return to_route('sites.show', $site)->with('status', "{$site->name} is ready. Put the script tag on the site, then invite your client.");
    }

    /** The list with one site open next to it: script tag, reviewers, settings. */
    public function show(Request $request, Site $site): Response
    {
        Gate::authorize('view', $site);

        return $this->page($request, $site);
    }

    public function update(SiteRequest $request, Site $site): RedirectResponse
    {
        $site->update($request->siteAttributes());

        return back()->with('status', 'Saved.');
    }

    public function destroy(Site $site, DeleteSite $delete): RedirectResponse
    {
        Gate::authorize('delete', $site);

        $delete->execute($site);

        return to_route('sites.index')->with('status', "{$site->name} and its feedback are deleted.");
    }

    private function page(Request $request, ?Site $selected = null, bool $creating = false): Response
    {
        $sites = $request->user()->account->sites()
            ->withCount(['reviewers', 'feedback as open_count' => fn ($query) => $query->where('status', FeedbackStatus::Open)])
            ->withMax('feedback', 'created_at')
            ->orderBy('name')
            ->get()
            ->map(fn (Site $site) => [
                'id' => $site->id,
                'name' => $site->name,
                'host' => parse_url($site->start_url, PHP_URL_HOST),
                'open_count' => $site->open_count,
                'reviewers_count' => $site->reviewers_count,
                'last_feedback_at' => $site->feedback_max_created_at ? Carbon::parse($site->feedback_max_created_at)->toIso8601String() : null,
            ]);

        return Inertia::render('sites/Index', [
            'sites' => $sites,
            'creating' => $creating,
            'selected' => $selected ? $this->detail($selected) : null,
            'reviewers' => $selected ? $this->reviewers($selected) : [],
        ]);
    }

    private function detail(Site $site): array
    {
        $origin = Site::originOf($site->start_url);

        return [
            'id' => $site->id,
            'name' => $site->name,
            'start_url' => $site->start_url,
            'origins' => array_values(array_diff($site->allowed_origins ?? [], [$origin])),
            'repository' => $site->repository,
            'position' => $site->settings['position'] ?? 'bottom-right',
            'color' => $site->settings['color'] ?? null,
            'script_tag' => $site->scriptTag(),
            'open_count' => $site->feedback()->where('status', FeedbackStatus::Open)->count(),
        ];
    }

    private function reviewers(Site $site): array
    {
        return $site->reviewers()
            ->withCount(['tokens', 'feedback'])
            ->orderBy('created_at')
            ->get()
            ->map(fn (Reviewer $reviewer) => [
                'id' => $reviewer->id,
                'name' => $reviewer->name,
                'email' => $reviewer->email,
                'browsers' => $reviewer->tokens_count,
                'feedback_count' => $reviewer->feedback_count,
                'last_seen_at' => $reviewer->last_seen_at?->toIso8601String(),
                'invited_at' => $reviewer->created_at->toIso8601String(),
                // For "Copy link": send it yourself when email isn't the way
                'link' => $reviewer->magicLink(),
            ])
            ->all();
    }
}
