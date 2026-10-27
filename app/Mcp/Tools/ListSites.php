<?php

namespace App\Mcp\Tools;

use App\Enums\FeedbackStatus;
use App\Models\Site;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Mcp\Request;
use Laravel\Mcp\Response;
use Laravel\Mcp\Server\Attributes\Description;
use Laravel\Mcp\Server\Tool;
use Laravel\Mcp\Server\Tools\Annotations\IsReadOnly;

#[Description('List the sites you can reach, with the repository each is built from and its count of open feedback.')]
#[IsReadOnly]
class ListSites extends Tool
{
    protected string $name = 'list_sites';

    public function handle(Request $request): Response
    {
        $sites = Site::where('account_id', $request->user()->account_id)
            ->withCount(['feedback as open_feedback' => fn ($query) => $query->where('status', FeedbackStatus::Open)])
            ->orderBy('name')
            ->get();

        return Response::json($sites->map(fn (Site $site) => [
            'id' => $site->id,
            'name' => $site->name,
            'url' => $site->start_url,
            'repository' => $site->repository,
            'open_feedback' => $site->open_feedback,
        ])->all());
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}
