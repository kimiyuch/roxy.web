<?php

namespace Tests\Feature;

use App\Models\Feedback;
use App\Models\Reviewer;
use App\Models\Site;
use App\Models\User;
use App\Notifications\ReviewerMagicLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class SitesTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_site_is_added_with_tidied_addresses(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/app/sites', [
            'name' => 'Alpenblick',
            'start_url' => 'https://staging.alpenblick.ch/rooms',
            'origins' => "https://www.alpenblick.ch/de/\nhttps://*.Alpenblick.ch\nhttps://www.alpenblick.ch",
            'repository' => 'https://github.com/kimiyuch/alpenblick.web.git',
            'position' => 'bottom-left',
            'color' => '#0F766E',
        ])->assertRedirect();

        $site = Site::where('name', 'Alpenblick')->firstOrFail();
        $this->assertSame($user->account_id, $site->account_id);
        $this->assertSame(['https://staging.alpenblick.ch', 'https://www.alpenblick.ch', 'https://*.alpenblick.ch'], $site->allowed_origins);
        $this->assertSame('kimiyuch/alpenblick.web', $site->repository);
        $this->assertSame(['position' => 'bottom-left', 'color' => '#0f766e'], $site->settings);
    }

    public function test_bad_addresses_are_refused(): void
    {
        $this->actingAs(User::factory()->create())->post('/app/sites', [
            'name' => 'Alpenblick',
            'start_url' => 'not a url',
            'origins' => 'ftp://example.ch',
            'repository' => 'just-a-name',
            'position' => 'top',
        ])->assertSessionHasErrors(['start_url', 'origins.0', 'repository', 'position']);
    }

    public function test_sites_of_other_accounts_are_off_limits(): void
    {
        $site = Site::factory()->create();
        $this->actingAs(User::factory()->create());

        $this->get("/app/sites/{$site->id}")->assertForbidden();
        $this->put("/app/sites/{$site->id}", ['name' => 'Mine now'])->assertForbidden();
        $this->delete("/app/sites/{$site->id}")->assertForbidden();
        $this->post("/app/sites/{$site->id}/reviewers", ['email' => 'me@evil.example'])->assertForbidden();
    }

    public function test_the_site_page_lists_reviewers_with_their_links(): void
    {
        $reviewer = Reviewer::factory()->create();
        $user = User::factory()->create(['account_id' => $reviewer->site->account_id]);

        $this->actingAs($user)->get("/app/sites/{$reviewer->site_id}")
            ->assertInertia(fn (Assert $page) => $page
                ->component('sites/Index')
                ->where('selected.script_tag', $reviewer->site->scriptTag())
                ->where('reviewers.0.email', $reviewer->email)
                ->has('reviewers.0.link'));
    }

    public function test_reviewers_are_invited_resent_and_removed(): void
    {
        Notification::fake();
        $site = Site::factory()->create();
        $user = User::factory()->create(['account_id' => $site->account_id]);
        $this->actingAs($user);

        $this->post("/app/sites/{$site->id}/reviewers", ['email' => 'Anna@Alpenblick.ch', 'name' => 'Anna'])->assertRedirect();
        $reviewer = $site->reviewers()->where('email', 'anna@alpenblick.ch')->firstOrFail();
        Notification::assertSentTo($reviewer, ReviewerMagicLink::class);

        $this->post("/app/sites/{$site->id}/reviewers/{$reviewer->id}/resend")->assertRedirect();
        Notification::assertSentToTimes($reviewer, ReviewerMagicLink::class, 2);

        $reviewer->tokens()->create(['token_hash' => hash('sha256', 'abc')]);
        $feedback = Feedback::factory()->create(['site_id' => $site->id, 'reviewer_id' => $reviewer->id]);

        $this->delete("/app/sites/{$site->id}/reviewers/{$reviewer->id}")->assertRedirect();
        $this->assertModelMissing($reviewer);
        $this->assertDatabaseCount('reviewer_tokens', 0);
        $this->assertNull($feedback->fresh()->reviewer_id);
    }

    public function test_deleting_a_site_removes_its_feedback_and_files(): void
    {
        Storage::fake(config('roxy.disk'));
        $feedback = Feedback::factory()->create();
        $site = $feedback->site;
        Storage::disk(config('roxy.disk'))->put("screenshots/{$site->id}/{$feedback->id}/1.png", 'x');
        $user = User::factory()->create(['account_id' => $site->account_id]);

        $this->actingAs($user)->delete("/app/sites/{$site->id}")->assertRedirect('/app/sites');

        $this->assertModelMissing($site);
        $this->assertModelMissing($feedback);
        Storage::disk(config('roxy.disk'))->assertMissing("screenshots/{$site->id}/{$feedback->id}/1.png");
    }
}
