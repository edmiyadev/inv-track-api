<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class inventoryMovement extends Model
{
    protected $fillable = [
        'movement_type',
        'origin_warehouse_id',
        'destination_warehouse_id',
        'notes',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(inventoryMovementItem::class);
    }
}
