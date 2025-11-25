<?php

namespace App\Services;

use App\Interfaces\WarehouseServiceInterface;
use App\Models\Warehouse;

class WarehouseService implements WarehouseServiceInterface
{
    public function getAllWarehouses()
    {
        return Warehouse::orderBy('created_at', 'desc')->paginate(request()->per_page ?? 10);
    }
    public function getWarehouseById(int|string $id)
    {
        return Warehouse::find($id);
    }
    public function createWarehouse(array $data)
    {
        return Warehouse::create($data);
    }
    public function updateWarehouse(Warehouse $warehouse, array $data)
    {
        if (!$data) {
            return false;
        }

        return $warehouse->update($data);
    }
    public function deleteWarehouse(Warehouse $warehouse)
    {
        return $warehouse->delete();
    }
}
