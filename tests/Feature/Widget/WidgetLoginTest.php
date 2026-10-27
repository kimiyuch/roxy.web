<?php

namespace Tests\Feature\Widget;

use App\Models\Reviewer;
use App\Models\Site;
use App\Notifications\ReviewerMagicLink;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class WidgetLoginTest extends TestCase
{
    use RefreshDatabase;

    private const ORIGIN = 'https://staging.example.ch';

    private function magicLink(Reviewer $reviewer, ?string $to = null): string
    {
        return URL::temporarySignedRoute('reviewers.magic-link', now()->addDay(), array_filter(['reviewer' => $reviewer, 'to' => $to]));
    }

    private function codeFrom(string $location): string
    {
        return str($location)->after('#roxy=')->toString();
    }

    public function test_magic_link_redirects_to_the_site_with_a_code_in_the_fragment(): void
    {
        $reviewer = Reviewer::factory()->create();

        $location = $this->get($this->magicLink($reviewer))->assertRedirect()->headers->get('Location');

        $this->assertStringStartsWith('https://staging.example.ch/#roxy=', $location);
        $this->assertSame(40, strlen($this->codeFrom($location)));
    }

    public function test_magic_link_lands_on_the_requested_page_only_if_it_is_on_the_site(): void
    {
        $reviewer = Reviewer::factory()->create();

        $this->assertStringStartsWith(
            'https://staging.example.ch/contact#roxy=',
            $this->get($this->magicLink($reviewer, 'https://staging.example.ch/contact#top'))->headers->get('Location'),
        );
        $this->assertStringStartsWith(
            'https://staging.example.ch/#roxy=',
            $this->get($this->magicLink($reviewer, 'https://evil.example.com/'))->headers->get('Location'),
        );
    }

    public function test_unsigned_magic_link_is_rejected(): void
    {
        $reviewer = Reviewer::factory()->create();

        $this->get("/r/{$reviewer->id}")->assertForbidden();
    }

    public function test_code_is_exchanged_once_for_a_token(): void
    {
        $reviewer = Reviewer::factory()->create();
        $code = $this->codeFrom($this->get($this->magicLink($reviewer))->headers->get('Location'));
        $key = $reviewer->site->key;

        $token = $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$key}/session", ['code' => $code])
            ->assertCreated()
            ->assertJsonPath('reviewer.email', $reviewer->email)
            ->json('token');

        $this->withHeaders(['Origin' => self::ORIGIN, 'Authorization' => "Bearer {$token}"])
            ->getJson("/api/widget/{$key}/me")
            ->assertOk()
            ->assertJsonPath('site.name', $reviewer->site->name)
            ->assertHeader('Access-Control-Allow-Origin', self::ORIGIN);

        $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$key}/session", ['code' => $code])
            ->assertUnprocessable();
    }

    public function test_expired_code_is_rejected(): void
    {
        $reviewer = Reviewer::factory()->create();
        $code = $this->codeFrom($this->get($this->magicLink($reviewer))->headers->get('Location'));

        $this->travel(16)->minutes();

        $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$reviewer->site->key}/session", ['code' => $code])
            ->assertUnprocessable();
    }

    public function test_code_from_another_site_is_rejected(): void
    {
        $reviewer = Reviewer::factory()->create();
        $other = Site::factory()->create();
        $code = $this->codeFrom($this->get($this->magicLink($reviewer))->headers->get('Location'));

        $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$other->key}/session", ['code' => $code])
            ->assertUnprocessable();
    }

    public function test_token_only_works_on_its_own_site(): void
    {
        $reviewer = Reviewer::factory()->create();
        $other = Site::factory()->create();
        $code = $this->codeFrom($this->get($this->magicLink($reviewer))->headers->get('Location'));
        $token = $this->withHeader('Origin', self::ORIGIN)->postJson("/api/widget/{$reviewer->site->key}/session", ['code' => $code])->json('token');

        $this->withHeaders(['Origin' => self::ORIGIN, 'Authorization' => "Bearer {$token}"])
            ->getJson("/api/widget/{$other->key}/me")
            ->assertUnauthorized();
    }

    public function test_logout_revokes_the_token(): void
    {
        $reviewer = Reviewer::factory()->create();
        $code = $this->codeFrom($this->get($this->magicLink($reviewer))->headers->get('Location'));
        $key = $reviewer->site->key;
        $headers = ['Origin' => self::ORIGIN];
        $headers['Authorization'] = 'Bearer '.$this->withHeaders($headers)->postJson("/api/widget/{$key}/session", ['code' => $code])->json('token');

        $this->withHeaders($headers)->deleteJson("/api/widget/{$key}/session")->assertNoContent();
        $this->withHeaders($headers)->getJson("/api/widget/{$key}/me")->assertUnauthorized();
    }

    public function test_requesting_a_link_emails_known_reviewers_and_says_nothing_either_way(): void
    {
        Notification::fake();
        $reviewer = Reviewer::factory()->create();
        $key = $reviewer->site->key;

        $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$key}/link", ['email' => strtoupper($reviewer->email), 'url' => 'https://staging.example.ch/about'])
            ->assertStatus(202);
        $this->withHeader('Origin', self::ORIGIN)
            ->postJson("/api/widget/{$key}/link", ['email' => 'stranger@example.com'])
            ->assertStatus(202);

        Notification::assertSentTo($reviewer, ReviewerMagicLink::class, fn ($n) => $n->to === 'https://staging.example.ch/about');
        Notification::assertCount(1);
    }

    public function test_unknown_origins_are_refused(): void
    {
        $site = Site::factory()->create();

        $this->withHeader('Origin', 'https://evil.example.com')
            ->postJson("/api/widget/{$site->key}/session", ['code' => 'x'])
            ->assertForbidden()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_requests_without_an_origin_get_no_cors_headers(): void
    {
        $site = Site::factory()->create();

        $this->postJson("/api/widget/{$site->key}/session", ['code' => 'nope'])
            ->assertUnprocessable()
            ->assertHeaderMissing('Access-Control-Allow-Origin');
    }

    public function test_preflight_is_answered_for_allowed_origins(): void
    {
        $site = Site::factory()->create(['allowed_origins' => ['https://*.example.ch']]);

        $this->withHeaders(['Origin' => 'https://preview.example.ch', 'Access-Control-Request-Method' => 'POST'])
            ->options("/api/widget/{$site->key}/feedback")
            ->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://preview.example.ch')
            ->assertHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, Accept');

        $this->withHeaders(['Origin' => 'https://a.b.example.ch', 'Access-Control-Request-Method' => 'POST'])
            ->options("/api/widget/{$site->key}/feedback")
            ->assertForbidden();
    }
}
