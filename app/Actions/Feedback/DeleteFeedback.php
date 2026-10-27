<?php

namespace App\Actions\Feedback;

use App\Models\Feedback;
use Illuminate\Support\Facades\Storage;

/** Discards feedback for good: the row, its screenshot rows and the files. */
class DeleteFeedback
{
    public function execute(Feedback $feedback): void
    {
        $paths = $feedback->screenshots()->pluck('path')->all();

        $feedback->delete();

        if ($paths) {
            Storage::disk(config('roxy.disk'))->delete($paths);
        }
    }
}
