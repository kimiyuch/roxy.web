<?php

namespace App\Policies;

use App\Models\Site;
use App\Models\User;

/** Everyone in an account can see and work on all of its sites (no roles beyond that yet). */
class SitePolicy
{
    public function view(User $user, Site $site): bool
    {
        return $user->account_id === $site->account_id;
    }

    public function update(User $user, Site $site): bool
    {
        return $user->account_id === $site->account_id;
    }

    public function delete(User $user, Site $site): bool
    {
        return $user->account_id === $site->account_id;
    }
}
