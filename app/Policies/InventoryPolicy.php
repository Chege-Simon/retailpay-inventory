<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\Store;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class InventoryPolicy
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
    public function view(User $user, Inventory $inventory): bool
    {
        return $user->role === 'admin' 
        || ($user->role === 'branch_manager' && $user->branch_id === $inventory->store->branch_id) 
        || ($user->role === 'store_manager' && $user->store_id === $inventory->store_id);
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
    public function update(User $user, Inventory $inventory): bool
    {
        return $this->view($user, $inventory);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Inventory $inventory): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Inventory $inventory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Inventory $inventory): bool
    {
        return false;
    }
}
