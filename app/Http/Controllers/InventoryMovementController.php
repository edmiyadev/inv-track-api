<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateInventoryMovementRequest;
use App\Interfaces\InventoryMovementServiceInterface;
use Illuminate\Http\JsonResponse;

class InventoryMovementController extends Controller
{
    public function __construct(
        private readonly InventoryMovementServiceInterface $inventoryMovementService
    ) {}

    /**
     * List all inventory movements
     * GET /api/inventory/movements
     */
    public function index(): JsonResponse
    {
        try {
            $inventoryMovements = $this->inventoryMovementService->listMovements();

            return response()->json([
                'success' => true,
                'message' => 'Inventory movements retrieved successfully',
                'data' => $inventoryMovements
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inventory movements',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get specific inventory movement
     * GET /api/inventory/movements/{id}
     */
    public function show(int $id): JsonResponse
    {
        try {
            $inventoryMovement = $this->inventoryMovementService->getMovementById($id);

            if (!$inventoryMovement) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inventory movement not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Inventory movement retrieved successfully',
                'data' => $inventoryMovement
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving inventory movement',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Create a new inventory movement (transfer or adjustment)
     * POST /api/inventory/movements
     * 
     * This endpoint creates movements that automatically update inventory_stocks
     * through InventoryMovementService -> InventoryStockService
     */
    public function store(CreateInventoryMovementRequest $request): JsonResponse
    {
        try {
            $movement = $this->inventoryMovementService->createMovement($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Inventory movement created successfully',
                'data' => $movement
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
