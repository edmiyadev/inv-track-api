<?php

namespace App\Interfaces;

interface InventoryMovementServiceInterface
{
    public function createMovement(array $data);

    public function getMovementById(int $id);

    public function listMovements(array $filters = []);
}
