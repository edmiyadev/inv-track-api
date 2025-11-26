<?php

namespace App\Services;

use App\Interfaces\InventoryStockServiceInterface;
use App\Models\InventoryStock;

class InventoryStockService implements InventoryStockServiceInterface
{
    public function __construct(private readonly InventoryStock $inventoryStock) {}

    public function adjustStock(int $productId, int $quantity, string $movementType, array $data): void
    {
        switch ($movementType) {
            case 'in':
                $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
                break;

            case 'out':
                $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity);
                break;

            // case 'transfer':
            //     $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity);
            //     $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
            //     break;

            // case 'adjustment':
            //     $this->incrementStock($data['origin_warehouse_id'], $productId, $quantity);
            //     break;

            default:
                throw new \InvalidArgumentException("type movement not found: $movementType");
        }
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

    // private function updateStockLevels(int $productId, int $quantity, string $movementType, array $data)
    // {
    //     if ($movementType === 'in') {
    //         $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
    //     } elseif ($movementType === 'out') {
    //         $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity);
    //     } elseif ($movementType === 'transfer') {
    //         $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity);
    //         $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
    //     } elseif ($movementType === 'adjustment') {
    //         // Assuming adjustment applies to origin_warehouse_id
    //         // If quantity is positive, it adds. If negative (if allowed), it subtracts.
    //         // For now, we treat it as an absolute addition to origin.
    //         // Real-world adjustments might need more nuance (e.g. "set to X" vs "add X").
    //         $this->incrementStock($data['origin_warehouse_id'], $productId, $quantity);
    //     }
    // }

    private function incrementStock(int $warehouseId, int $productId, int $quantity)
    {
        $stock = InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0]
        );
        dd($stock);
        $stock->increment('quantity', $quantity);
    }

    private function decrementStock(int $warehouseId, int $productId, int $quantity)
    {
        $stock = InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0]
        );
        $stock->decrement('quantity', $quantity);
    }
}
