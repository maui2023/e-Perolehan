<?php

namespace App\Policies;

use App\Models\Tender;
use App\Models\User;

class TenderPolicy
{
    /**
     * Determine whether the user can view any tenders.
     */
    public function viewAny(?User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the specific tender.
     */
    public function view(?User $user, Tender $tender): bool
    {
        if (in_array($tender->status, ['published', 'closed', 'evaluating', 'awarded'])) {
            return true;
        }

        if (! $user) {
            return false;
        }

        return $user->isUrusSetia();
    }

    /**
     * Determine whether the user can create tenders.
     * STRICT: Only Admin and Urus Setia may create tenders.
     * Vendors and Evaluators are strictly forbidden.
     */
    public function create(User $user): bool
    {
        return $user->isUrusSetia();
    }

    /**
     * Determine whether the user can update the tender.
     * STRICT: Only Admin and Urus Setia may update tenders.
     */
    public function update(User $user, Tender $tender): bool
    {
        return $user->isUrusSetia();
    }

    /**
     * Determine whether the user can delete the tender.
     * STRICT: Only Admin may delete tenders.
     */
    public function delete(User $user, Tender $tender): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Tender $tender): bool
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Tender $tender): bool
    {
        return $user->isAdmin();
    }
}
