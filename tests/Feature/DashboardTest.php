<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_sent_to_login(): void
    {
        $this->get('/app/feedback')->assertRedirect('/login');
        $this->get('/login')->assertOk()->assertInertia(fn (Assert $page) => $page->component('auth/Login'));
    }

    public function test_users_only_see_their_accounts_feedback(): void
    {
        $mine = Feedback::factory()->create();
        $user = User::factory()->create(['account_id' => $mine->site->account_id]);
        $other = Feedback::factory()->create();

        $this->actingAs($user)->get('/app/feedback')
            ->assertInertia(fn (Assert $page) => $page
                ->component('feedback/Index')
                ->has('feedback.data', 1)
                ->where('feedback.data.0.id', $mine->id)
                ->where('counts.open', 1)
                ->where('selected', null)
                ->has('sites', 1));

        $this->actingAs($user)->get("/app/feedback/{$mine->id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('feedback/Index')
                ->where('selected.data.id', $mine->id));
        $this->actingAs($user)->get("/app/feedback/{$other->id}")->assertForbidden();
    }

    public function test_signing_out_lands_on_the_login_page(): void
    {
        $this->actingAs(User::factory()->create())->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_feedback_is_resolved_with_a_note_and_reopened(): void
    {
        $feedback = Feedback::factory()->create();
        $user = User::factory()->create(['account_id' => $feedback->site->account_id]);

        $this->actingAs($user)->post("/app/feedback/{$feedback->id}/resolve", ['resolved' => true, 'note' => 'Fixed the wrap.'])->assertRedirect();
        $this->assertSame('done', $feedback->fresh()->status->value);
        $this->assertSame('Fixed the wrap.', $feedback->fresh()->resolution_note);

        $this->actingAs($user)->post("/app/feedback/{$feedback->id}/resolve", ['resolved' => false])->assertRedirect();
        $this->assertSame('open', $feedback->fresh()->status->value);
        $this->assertNull($feedback->fresh()->resolved_by_id);
    }
}
