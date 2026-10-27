<?php

namespace App\Http\Controllers;

use App\Actions\Feedback\DeleteFeedback;
use App\Actions\Feedback\ResolveFeedback;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Inertia\Response;

class FeedbackController extends Controller
{
    /** The inbox: the list on the left, nothing selected yet. */
    public function index(Request $request): Response
    {
        return $this->inbox($request);
    }

    /** The inbox with one entry open next to the list. */
    public function show(Request $request, Feedback $feedback): Response
    {
        Gate::authorize('view', $feedback);

        return $this->inbox($request, $feedback);
    }

    public function resolve(Request $request, Feedback $feedback, ResolveFeedback $resolve): RedirectResponse
    {
        Gate::authorize('update', $feedback);

        $input = $request->validate([
            'resolved' => ['required', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $resolve->execute($feedback, $request->user(), $input['resolved'], $input['note'] ?? null);

        return back();
    }

    public function destroy(Request $request, Feedback $feedback, DeleteFeedback $delete): RedirectResponse
    {
        Gate::authorize('delete', $feedback);

        $delete->execute($feedback);

        return to_route('feedback.index', $request->only('site', 'status'));
    }

    private function inbox(Request $request, ?Feedback $selected = null): Response
    {
        $filters = $request->validate([
            'site' => ['nullable', 'string'],
            'status' => ['nullable', 'in:open,done'],
        ]);
        $status = $filters['status'] ?? 'open';
        $account = $request->user()->account;

        $base = Feedback::query()
            ->forAccount($account->id)
            ->when($filters['site'] ?? null, fn ($query, $site) => $query->where('site_id', $site));

        $feedback = (clone $base)
            ->where('status', $status)
            ->with(['site', 'reviewer'])
            ->withCount('screenshots')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('feedback/Index', [
            'feedback' => FeedbackResource::collection($feedback),
            'counts' => (clone $base)->toBase()->selectRaw('status, count(*) as total')->groupBy('status')->pluck('total', 'status'),
            'selected' => $selected ? new FeedbackResource($selected->load(['site', 'reviewer', 'resolvedBy', 'screenshots'])) : null,
            'filters' => ['site' => $filters['site'] ?? null, 'status' => $status],
        ]);
    }
}
