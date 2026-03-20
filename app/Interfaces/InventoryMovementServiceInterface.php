<?php

namespace App\Interfaces;

use App\Models\InventoryMovement;

interface InventoryMovementServiceInterface
{
    public function createMovement(array $data): InventoryMovement;

    public function createReversalMovement(InventoryMovement $originalMovement): InventoryMovement;

    public function getMovementById(int $id);

    public function listMovements(array $filters = []);
}
