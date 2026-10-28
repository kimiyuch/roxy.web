<?php

namespace App\Notifications;

use App\Models\Reviewer;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * The link that turns the widget on for a reviewer. It points at Roxy (signed,
 * {@see config('roxy.invite_link_days')} days), which redirects to the site with a
 * one-time code in the fragment. `to` is the page to land on, if it's on the site.
 */
class ReviewerMagicLink extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public bool $invited = false, public ?string $to = null) {}

    public function via(Reviewer $reviewer): array
    {
        return ['mail'];
    }

    public function toMail(Reviewer $reviewer): MailMessage
    {
        $site = $reviewer->site;
        $url = $reviewer->magicLink($this->to);

        return (new MailMessage)
            ->subject($this->invited ? "You're invited to review {$site->name}" : "Your link to review {$site->name}")
            ->greeting('Hi'.($reviewer->name ? " {$reviewer->name}" : ''))
            ->line($this->invited
                ? "You've been invited to leave feedback on {$site->name}. The link below opens the site with the feedback button turned on."
                : "Here's your link to leave feedback on {$site->name}.")
            ->action("Open {$site->name}", $url)
            ->line('Point at what needs changing, describe it, and send. Screenshots are attached for you.')
            ->line('The link works for '.config('roxy.invite_link_days').' days, on any device.');
    }
}
