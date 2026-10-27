<?php

namespace App\Notifications;

use App\Models\Feedback;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Str;

/** Tells the account's users that a reviewer left feedback. */
class NewFeedback extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Feedback $feedback) {}

    public function via(User $user): array
    {
        return ['mail'];
    }

    public function toMail(User $user): MailMessage
    {
        $feedback = $this->feedback->loadMissing(['site', 'reviewer']);
        $who = $feedback->reviewer?->displayName() ?? 'A reviewer';

        return (new MailMessage)
            ->subject("New feedback on {$feedback->site->name}")
            ->line("{$who} left feedback on {$feedback->url}:")
            ->line(Str::limit($feedback->body, 500))
            ->action('Open in Roxy', route('feedback.show', $feedback));
    }
}
