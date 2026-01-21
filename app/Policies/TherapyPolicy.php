<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Therapy;

class TherapyPolicy
{
    public function before($user, $ability)
    {
        // Admins can do everything
        if ($user->isAdmin() && !$user->actingAsTherapist()) {
            return true;
        }
    }

    public function viewAny(User $user)
    {
        return in_array($user->activeRole(), ['admin', 'therapist']);
    }

    /**
     * Determine if the user can view the therapy.
     * Accessible by: Admin, assigned therapist, or assigned patient
     */
    public function view(?User $user, Therapy $therapy)
    {
        // Allow guests to view published therapies (will be further restricted in controller)
        if (!$user) {
            return $therapy->published;
        }

        // Admin can view all
        if ($user->isAdmin() && !$user->actingAsTherapist()) {
            return true;
        }

        // Assigned therapist can view
        if ($therapy->therapist_id === $user->id) {
            return true;
        }

        // Assigned patient can view
        if ($therapy->assigned_patient_id === $user->id) {
            return true;
        }

        return false;
    }

    public function create(User $user)
    {
        return in_array($user->activeRole(), ['admin', 'therapist']);
    }

    public function update(User $user, Therapy $therapy)
    {
        // Admin (not in therapist mode) can update all
        if ($user->isAdmin() && !$user->actingAsTherapist()) {
            return true;
        }

        // Therapist can update their own therapies (assigned as therapist)
        if ($therapy->therapist_id === $user->id) {
            return true;
        }

        // Admin in therapist mode can update therapies they authored
        if ($user->isAdmin() && $user->actingAsTherapist() && $therapy->author_id === $user->id) {
            return true;
        }

        return false;
    }

    public function delete(User $user, Therapy $therapy)
    {
        // Admin (not in therapist mode) can delete all
        if ($user->isAdmin() && !$user->actingAsTherapist()) {
            return true;
        }

        // Therapist can delete their own therapies (assigned as therapist)
        if ($therapy->therapist_id === $user->id) {
            return true;
        }

        // Admin in therapist mode can delete therapies they authored
        if ($user->isAdmin() && $user->actingAsTherapist() && $therapy->author_id === $user->id) {
            return true;
        }

        return false;
    }
}
