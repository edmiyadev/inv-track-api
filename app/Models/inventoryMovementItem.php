<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class inventoryMovementItem extends Model
{
    protected $fillable = [
        'inventory_movement_id',
        'product_id',
        'quantity',
        'unit_cost',
        'total_cost',
    ];

    public function inventoryMovement(): BelongsTo
    {
        return $this->belongsTo(inventoryMovement::class);
    }
}
