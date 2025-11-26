<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateReorderPointRequest;
use App\Interfaces\InventoryStockServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryStockController extends Controller
{
    public function __construct(
        private readonly InventoryStockServiceInterface $inventoryStockService
    ) {}

    /**
     * List all stocks with optional filters
     * GET /api/inventory/stocks?product_id=1&warehouse_id=2
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'product_id' => 'nullable|exists:products,id',
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $filters = $request->only(['product_id', 'warehouse_id']);
            $stocks = $this->inventoryStockService->listStocks($filters);

            return response()->json([
                'success' => true,
                'message' => 'Stocks retrieved successfully',
                'data' => $stocks
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving stocks',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get stock by specific warehouse
     * GET /api/inventory/stocks/warehouse/{warehouseId}
     */
    public function getByWarehouse(int $warehouseId): JsonResponse
    {
        try {
            $stocks = $this->inventoryStockService->getStockByWarehouse($warehouseId);

            return response()->json([
                'success' => true,
                'message' => 'Warehouse stock retrieved successfully',
                'data' => $stocks
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving warehouse stock',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Get products needing reorder (low stock)
     * GET /api/inventory/stocks/low-stock?warehouse_id=1
     */
    public function lowStock(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'warehouse_id' => 'nullable|exists:warehouses,id'
            ]);

            $warehouseId = $request->query('warehouse_id');
            $products = $this->inventoryStockService->getProductsNeedingReorder($warehouseId);

            return response()->json([
                'success' => true,
                'message' => 'Low stock products retrieved successfully',
                'data' => $products,
                'count' => count($products)
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving low stock products',
                'error' => $th->getMessage()
            ], 500);
        }
    }

    /**
     * Set or update reorder point for a product in a warehouse
     * PUT /api/inventory/stocks/reorder-point
     * 
     * Note: This endpoint only modifies reorder_point configuration,
     * it does NOT modify stock quantities. Stock changes must go through movements.
     */
    public function setReorderPoint(UpdateReorderPointRequest $request): JsonResponse
    {
        try {
            $validated = $request->validated();
            
            $stock = $this->inventoryStockService->setReorderPoint(
                $validated['warehouse_id'],
                $validated['product_id'],
                $validated['reorder_point']
            );

            return response()->json([
                'success' => true,
                'message' => 'Reorder point updated successfully',
                'data' => $stock
            ]);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage()
            ], 500);
        }
    }
}
