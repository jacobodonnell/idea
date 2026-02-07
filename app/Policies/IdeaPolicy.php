<?php

declare(strict_types=1);

namespace App\Policies;

use App\Models\Idea;
use App\Models\User;

class IdeaPolicy
{
    /**
     * Determine if the given user is the owner of the specified idea.
     *
     * @param  User  $user  The user instance to check ownership.
     * @param  Idea  $idea  The idea instance to verify against the user.
     * @return bool Returns true if the user is the owner of the idea, otherwise false.
     */
    public function workWith(User $user, Idea $idea): bool
    {
        return $idea->user->is($user);
    }
}
