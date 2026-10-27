<?php

namespace App\Actions\Reviewers;

use App\Models\Reviewer;
use App\Models\Site;
use App\Notifications\ReviewerMagicLink;
use Illuminate\Support\Str;

/** Adds a reviewer to a site (or finds the existing one) and emails them a magic link. */
class InviteReviewer
{
    public function execute(Site $site, string $email, ?string $name = null): Reviewer
    {
        $reviewer = $site->reviewers()->firstOrCreate(
            ['email' => Str::lower(trim($email))],
            ['name' => $name],
        );

        $reviewer->notify(new ReviewerMagicLink(invited: true));

        return $reviewer;
    }
}
