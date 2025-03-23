<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class FollowTagPolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }
    public function follow(?User $user): Response
    {
        return $user !== null 
            ? Response::allow() : Response::deny("You need to login first");
    }
}
