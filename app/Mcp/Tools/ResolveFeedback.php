<?php

namespace App\Mcp\Tools;

use App\Actions\Feedback\ResolveFeedback as ResolveFeedbackAction;
use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsIdempotent;

#[Description('Mark feedback done with a short note on what changed (`resolved: true`), or reopen it (`resolved: false`).')]
#[IsIdempotent]
class ResolveFeedback extends Tool
{
    protected string $name = 'resolve_feedback';

    public function handle(Request $request, ResolveFeedbackAction $resolve): Response
    {
        $input = $request->validate([
            'feedback' => ['required', 'string'],
            'resolved' => ['nullable', 'boolean'],
            'note' => ['nullable', 'string', 'max:2000'],
        ]);

        $feedback = Feedback::find($input['feedback']);

        if (! $feedback || ! $request->user()->can('update', $feedback)) {
            return Response::error('Feedback not found.');
        }

        $resolve->execute($feedback, $request->user(), $input['resolved'] ?? true, $input['note'] ?? null);

        return Response::json((new FeedbackResource($feedback->load(['site', 'reviewer', 'resolvedBy'])))->resolve());
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->description('Feedback id.')->required(),
            'resolved' => $schema->boolean()->description('Default true; false reopens.'),
            'note' => $schema->string()->description('What you changed, in a sentence or two, written for the client.'),
        ];
    }
}
