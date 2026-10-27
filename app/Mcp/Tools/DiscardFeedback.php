<?php

namespace App\Mcp\Tools;

use App\Actions\Feedback\DeleteFeedback;
use App\Models\Feedback;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsDestructive;

#[Description('Delete a feedback entry and its screenshots for good. For spam and duplicates; confirm with the user first.')]
#[IsDestructive]
class DiscardFeedback extends Tool
{
    protected string $name = 'discard_feedback';

    public function handle(Request $request, DeleteFeedback $delete): Response
    {
        $feedback = Feedback::find((string) $request->get('feedback'));

        if (! $feedback || ! $request->user()->can('delete', $feedback)) {
            return Response::error('Feedback not found.');
        }

        $delete->execute($feedback);

        return Response::text('Discarded.');
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->description('Feedback id.')->required(),
        ];
    }
}
