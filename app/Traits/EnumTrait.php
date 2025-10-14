<?php

namespace App\Traits;

use Illuminate\Support\Arr;

trait EnumTrait
{
    public static function names()
    {
        return Arr::pluck(self::cases(), 'name');
    }

    public static function values()
    {
        return Arr::pluck(self::cases(), 'value');
    }

    public static function arrayCases()
    {
        $types = [];
        foreach (self::cases() as $value) {
            $types[$value->value] = $value->name;
        }

        return $types;
    }
}
