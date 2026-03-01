<?php

namespace App\Interfaces;

use App\Models\Supplier;

interface SupplierServiceInterface
{
    public function getAllSuppliers();

    public function getSupplierById(int|string $id);

    public function createSupplier(array $data);

    public function updateSupplier(Supplier $supplier, array $data);

    public function deleteSupplier(Supplier $supplier);
}
