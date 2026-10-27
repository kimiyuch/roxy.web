<?php

namespace App\Actions\Feedback;

use App\Enums\ScreenshotKind;
use App\Models\Feedback;
use App\Models\Reviewer;
use App\Notifications\NewFeedback;
use App\Support\CapturedContext;
use App\Support\ImagickLimits;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Spatie\Image\Enums\Fit;
use Spatie\Image\Enums\ImageDriver;
use Spatie\Image\Image;

/**
 * Records a reviewer's feedback from the widget. The captured context is trimmed
 * ({@see CapturedContext}); screenshots are ping-validated, fitted (no upscale) and
 * re-encoded by Imagick, which strips metadata. JPEG stays JPEG, anything else
 * becomes PNG, stored at `screenshots/{site}/{feedback}/{n}-{random}.{ext}`.
 * The account's users are notified.
 */
class CreateFeedback
{
    /**
     * @param  array{body: string, url: string, context?: ?array, element?: ?array, console?: ?array, network?: ?array}  $input
     * @param  list<array{file: UploadedFile, kind: ScreenshotKind}>  $screenshots
     */
    public function execute(Reviewer $reviewer, array $input, array $screenshots = []): Feedback
    {
        $site = $reviewer->site;

        $feedback = new Feedback([
            'site_id' => $site->id,
            'reviewer_id' => $reviewer->id,
            'body' => $input['body'],
            'url' => $input['url'],
            'path' => parse_url($input['url'], PHP_URL_PATH) ?: '/',
            'context' => CapturedContext::context($input['context'] ?? null),
            'element' => CapturedContext::element($input['element'] ?? null),
            'console' => CapturedContext::console($input['console'] ?? null),
            'network' => CapturedContext::network($input['network'] ?? null),
        ]);
        $feedback->id = $feedback->newUniqueId();

        $stored = [];
        try {
            foreach (array_values($screenshots) as $i => $screenshot) {
                $stored[] = $this->store($feedback, $i + 1, $screenshot['file'], $screenshot['kind']);
            }

            DB::transaction(function () use ($feedback, $stored) {
                $feedback->save();
                $feedback->screenshots()->createMany($stored);
            });
        } catch (\Throwable $e) {
            Storage::disk(config('roxy.disk'))->delete(array_column($stored, 'path'));
            throw $e;
        }

        Notification::send($site->account->users, new NewFeedback($feedback));

        return $feedback;
    }

    /** @return array<string, mixed> attributes for the Screenshot row */
    private function store(Feedback $feedback, int $position, UploadedFile $file, ScreenshotKind $kind): array
    {
        $tmpDir = storage_path('app/tmp');
        if (! is_dir($tmpDir)) {
            mkdir($tmpDir, 0775, true);
        }

        ImagickLimits::apply($tmpDir);

        try {
            $probe = new \Imagick;
            $probe->pingImage($file->getRealPath());
            $pixels = $probe->getImageWidth() * $probe->getImageHeight();
            $probe->clear();
        } catch (\Throwable) {
            $this->fail('That file isn’t an image we can read. Try a PNG, JPEG or WebP.');
        }

        if ($pixels > ImagickLimits::MEGAPIXEL_CEILING) {
            $this->fail('That image is too large to process.');
        }

        [$ext, $mime] = $file->getMimeType() === 'image/jpeg' ? ['jpg', 'image/jpeg'] : ['png', 'image/png'];
        $tmpOut = "{$tmpDir}/".Str::uuid()."-screenshot.{$ext}";

        try {
            $maxEdge = config('roxy.screenshot_max_edge');
            Image::useImageDriver(ImageDriver::Imagick)
                ->loadFile($file->getRealPath())
                ->fit(Fit::Max, $maxEdge, $maxEdge)
                ->save($tmpOut);

            [$width, $height] = getimagesize($tmpOut);
            $path = "screenshots/{$feedback->site_id}/{$feedback->id}/{$position}-".Str::random(8).".{$ext}";
            Storage::disk(config('roxy.disk'))->put($path, file_get_contents($tmpOut), ['ContentType' => $mime]);

            return [
                'path' => $path,
                'mime' => $mime,
                'width' => $width,
                'height' => $height,
                'size' => filesize($tmpOut),
                'kind' => $kind,
                'position' => $position,
            ];
        } finally {
            @unlink($tmpOut);
        }
    }

    private function fail(string $message): never
    {
        throw ValidationException::withMessages(['screenshots' => $message]);
    }
}
