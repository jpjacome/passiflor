<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Therapy;

class TherapyPolicy
{
    public function before($user, $ability)
    {
        // super-admin check could go here
    }

    public function viewAny(User $user)
    {
        return in_array($user->role, ['admin', 'therapist']);
    }

    public function view(User $user, Therapy $therapy)
    {
        return in_array($user->role, ['admin', 'therapist']);
    }

    public function create(User $user)
    {
        return in_array($user->role, ['admin', 'therapist']);
    }

    public function update(User $user, Therapy $therapy)
    {
        return in_array($user->role, ['admin', 'therapist']);
    }

    public function delete(User $user, Therapy $therapy)
    {
        return in_array($user->role, ['admin', 'therapist']);
    }
}
