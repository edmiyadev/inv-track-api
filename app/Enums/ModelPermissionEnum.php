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
    case PRODUCT = 'products';
    case WAREHOUSE = 'warehouses';
    case PURCHASE = 'purchases';
    case INVENTORY_STOCK = 'inventory_stocks';
    case INVENTORY_MOVEMENT = 'inventory_movements';
}
