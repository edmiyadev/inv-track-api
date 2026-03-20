<?php

namespace App\Models;

use App\Enums\MovementTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InventoryMovement extends Model
{
    protected $fillable = [
        'purchase_id',
        'movement_type',
        'origin_warehouse_id',
        'destination_warehouse_id',
        'notes',
    ];

    protected $casts = [
        'movement_type' => MovementTypeEnum::class,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryMovementItem::class);
    }

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function originWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'origin_warehouse_id');
    }

    public function destinationWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'destination_warehouse_id');
    }
}
