<?php

namespace App\Actions\Sites;

use App\Models\Site;
use Illuminate\Support\Facades\Storage;

/** Deletes a site with its reviewers, feedback and screenshot files. */
class DeleteSite
{
    public function execute(Site $site): void
    {
        $site->delete();

        // Screenshots live under screenshots/{site}/…; the rows went with the cascade.
        Storage::disk(config('roxy.disk'))->deleteDirectory("screenshots/{$site->id}");
    }
}
