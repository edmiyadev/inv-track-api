<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    /** @use HasFactory<\Database\Factories\WarehouseFactory> */
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'location',
        'descripcion',
        'is_active',
    ];

    public function inventoryStocks(): HasMany
    {
        return $this->hasMany(InventoryStock::class);
    }
}
