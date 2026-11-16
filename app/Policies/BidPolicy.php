<?php

namespace App\Policies;

use App\Models\Bid;
use App\Models\User;

class BidPolicy
{
    /**
     * Determine whether the user can view any bids.
     */
    public function viewAny(User $user): bool
    {
        return $user->is_active;
    }

    /**
     * Determine whether the user can view the bid.
     */
    public function view(User $user, Bid $bid): bool
    {
        if ($user->isVendor()) {
            return $user->vendor && $user->vendor->id === $bid->vendor_id;
        }

        if ($user->isUrusSetia()) {
            return true;
        }

        if ($user->isPenilaiTeknikal() || $user->isPenilaiKewangan()) {
            return $bid->tender->isBoxOpened();
        }

        if ($user->isSaksiPembuka()) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create bids.
     * STRICT SEGREGATION: Only verified Vendors may create bids.
     * Admins, Urus Setia, and Evaluators are strictly forbidden.
     */
    public function create(User $user): bool
    {
        if (! $user->isVendor()) {
            return false;
        }

        return (bool) $user->vendor?->isVerified();
    }

    /**
     * Determine whether the user can update the bid.
     * STRICT: Only the owning vendor may update the bid, and ONLY in 'draft' status.
     * Once sealed or submitted, no changes are permitted.
     */
    public function update(User $user, Bid $bid): bool
    {
        if (! $user->isVendor()) {
            return false;
        }

        if (! $user->vendor || $user->vendor->id !== $bid->vendor_id) {
            return false;
        }

        return $bid->status === 'draft';
    }

    /**
     * Determine whether the user can delete the bid.
     * STRICT: Only the owning vendor may delete their bid while in 'draft' status.
     */
    public function delete(User $user, Bid $bid): bool
    {
        if (! $user->isVendor()) {
            return false;
        }

        if (! $user->vendor || $user->vendor->id !== $bid->vendor_id) {
            return false;
        }

        return $bid->status === 'draft';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Bid $bid): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Bid $bid): bool
    {
        return false;
    }
}
