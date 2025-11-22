<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy
{
    /**
     * Determine whether the user can view any models.
     * Only admins and therapists can view all consultations.
     */
    public function viewAny(User $user): bool
    {
        return $user->canManageConsultations();
    }

    /**
     * Determine whether the user can view the model.
     * Admins and therapists can view all consultations.
     */
    public function view(User $user, Consultation $consultation): bool
    {
        return $user->canManageConsultations();
    }

    /**
     * Determine whether the user can create models.
     * All users can create consultation requests.
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can update the model.
     * Only admins and therapists can update consultations.
     */
    public function update(User $user, Consultation $consultation): bool
    {
        return $user->canManageConsultations();
    }

    /**
     * Determine whether the user can delete the model.
     * Only admins can delete consultations.
     */
    public function delete(User $user, Consultation $consultation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     * Only admins can restore consultations.
     */
    public function restore(User $user, Consultation $consultation): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     * Only admins can force delete consultations.
     */
    public function forceDelete(User $user, Consultation $consultation): bool
    {
        return $user->isAdmin();
    }
}
