<?php

namespace App\Policies;

use App\Models\ProductCategory;
use App\Models\User;

class ProductCategoryPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('product_categories.viewAny');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product_categories.view');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('product_categories.create');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product_categories.edit');
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, ProductCategory $productCategory): bool
    {
        return $user->can('product_categories.delete');
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, ProductCategory $productCategory): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, ProductCategory $productCategory): bool
    {
        return false;
    }
}
