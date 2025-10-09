<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Interfaces\SupplierServiceInterface;
use App\Models\Supplier;

class SupplierController extends Controller
{
    protected readonly SupplierServiceInterface $supplierService;
    public function __construct(SupplierServiceInterface $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $suppliers = $this->supplierService->getAllSuppliers();
        return response([
            "status" => 'success',
            'message' => 'Suppliers retrieved successfully',
            'data' => $suppliers
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupplierRequest $request)
    {
        $supplier = $this->supplierService->createSupplier($request->validated());

        if (!$supplier) {
            return response([
                "status" => 'error',
                'message' => 'Error creating supplier'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Supplier created successfully',
            'data' => $supplier
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $supplierId)
    {
        $supplier = $this->supplierService->getSupplierById($supplierId);

        if (!$supplier) {
            return response([
                "status" => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Supplier retrieved successfully',
            'data' => $supplier
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSupplierRequest $request, int|string $supplierId)
    {
        $supplier = $this->supplierService->getSupplierById($supplierId);

        if (!$supplier) {
            return response([
                "status" => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }

        $supplierUpdated = $this->supplierService->updateSupplier($supplier, $request->validated());

        if (!$supplierUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating supplier'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Supplier updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $supplierId)
    {
        $supplier = $this->supplierService->getSupplierById($supplierId);

        if (!$supplier) {
            return response([
                "status" => 'error',
                'message' => 'Supplier not found'
            ], 404);
        }

        if (!$this->supplierService->deleteSupplier($supplier)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting supplier'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Supplier deleted successfully'
        ]);
    }
}
