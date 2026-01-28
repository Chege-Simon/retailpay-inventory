<?php

namespace App\Policies;

use App\Models\Branch;
use App\Models\Sale;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SalePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Sale $sale): bool
    {
        return $user->role === 'admin' 
            || ($user->role === 'branch_manager' && $user->branch_id === $sale->store->branch_id) 
            || ($user->role === 'store_manager' && $user->store_id === $sale->store_id);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user, Store $store): bool
    {
        return $user->role === 'admin' 
            || ($user->role === 'branch_manager' && $user->branch_id === $store->branch_id) 
            || ($user->role === 'store_manager' && $user->store_id === $store->id);
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Sale $sale): bool
    {
        return $this->view($user, $sale);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Sale $sale): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Sale $sale): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Sale $sale): bool
    {
        return false;
    }
}
