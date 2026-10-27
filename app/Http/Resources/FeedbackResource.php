<?php

namespace App\Http\Resources;

use App\Models\Feedback;
use App\Models\Screenshot;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Feedback for the dashboard and MCP. Screenshot URLs are short-lived and only
 * included when the screenshots are loaded.
 *
 * @mixin Feedback
 */
class FeedbackResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'body' => $this->body,
            'url' => $this->url,
            'path' => $this->path,
            'site' => $this->whenLoaded('site', fn () => [
                'id' => $this->site->id,
                'name' => $this->site->name,
                'repository' => $this->site->repository,
            ]),
            'reviewer' => $this->whenLoaded('reviewer', fn () => $this->reviewer ? [
                'name' => $this->reviewer->name,
                'email' => $this->reviewer->email,
            ] : null),
            'context' => $this->context,
            'element' => $this->element,
            'console' => $this->console,
            'network' => $this->network,
            'screenshots' => $this->whenLoaded('screenshots', fn () => $this->screenshots->map(fn (Screenshot $screenshot) => [
                'id' => $screenshot->id,
                'kind' => $screenshot->kind,
                'width' => $screenshot->width,
                'height' => $screenshot->height,
                'url' => $screenshot->temporaryUrl(),
            ])),
            'screenshot_count' => $this->whenCounted('screenshots'),
            'resolved_at' => $this->resolved_at?->toIso8601String(),
            'resolved_by' => $this->whenLoaded('resolvedBy', fn () => $this->resolvedBy?->name),
            'resolution_note' => $this->resolution_note,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
