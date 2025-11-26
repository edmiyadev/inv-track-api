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
        private readonly inventoryMovementItem $inventoryMovementItem,
        private readonly InventoryStockService $inventoryStockService,
    ) {}

    public function createMovement(array $data)
    {

        try {
            DB::transaction(function () use ($data) {
                $movement = $this->inventoryMovement->create([
                    'movement_type' => $data['movement_type'],
                    'origin_warehouse_id' => $data['origin_warehouse_id'] ?? null,
                    'destination_warehouse_id' => $data['destination_warehouse_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);


                foreach ($data['items'] as $item) {
                    $movementItem = $this->inventoryMovementItem->create([
                        'inventory_movement_id' => $movement->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_cost' => $item['unit_price'],
                        'total_cost' => $item['quantity'] * $item['unit_price'],
                    ]);

                    $this->inventoryStockService->adjustStock($item['product_id'], $item['quantity'], $data['movement_type'], $data);
                }
                return $movement;
            });
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
};
