<?php

namespace App\Services;

use App\Interfaces\InventoryMovementServiceInterface;
use App\Models\inventoryMovement;
use App\Models\inventoryMovementItem;

class InventoryMovementService implements InventoryMovementServiceInterface
{
    public function __construct(private readonly inventoryMovement $inventoryMovement, private readonly inventoryMovementItem $inventoryMovementItem)
    {
    }

    public function createMovement(array $data)
    {

        // Todo: implement creation inventory movement items and update stock levels accordingly
         $this->inventoryMovement->create($data);

         
         return;
    }

    public function getMovementById(int $id)
    {
        return $this->inventoryMovement->find($id);
    }

    public function listMovements(array $filters = [])
    {
        return $this->inventoryMovement->all();
    }


    private function createInventoryMovementItem(array $data)
    {
        return $this->inventoryMovementItem->create($data);
    }

    private function updateStockLevels(int $productId, int $quantity, string $movementType)
    {
        // Todo: implement stock level updates based on movement type
    }
}
;
