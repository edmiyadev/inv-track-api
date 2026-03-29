<?php

namespace App\Policies;

use App\Models\Sale;
use App\Models\User;

class SalePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('sales.viewAny');
    }

    public function view(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('sales.create');
    }

    public function update(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.edit');
    }

    public function delete(User $user, Sale $sale): bool
    {
        return $user->hasPermissionTo('sales.delete');
    }
}
