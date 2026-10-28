<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\URL;

/**
 * Someone invited to leave feedback on one site. Reviewers never log in to Roxy:
 * a magic link hands the widget a one-time code, which it trades for a token
 * ({@see ReviewerToken}), one per browser.
 */
#[Fillable(['site_id', 'email', 'name'])]
#[Hidden(['login_code_hash', 'login_code_expires_at'])]
class Reviewer extends Model
{
    use HasFactory, HasUlids, Notifiable;

    protected function casts(): array
    {
        return [
            'login_code_expires_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(ReviewerToken::class);
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /** The signed link from the invitation email; opens the site and signs the widget in. */
    public function magicLink(?string $to = null): string
    {
        return URL::temporarySignedRoute(
            'reviewers.magic-link',
            now()->addDays(config('roxy.invite_link_days')),
            array_filter(['reviewer' => $this, 'to' => $to]),
        );
    }

    public function displayName(): string
    {
        return $this->name ?: $this->email;
    }
}
