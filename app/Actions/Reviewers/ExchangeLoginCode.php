<?php

namespace App\Actions\Reviewers;

use App\Models\Reviewer;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Trades a one-time login code for a long-lived widget token. The code is burnt
 * either way; only the token's hash is stored. Returns null for an unknown,
 * expired or other-site code.
 *
 * @return array{reviewer: Reviewer, token: string}|null
 */
class ExchangeLoginCode
{
    public function execute(Site $site, string $code, ?string $userAgent = null): ?array
    {
        return DB::transaction(function () use ($site, $code, $userAgent) {
            $reviewer = Reviewer::where('login_code_hash', hash('sha256', $code))->lockForUpdate()->first();

            if (! $reviewer) {
                return null;
            }

            $valid = $reviewer->site_id === $site->id && $reviewer->login_code_expires_at?->isFuture();

            $reviewer->forceFill(['login_code_hash' => null, 'login_code_expires_at' => null])->save();

            if (! $valid) {
                return null;
            }

            $token = Str::random(48);
            $reviewer->tokens()->create([
                'token_hash' => hash('sha256', $token),
                'user_agent' => $userAgent ? Str::limit($userAgent, 250, '') : null,
                'last_used_at' => now(),
            ]);
            $reviewer->forceFill(['last_seen_at' => now()])->save();

            return ['reviewer' => $reviewer, 'token' => $token];
        });
    }
}
