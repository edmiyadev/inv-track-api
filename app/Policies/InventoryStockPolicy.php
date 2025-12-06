<?php

namespace App\Policies;

use App\Models\InventoryStock;
use App\Models\User;

class InventoryStockPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('inventory_stocks.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, InventoryStock $inventoryStock): bool
    {
        return $user->can('inventory_stocks.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, InventoryStock $inventoryStock): bool
    {
        return $user->can('inventory_stocks.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, InventoryStock $inventoryStock): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, InventoryStock $inventoryStock): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, InventoryStock $inventoryStock): bool
    {
        return false;
    }
}
