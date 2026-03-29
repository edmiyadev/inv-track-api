<?php

namespace App\Policies;

use App\Models\Tax;
use App\Models\User;

class TaxPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('taxes.viewAny');
    }

    public function view(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.view');
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('taxes.create');
    }

    public function update(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.edit');
    }

    public function delete(User $user, Tax $tax): bool
    {
        return $user->hasPermissionTo('taxes.delete');
    }
}
