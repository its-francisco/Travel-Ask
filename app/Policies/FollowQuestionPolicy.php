<?php

namespace App\Policies;

use App\Models\FollowQuestion;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class FollowQuestionPolicy 
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, FollowQuestion $followQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): Response
    {
        return $user !== null
                ? Response::allow() : Response::deny("You need to login first");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, FollowQuestion $followQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, FollowQuestion $followQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, FollowQuestion $followQuestion): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, FollowQuestion $followQuestion): bool
    {
        //
    }
}
