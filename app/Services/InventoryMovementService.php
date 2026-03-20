<?php

namespace App\Services;

use App\Interfaces\InventoryMovementServiceInterface;
use App\Models\inventoryMovement;
use App\Models\inventoryMovementItem;
use Illuminate\Support\Facades\DB;

class InventoryMovementService implements InventoryMovementServiceInterface
{
    public function __construct(
        private readonly inventoryMovement $inventoryMovement,
        // private readonly inventoryMovementItem $inventoryMovementItem,
        private readonly InventoryStockService $inventoryStockService,
    ) {}

    /**
     * Create a new inventory movement
     *
     * This is the ONLY way to modify inventory_stocks through movements.
     * Flow:
     * 1. Creates InventoryMovement record (audit trail)
     * 2. Creates InventoryMovementItem records (details)
     * 3. Calls InventoryStockService to update actual stock levels
     *
     * @param  array  $data  Movement data (type, warehouses, items)
     * @return inventoryMovement
     *
     * @throws \Exception If transaction fails or stock validation fails
     */
    public function createMovement(array $data)
    {
        try {
            $movement = null;

            DB::transaction(function () use ($data, &$movement) {
                // Step 1: Create movement record (audit trail)
                $movement = $this->inventoryMovement->create([
                    'movement_type' => $data['movement_type'],
                    'origin_warehouse_id' => $data['origin_warehouse_id'] ?? null,
                    'destination_warehouse_id' => $data['destination_warehouse_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Step 2: Create movement items and update stock
                foreach ($data['items'] as $item) {
                    $movement->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $item['quantity'] * $item['unit_price'],
                    ]);

                    // Step 3: Update actual stock levels (single source of truth)
                    $this->inventoryStockService->adjustStock(
                        $item['product_id'],
                        $item['quantity'],
                        $data['movement_type'],
                        $data
                    );
                }
            });

            return $movement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getMovementById(int $id)
    {
        return $this->inventoryMovement->with('items')->find($id);
    }

    public function listMovements(array $filters = [])
    {
        return $this->inventoryMovement->with('items')->get();
    }
}
