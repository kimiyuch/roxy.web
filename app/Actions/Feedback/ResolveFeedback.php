<?php

namespace App\Actions\Feedback;

use App\Enums\FeedbackStatus;
use App\Models\Feedback;
use App\Models\User;

/** Marks feedback done (stamping who, when and an optional note) or reopens it. */
class ResolveFeedback
{
    public function execute(Feedback $feedback, User $by, bool $resolved, ?string $note = null): Feedback
    {
        $feedback->update($resolved
            ? ['status' => FeedbackStatus::Done, 'resolved_at' => now(), 'resolved_by_id' => $by->id, 'resolution_note' => $note]
            : ['status' => FeedbackStatus::Open, 'resolved_at' => null, 'resolved_by_id' => null, 'resolution_note' => null]);

        return $feedback;
    }
}
