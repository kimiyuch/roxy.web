<?php

namespace Tests\Feature\Widget;

use App\Models\Feedback;
use App\Models\Reviewer;
use App\Models\User;
use App\Notifications\NewFeedback;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\TestCase;

class WidgetFeedbackTest extends TestCase
{
    use RefreshDatabase;

    private Reviewer $reviewer;

    private array $headers;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('local');
        Notification::fake();

        $this->reviewer = Reviewer::factory()->create();
        $token = Str::random(48);
        $this->reviewer->tokens()->create(['token_hash' => hash('sha256', $token)]);
        $this->headers = ['Origin' => 'https://staging.example.ch', 'Authorization' => "Bearer {$token}"];
    }

    private function png(int $width, int $height): UploadedFile
    {
        $image = new \Imagick;
        $image->newImage($width, $height, new \ImagickPixel('#9fc0e3'));
        $image->setImageFormat('png');
        $path = tempnam(sys_get_temp_dir(), 'roxy').'.png';
        $image->writeImage($path);

        return new UploadedFile($path, 'screenshot.png', 'image/png', null, true);
    }

    public function test_reviewer_leaves_feedback_with_context_and_screenshots(): void
    {
        $owner = User::factory()->create(['account_id' => $this->reviewer->site->account_id]);

        $id = $this->withHeaders($this->headers)->post("/api/widget/{$this->reviewer->site->key}/feedback", [
            'body' => '  The headline wraps badly on mobile.  ',
            'url' => 'https://staging.example.ch/about?ref=x',
            'context' => json_encode(['title' => 'About', 'viewport' => ['width' => 390, 'height' => 844], 'dpr' => 3, 'cookie' => 'secret']),
            'element' => json_encode(['selector' => 'main > h1', 'html' => str_repeat('<b>x</b>', 2000), 'text' => 'About us', 'rect' => ['x' => 1, 'y' => 2, 'width' => 3, 'height' => 4]]),
            'console' => json_encode(array_fill(0, 30, ['level' => 'error', 'message' => 'Boom'])),
            'network' => json_encode([['method' => 'get', 'url' => 'https://api.example.ch/x?token=secret', 'status' => 500]]),
            'screenshots' => [$this->png(3000, 1500), $this->png(400, 300)],
            'screenshot_kinds' => ['auto', 'manual'],
        ], ['Accept' => 'application/json'])->assertCreated()->json('id');

        $feedback = Feedback::with('screenshots')->find($id);

        $this->assertSame('The headline wraps badly on mobile.', $feedback->body);
        $this->assertSame('/about', $feedback->path);
        $this->assertEquals(['title' => 'About', 'viewport' => ['width' => 390, 'height' => 844], 'dpr' => 3], $feedback->context);
        $this->assertSame(4001, mb_strlen($feedback->element['html']));
        $this->assertCount(20, $feedback->console);
        $this->assertSame([['method' => 'GET', 'url' => 'https://api.example.ch/x', 'status' => 500]], $feedback->network);

        $this->assertCount(2, $feedback->screenshots);
        [$auto, $manual] = $feedback->screenshots;
        $this->assertSame('auto', $auto->kind->value);
        $this->assertSame([2560, 1280], [$auto->width, $auto->height]);
        $this->assertSame([400, 300], [$manual->width, $manual->height]);
        Storage::disk('local')->assertExists($auto->path);

        Notification::assertSentTo($owner, NewFeedback::class);
    }

    public function test_feedback_needs_a_token(): void
    {
        $this->withHeader('Origin', 'https://staging.example.ch')
            ->postJson("/api/widget/{$this->reviewer->site->key}/feedback", ['body' => 'x', 'url' => 'https://staging.example.ch/'])
            ->assertUnauthorized()
            ->assertHeader('Access-Control-Allow-Origin', 'https://staging.example.ch');
    }

    public function test_non_images_are_rejected(): void
    {
        $this->withHeaders($this->headers)->post("/api/widget/{$this->reviewer->site->key}/feedback", [
            'body' => 'x',
            'url' => 'https://staging.example.ch/',
            'screenshots' => [UploadedFile::fake()->create('evil.png', 10, 'text/html')],
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->assertSame(0, Feedback::count());
    }

    public function test_reviewers_see_everyones_open_feedback_on_the_same_page(): void
    {
        $colleague = Reviewer::factory()->create(['site_id' => $this->reviewer->site_id]);
        $mine = Feedback::factory()->create(['reviewer_id' => $this->reviewer->id]);
        $theirs = Feedback::factory()->create(['reviewer_id' => $colleague->id]);
        Feedback::factory()->create(['reviewer_id' => $colleague->id, 'status' => 'done']);
        Feedback::factory()->create(['reviewer_id' => $colleague->id, 'path' => '/contact']);
        Feedback::factory()->create();

        $this->withHeaders($this->headers)
            ->getJson("/api/widget/{$this->reviewer->site->key}/feedback?url=".urlencode('https://staging.example.ch/about#x'))
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonFragment(['id' => $mine->id, 'mine' => true])
            ->assertJsonFragment(['id' => $theirs->id, 'mine' => false, 'selector' => 'main > h1']);
    }
}
