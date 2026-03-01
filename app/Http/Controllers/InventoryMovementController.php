<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryMovementRequest;
use App\Interfaces\InventoryMovementServiceInterface;
use App\Models\inventoryMovement;
use App\Traits\Authorizes;

class InventoryMovementController extends Controller
{
    use Authorizes;

    public function __construct(
        private readonly InventoryMovementServiceInterface $inventoryMovementService
    ) {}

    /**
     * List all inventory movements
     * GET /api/inventory/movements
     */
    public function index()
    {
        $this->authorize('viewAny', inventoryMovement::class);

        $inventoryMovements = $this->inventoryMovementService->listMovements();

        return response([
            'status' => 'success',
            'message' => 'Inventory movements retrieved successfully',
            'data' => $inventoryMovements,
        ]);
    }

    /**
     * Get specific inventory movement
     * GET /api/inventory/movements/{id}
     */
    public function show(int|string $id)
    {
        $inventoryMovement = $this->inventoryMovementService->getMovementById($id);
        $this->authorize('view', $inventoryMovement);

        if (! $inventoryMovement) {
            return response([
                'status' => 'error',
                'message' => 'Inventory movement not found',
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Inventory movement retrieved successfully',
            'data' => $inventoryMovement,
        ]);
    }

    /**
     * Create a new inventory movement (transfer or adjustment)
     * POST /api/inventory/movements
     *
     * This endpoint creates movements that automatically update inventory_stocks
     * through InventoryMovementService -> InventoryStockService
     */
    public function store(CreateInventoryMovementRequest $request)
    {
        $this->authorize('create', inventoryMovement::class);

        $movement = $this->inventoryMovementService->createMovement($request->validated());

        if (! $movement) {
            return response([
                'status' => 'error',
                'message' => 'Error creating inventory movement',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Inventory movement created successfully',
            'data' => $movement,
        ], 201);
    }
}
