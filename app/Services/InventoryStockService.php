<?php

namespace App\Services;

use App\Interfaces\InventoryStockServiceInterface;
use App\Models\InventoryStock;

class InventoryStockService implements InventoryStockServiceInterface
{
    public function __construct(private readonly InventoryStock $inventoryStock)
    {
    }

    public function adjustStock(array $data): void
    {
        $stock = $this->inventoryStock->firstOrCreate(
            ['product_id' => $data['product_id'], 'warehouse_id' => $data['warehouse_id']],
            ['quantity' => 0]
        );

        $stock->quantity += $data['quantity'];
        $stock->save();
    }

    public function listStocks(array $filters = []): array
    {
        $query = $this->inventoryStock->newQuery();

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->get()->toArray();
    }
}
