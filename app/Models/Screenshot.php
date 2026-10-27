<?php

namespace App\Models;

use App\Enums\ScreenshotKind;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/** A re-encoded screenshot on the `roxy.disk`, served only through short-lived URLs. */
#[Fillable(['feedback_id', 'path', 'mime', 'width', 'height', 'size', 'kind', 'position'])]
class Screenshot extends Model
{
    use HasUlids;

    protected function casts(): array
    {
        return [
            'kind' => ScreenshotKind::class,
        ];
    }

    public function feedback(): BelongsTo
    {
        return $this->belongsTo(Feedback::class);
    }

    public function temporaryUrl(int $minutes = 10): string
    {
        return Storage::disk(config('roxy.disk'))->temporaryUrl($this->path, now()->addMinutes($minutes));
    }
}
