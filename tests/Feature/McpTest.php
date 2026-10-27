<?php

namespace Tests\Feature;

use App\Mcp\Servers\RoxyServer;
use App\Mcp\Tools\DiscardFeedback;
use App\Mcp\Tools\GetFeedback;
use App\Mcp\Tools\ListFeedback;
use App\Mcp\Tools\ListSites;
use App\Mcp\Tools\ResolveFeedback;
use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class McpTest extends TestCase
{
    use RefreshDatabase;

    public function test_endpoint_needs_a_token_with_the_mcp_ability(): void
    {
        $user = User::factory()->create();

        $this->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])->assertUnauthorized();

        $plain = $user->createToken('other', ['read'])->plainTextToken;
        $this->withToken($plain)->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])->assertForbidden();

        $this->app['auth']->forgetGuards();
        $plain = $user->createToken('claude', ['mcp'])->plainTextToken;
        $this->withToken($plain)->postJson('/mcp', ['jsonrpc' => '2.0', 'id' => 1, 'method' => 'tools/list'])
            ->assertOk()
            ->assertJsonFragment(['name' => 'get_feedback']);
    }

    public function test_agent_lists_reads_and_resolves_feedback_of_its_account_only(): void
    {
        $feedback = Feedback::factory()->create(['body' => 'Logo is blurry']);
        $other = Feedback::factory()->create(['body' => 'Someone else’s']);
        $user = User::factory()->create(['account_id' => $feedback->site->account_id]);

        RoxyServer::actingAs($user)->tool(ListSites::class)->assertOk()->assertSee('kimiyuch/example.web');
        RoxyServer::actingAs($user)->tool(ListFeedback::class)->assertOk()->assertSee('Logo is blurry')->assertDontSee('Someone else');
        RoxyServer::actingAs($user)->tool(GetFeedback::class, ['feedback' => $feedback->id])->assertOk()->assertSee('main > h1');
        RoxyServer::actingAs($user)->tool(GetFeedback::class, ['feedback' => $other->id])->assertHasErrors(['Feedback not found.']);

        RoxyServer::actingAs($user)->tool(ResolveFeedback::class, ['feedback' => $feedback->id, 'note' => 'Swapped in the SVG logo.'])->assertOk();
        $this->assertSame('done', $feedback->fresh()->status->value);
        $this->assertSame($user->id, $feedback->fresh()->resolved_by_id);

        RoxyServer::actingAs($user)->tool(DiscardFeedback::class, ['feedback' => $other->id])->assertHasErrors();
        RoxyServer::actingAs($user)->tool(DiscardFeedback::class, ['feedback' => $feedback->id])->assertOk();
        $this->assertNull($feedback->fresh());
        $this->assertNotNull($other->fresh());
    }
}
