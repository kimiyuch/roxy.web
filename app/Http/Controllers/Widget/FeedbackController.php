<?php

namespace App\Http\Controllers\Widget;

use App\Actions\Feedback\CreateFeedback;
use App\Enums\FeedbackStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Widget\StoreFeedbackRequest;
use App\Models\Feedback;
use App\Models\Reviewer;
use App\Models\Site;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedbackController extends Controller
{
    /**
     * Open feedback on the page the reviewer is looking at, from every reviewer of
     * the site, so they don't report the same thing twice.
     */
    public function index(Request $request, Site $site): JsonResponse
    {
        $input = $request->validate(['url' => ['required', 'url', 'max:2048']]);

        /** @var Reviewer $reviewer */
        $reviewer = $request->attributes->get('reviewer');

        $items = $site->feedback()
            ->where('status', FeedbackStatus::Open)
            ->where('path', parse_url($input['url'], PHP_URL_PATH) ?: '/')
            ->with('reviewer')
            ->latest()
            ->limit(50)
            ->get();

        return response()->json([
            'data' => $items->map(fn (Feedback $feedback) => [
                'id' => $feedback->id,
                'body' => $feedback->body,
                'author' => $feedback->reviewer?->displayName(),
                'mine' => $feedback->reviewer_id === $reviewer->id,
                'selector' => $feedback->element['selector'] ?? null,
                'created_at' => $feedback->created_at->toIso8601String(),
            ]),
        ]);
    }

    public function store(StoreFeedbackRequest $request, CreateFeedback $create): JsonResponse
    {
        $feedback = $create->execute(
            $request->attributes->get('reviewer'),
            $request->feedback(),
            $request->screenshots(),
        );

        return response()->json(['id' => $feedback->id], 201);
    }
}
