<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{

    public function edit(User $user, User $pf_owner): bool
    {
        return $user->id === $pf_owner->id || $user->isAdmin();
    }

    public function delete(User $user, User $pf_owner): bool
    {
        return $user->id === $pf_owner->id || $user->isAdmin();
    }
    public function block(User $user, User $pf_owner): bool
    {
        return $user->isAdmin() && !$pf_owner->isAdmin();
    }

    public function unblock(User $user, User $pf_owner): bool
    {
        return $user->isAdmin();
    }

    public function create(User $user): bool
    {
        return $user->isAdmin();
    }

    public function accessAdmin(User $user)
    {
        return $user->isAdmin();
    }
}
