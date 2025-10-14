<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ModelPermissionEnum: string
{
    use EnumTrait;

    case USER = 'users';
    case ROLE = 'roles';
    case SUPPLIER = 'suppliers';
    case PRODUCT_CATEGORY = 'product_categories';
}
