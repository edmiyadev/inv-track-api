<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWarehouseRequest;
use App\Http\Requests\UpdateWarehouseRequest;
use App\Interfaces\WarehouseServiceInterface;
use App\Models\Warehouse;
use App\Traits\Authorizes;

class WarehouseController extends Controller
{
    use Authorizes;

    protected readonly WarehouseServiceInterface $warehouseService;

    public function __construct(WarehouseServiceInterface $warehouseService)
    {
        $this->warehouseService = $warehouseService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', Warehouse::class);

        $warehouses = $this->warehouseService->getAllWarehouses();
        return response([
            "status" => 'success',
            'message' => 'Warehouses retrieved successfully',
            'data' => $warehouses
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreWarehouseRequest $request)
    {
        $this->authorize('create', Warehouse::class);

        $warehouse = $this->warehouseService->createWarehouse($request->validated());

        if (!$warehouse) {
            return response([
                "status" => 'error',
                'message' => 'Error creating warehouse'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Warehouse created successfully',
            'data' => $warehouse
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $warehouseId)
    {
        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        $this->authorize('view', $warehouse);

        if (!$warehouse) {
            return response([
                "status" => 'error',
                'message' => 'Warehouse not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Warehouse retrieved successfully',
            'data' => $warehouse
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateWarehouseRequest $request, int|string $warehouseId)
    {

        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        $this->authorize('update', $warehouse);

        if (!$warehouse) {
            return response([
                "status" => 'error',
                'message' => 'Warehouse not found'
            ], 404);
        }

        $warehouseUpdated = $this->warehouseService->updateWarehouse($warehouse, $request->validated());

        if (!$warehouseUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating warehouse'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Warehouse updated successfully'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $warehouseId)
    {
        $warehouse = $this->warehouseService->getWarehouseById($warehouseId);
        $this->authorize('delete', $warehouse);

        if (!$warehouse) {
            return response([
                "status" => 'error',
                'message' => 'Warehouse not found'
            ], 404);
        }

        if (!$this->warehouseService->deleteWarehouse($warehouse)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting warehouse'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Warehouse deleted successfully'
        ]);
    }
}
