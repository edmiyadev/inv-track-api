<?php

namespace App\Services;

use App\Interfaces\SupplierServiceInterface;
use App\Models\Supplier;

class SupplierService implements SupplierServiceInterface
{
    public function getAllSuppliers()
    {
        return Supplier::all();
    }

    public function getSupplierById(int|string $id)
    {
        return Supplier::find($id);
    }

    public function createSupplier(array $data)
    {
        return Supplier::create($data);
    }

    public function updateSupplier(Supplier $supplier, array $data)
    {
        if (!$data) {
            return false;
        }

        return $supplier->update($data);
    }

    public function deleteSupplier(Supplier $supplier)
    {
        return $supplier->delete();
    }
}
