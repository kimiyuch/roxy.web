<?php

namespace App\Http\Requests\Widget;

use App\Enums\ScreenshotKind;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\UploadedFile;
use Illuminate\Validation\Rule;

/**
 * Multipart from the widget. The captured context arrives as JSON strings (`context`,
 * `element`, `console`, `network`), screenshots as `screenshots[]` with a matching
 * `screenshot_kinds[]`.
 */
class StoreFeedbackRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'body' => ['required', 'string', 'max:10000'],
            'url' => ['required', 'url', 'max:2048'],
            'context' => ['nullable', 'json', 'max:20000'],
            'element' => ['nullable', 'json', 'max:20000'],
            'console' => ['nullable', 'json', 'max:100000'],
            'network' => ['nullable', 'json', 'max:100000'],
            'screenshots' => ['nullable', 'array', 'max:'.config('roxy.max_screenshots')],
            'screenshots.*' => ['file', 'mimes:jpg,jpeg,png,webp', 'max:'.config('roxy.max_upload_kb')],
            'screenshot_kinds' => ['nullable', 'array'],
            'screenshot_kinds.*' => [Rule::enum(ScreenshotKind::class)],
        ];
    }

    /** @return array{body: string, url: string, context: ?array, element: ?array, console: ?array, network: ?array} */
    public function feedback(): array
    {
        return [
            'body' => $this->string('body')->trim()->toString(),
            'url' => $this->string('url')->toString(),
            ...collect(['context', 'element', 'console', 'network'])
                ->mapWithKeys(fn (string $key) => [$key => $this->filled($key) ? json_decode($this->input($key), true) : null])
                ->map(fn ($value) => is_array($value) ? $value : null)
                ->all(),
        ];
    }

    /** @return list<array{file: UploadedFile, kind: ScreenshotKind}> */
    public function screenshots(): array
    {
        $kinds = $this->input('screenshot_kinds', []);

        return collect($this->file('screenshots', []))
            ->values()
            ->map(fn (UploadedFile $file, int $i) => [
                'file' => $file,
                'kind' => ScreenshotKind::tryFrom($kinds[$i] ?? '') ?? ScreenshotKind::Manual,
            ])
            ->all();
    }
}
