<?php

namespace App\Mcp\Tools;

use App\Http\Resources\FeedbackResource;
use App\Models\Feedback;
use App\Models\Screenshot;
use App\Support\ImagickLimits;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Illuminate\Support\Facades\Storage;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\ResponseFactory;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('Get one feedback entry in full (text, page context, picked element, console errors, failed requests) with its screenshots as images. The `auto` screenshot is the viewport as the reviewer saw it.')]
#[IsReadOnly]
class GetFeedback extends Tool
{
    protected string $name = 'get_feedback';

    public function handle(Request $request): Response|ResponseFactory
    {
        $feedback = Feedback::find((string) $request->get('feedback'));

        if (! $feedback || ! $request->user()->can('view', $feedback)) {
            return Response::error('Feedback not found.');
        }

        $feedback->load(['site', 'reviewer', 'resolvedBy', 'screenshots']);

        $data = (new FeedbackResource($feedback))->resolve();
        $data['screenshots'] = $feedback->screenshots->map(fn (Screenshot $screenshot) => [
            'kind' => $screenshot->kind,
            'width' => $screenshot->width,
            'height' => $screenshot->height,
        ])->all();

        return Response::make([
            Response::json($data),
            ...$feedback->screenshots->map(fn (Screenshot $screenshot) => $this->image($screenshot))->all(),
        ]);
    }

    /** Scaled down for the model: past ~1568px an image costs tokens without showing more. */
    private function image(Screenshot $screenshot): Response
    {
        $data = Storage::disk(config('roxy.disk'))->get($screenshot->path);
        $maxEdge = config('roxy.mcp_image_max_edge');

        if (max($screenshot->width, $screenshot->height) > $maxEdge) {
            ImagickLimits::apply(storage_path('app/tmp'));
            $image = new \Imagick;
            $image->readImageBlob($data);
            $image->thumbnailImage($maxEdge, $maxEdge, true);
            $data = $image->getImageBlob();
            $image->clear();
        }

        return Response::image($data, $screenshot->mime);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'feedback' => $schema->string()->description('Feedback id.')->required(),
        ];
    }
}
