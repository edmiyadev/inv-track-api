<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum ActionEnum: string
{
    use EnumTrait;
    case VIEW_ANY = 'viewAny';
    case VIEW = 'view';
    case CREATE = 'create';
    case EDIT = 'edit';
    case DELETE = 'delete';
}
