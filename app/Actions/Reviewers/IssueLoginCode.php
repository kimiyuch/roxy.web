<?php

namespace App\Actions\Reviewers;

use App\Models\Reviewer;
use Illuminate\Support\Str;

/**
 * Mints the one-time code a magic link hands to the widget in the URL fragment.
 * One pending code per reviewer; a new click replaces the old one.
 */
class IssueLoginCode
{
    public function execute(Reviewer $reviewer): string
    {
        $code = Str::random(40);

        $reviewer->forceFill([
            'login_code_hash' => hash('sha256', $code),
            'login_code_expires_at' => now()->addMinutes(config('roxy.login_code_minutes')),
        ])->save();

        return $code;
    }
}
