<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vote;
use App\Models\Post;
use Illuminate\Auth\Access\Response;
use Illuminate\Support\Facades\Log;

class VotePolicy
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
    public function view(User $user, Vote $vote): bool
    {
        //
    }

    /**
     * Determine whether the user can create models.
     */
    public function checkOwner(User $user, Post $post): Response
    {
        // O user não pode votar no próprio post Buissnes rules
        return $user->id !== $post->user_id 
            ? Response::allow() : Response::deny("You cannot vote your own post");;
    }

    public function create(?User $user): Response
    {
        // O user tem que estar autenticado
        return $user !== null 
            ? Response::allow() : Response::deny("You need to login first");
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Vote $vote): bool
    {
        return $user->id === $vote->user_id; // Em principio nao vai ser usado
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Vote $vote): bool
    {
        return $user->id === $vote->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Vote $vote): bool
    {
        //
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Vote $vote): bool
    {
        //
    }
}
