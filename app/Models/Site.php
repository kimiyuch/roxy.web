<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * A website the widget is installed on. `key` is public (it's in the script tag);
 * the widget API only answers requests from `allowed_origins`, and writes need a
 * reviewer token on top. `start_url` is where magic links land. `repository` tells
 * the agent which codebase the site is built from.
 */
#[Fillable(['account_id', 'name', 'allowed_origins', 'start_url', 'repository', 'settings'])]
#[Hidden(['account_id'])]
class Site extends Model
{
    use HasFactory, HasUlids;

    protected static function booted(): void
    {
        static::creating(function (Site $site) {
            $site->key ??= Str::lower(Str::random(24));
        });
    }

    protected function casts(): array
    {
        return [
            'allowed_origins' => 'array',
            'settings' => 'array',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reviewers(): HasMany
    {
        return $this->hasMany(Reviewer::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Exact origins (`https://staging.example.ch`) or one wildcard subdomain level
     * (`https://*.example.ch`). Scheme and port must match.
     */
    /** `https://staging.example.ch` from any URL on it; null if it isn't one. */
    public static function originOf(string $url): ?string
    {
        $parts = parse_url(trim($url));

        if (! isset($parts['scheme'], $parts['host']) || ! in_array(Str::lower($parts['scheme']), ['http', 'https'])) {
            return null;
        }

        return Str::lower($parts['scheme'].'://'.$parts['host']).(isset($parts['port']) ? ':'.$parts['port'] : '');
    }

    /** What goes on the client's site. */
    public function scriptTag(): string
    {
        return '<script src="'.url('widget.js').'" data-site="'.$this->key.'" defer></script>';
    }

    public function allowsOrigin(?string $origin): bool
    {
        if (! $origin) {
            return false;
        }

        $origin = rtrim(Str::lower($origin), '/');

        foreach ($this->allowed_origins ?? [] as $allowed) {
            $allowed = rtrim(Str::lower($allowed), '/');

            if ($allowed === $origin) {
                return true;
            }

            if (str_contains($allowed, '://*.')) {
                [$scheme, $host] = explode('://*.', $allowed, 2);
                $pattern = '/^'.preg_quote($scheme, '/').':\/\/[a-z0-9-]+\.'.preg_quote($host, '/').'$/';

                if (preg_match($pattern, $origin)) {
                    return true;
                }
            }
        }

        return false;
    }
}
