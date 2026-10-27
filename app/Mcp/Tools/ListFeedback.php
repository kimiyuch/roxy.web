<?php

namespace App\Mcp\Tools;

use App\Models\Feedback;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Str;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List feedback, newest first: a summary per entry with its URL, the selector of the element it points at, and its screenshot count. Use get_feedback for the full entry and screenshots.')]
#[IsReadOnly]
class ListFeedback extends Tool
{
    protected string $name = 'list_feedback';

    public function handle(Request $request): Response
    {
        $input = $request->validate([
            'site' => ['nullable', 'string'],
            'status' => ['nullable', 'in:open,done,all'],
            'since' => ['nullable', 'date'],
        ]);

        $status = $input['status'] ?? 'open';

        $items = Feedback::query()
            ->forAccount($request->user()->account_id)
            ->when($input['site'] ?? null, fn ($query, $site) => $query->where('site_id', $site))
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($input['since'] ?? null, fn ($query, $since) => $query->where('created_at', '>=', $since))
            ->with(['site', 'reviewer'])
            ->withCount('screenshots')
            ->latest()
            ->limit(100)
            ->get();

        return Response::json($items->map(fn (Feedback $feedback) => [
            'id' => $feedback->id,
            'site' => $feedback->site->name,
            'status' => $feedback->status,
            'summary' => Str::limit($feedback->body, 200),
            'url' => $feedback->url,
            'selector' => $feedback->element['selector'] ?? null,
            'reviewer' => $feedback->reviewer?->displayName(),
            'screenshots' => $feedback->screenshots_count,
            'console_errors' => count($feedback->console ?? []),
            'failed_requests' => count($feedback->network ?? []),
            'created_at' => $feedback->created_at->toIso8601String(),
        ])->all());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'site' => $schema->string()->description('Site id from list_sites. Default: all sites.'),
            'status' => $schema->string()->enum(['open', 'done', 'all'])->description('Default `open`.'),
            'since' => $schema->string()->description('Only feedback created on or after this date (ISO 8601).'),
        ];
    }
}
