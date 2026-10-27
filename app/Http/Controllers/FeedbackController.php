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
    public function index(Request $request): Response
    {
        $filters = $request->validate([
            'site' => ['nullable', 'string'],
            'status' => ['nullable', 'in:open,done,all'],
        ]);
        $status = $filters['status'] ?? 'open';
        $account = $request->user()->account;

        $feedback = Feedback::query()
            ->forAccount($account->id)
            ->when($filters['site'] ?? null, fn ($query, $site) => $query->where('site_id', $site))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->with(['site', 'reviewer'])
            ->withCount('screenshots')
            ->latest()
            ->paginate(50)
            ->withQueryString();

        return Inertia::render('feedback/Index', [
            'feedback' => FeedbackResource::collection($feedback),
            'sites' => $account->sites()->orderBy('name')->get(['id', 'name']),
            'filters' => ['site' => $filters['site'] ?? null, 'status' => $status],
        ]);
    }

    public function show(Feedback $feedback): Response
    {
        Gate::authorize('view', $feedback);

        return Inertia::render('feedback/Show', [
            'feedback' => new FeedbackResource($feedback->load(['site', 'reviewer', 'resolvedBy', 'screenshots'])),
        ]);
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

    public function destroy(Feedback $feedback, DeleteFeedback $delete): RedirectResponse
    {
        Gate::authorize('delete', $feedback);

        $delete->execute($feedback);

        return to_route('feedback.index');
    }
}
