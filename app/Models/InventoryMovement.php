<?php

namespace App\Models;

use App\Enums\MovementTypeEnum;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class InventoryMovement extends Model
{
    protected $fillable = [
        'movement_type',
        'document_id',
        'document_type',
        'notes',
    ];

    protected $casts = [
        'movement_type' => MovementTypeEnum::class,
    ];

    public function items(): HasMany
    {
        return $this->hasMany(InventoryMovementItem::class);
    }

    public function document(): MorphTo
    {
        return $this->morphTo();
    }
}
