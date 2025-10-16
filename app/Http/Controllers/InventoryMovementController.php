<?php

namespace App\Http\Controllers;

use App\Interfaces\InventoryMovementServiceInterface;
use Illuminate\Http\Request;

class InventoryMovementController extends Controller
{
    public function __construct(private readonly InventoryMovementServiceInterface $inventoryMovementService)
    {
    }
    public function index()
    {
        $inventoryMovements = $this->inventoryMovementService->listMovements();

        return response([
            "status" => 'success',
            'message' => 'Inventory movements retrieved successfully',
            'data' => $inventoryMovements
        ]);
    }


    public function show(string $id)
    {
        $inventoryMovement = $this->inventoryMovementService->getMovementById($id);

        if (!$inventoryMovement) {
            return response([
                "status" => 'error',
                'message' => 'Inventory movement not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Inventory movement retrieved successfully',
            'data' => $inventoryMovement
        ]);
    }
}
