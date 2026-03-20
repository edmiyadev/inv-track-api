<?php

namespace App\Models;

use App\Enums\MovementTypeEnum;
use App\Enums\PurchaseStatusEnum;
use App\Observers\PurchaseObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[ObservedBy([PurchaseObserver::class])]
class Purchase extends Model
{
    /** @use HasFactory<\Database\Factories\PurchaseFactory> */
    use HasFactory;

    protected $fillable = [
        'supplier_id',
        'warehouse_id',
        'total_amount',
        'date',
        'status',
        'notes',
    ];

    protected $casts = [
        'status' => PurchaseStatusEnum::class,
        'date' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function inventoryMovements(): HasMany
    {
        return $this->hasMany(InventoryMovement::class);
    }

    /**
     * Check if this purchase has already created an inventory movement
     */
    public function hasInventoryMovement(): bool
    {
        return $this->inventoryMovements()
            ->where('movement_type', MovementTypeEnum::In->value)
            ->exists();
    }

    /**
     * Get the original inventory movement (type='in') for this purchase
     */
    public function getOriginalInventoryMovement()
    {
        return $this->inventoryMovements()
            ->where('movement_type', MovementTypeEnum::In->value)
            ->first();
    }

    /**
     * Check if inventory for this purchase has been reversed
     */
    public function hasInventoryReversed(): bool
    {
        return $this->inventoryMovements()
            ->where('movement_type', MovementTypeEnum::Out->value)
            ->exists();
    }
}
