<?php

namespace App\Models;

use App\Enums\FeedbackStatus;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One piece of feedback from a reviewer, with what the agent needs to act on it:
 * the page (`url`, `path`), `context` (title, viewport, pixel ratio, user agent,
 * scroll), the picked `element` (selector, html, text, rect), recent `console`
 * errors and failed `network` requests, and screenshots.
 */
#[Fillable([
    'site_id', 'reviewer_id', 'body', 'url', 'path', 'status',
    'context', 'element', 'console', 'network',
    'resolved_at', 'resolved_by_id', 'resolution_note',
])]
class Feedback extends Model
{
    use HasFactory, HasUlids;

    protected $table = 'feedback';

    protected $attributes = [
        'status' => 'open',
    ];

    protected function casts(): array
    {
        return [
            'status' => FeedbackStatus::class,
            'context' => 'array',
            'element' => 'array',
            'console' => 'array',
            'network' => 'array',
            'resolved_at' => 'datetime',
        ];
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(Reviewer::class);
    }

    public function resolvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'resolved_by_id');
    }

    public function screenshots(): HasMany
    {
        return $this->hasMany(Screenshot::class)->orderBy('position');
    }

    /** @param  Builder<Feedback>  $query */
    public function scopeForAccount(Builder $query, int $accountId): void
    {
        $query->whereHas('site', fn (Builder $site) => $site->where('account_id', $accountId));
    }
}
