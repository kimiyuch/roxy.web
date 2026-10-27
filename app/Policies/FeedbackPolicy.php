<?php

namespace App\Policies;

use App\Models\Feedback;
use App\Models\User;

class FeedbackPolicy
{
    public function view(User $user, Feedback $feedback): bool
    {
        return $user->account_id === $feedback->site->account_id;
    }

    public function update(User $user, Feedback $feedback): bool
    {
        return $this->view($user, $feedback);
    }

    public function delete(User $user, Feedback $feedback): bool
    {
        return $this->view($user, $feedback);
    }
}
