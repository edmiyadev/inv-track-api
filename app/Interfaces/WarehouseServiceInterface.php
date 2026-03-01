<?php

namespace App\Interfaces;

use App\Models\Warehouse;

interface WarehouseServiceInterface
{
    public function getAllWarehouses();

    public function getWarehouseById(int|string $id);

    public function createWarehouse(array $data);

    public function updateWarehouse(Warehouse $warehouse, array $data);

    public function deleteWarehouse(Warehouse $warehouse);
}
