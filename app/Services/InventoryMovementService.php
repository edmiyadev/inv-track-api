<?php

namespace App\Services;

use App\Enums\MovementTypeEnum;
use App\Interfaces\InventoryMovementServiceInterface;
use App\Models\InventoryMovement;
use Illuminate\Support\Facades\DB;

class InventoryMovementService implements InventoryMovementServiceInterface
{
    public function __construct(
        private readonly InventoryMovement $inventoryMovement,
        private readonly InventoryStockService $inventoryStockService,
    ) {}

    /**
     * Create a new inventory movement
     *
     * This is the ONLY way to modify inventory_stocks through movements.
     * Flow:
     * 1. Creates InventoryMovement record (audit trail)
     * 2. Creates InventoryMovementItem records (details)
     * 3. Calls InventoryStockService to update actual stock levels
     *
     * @param  array  $data  Movement data (type, warehouses, items, purchase_id)
     * @return InventoryMovement
     *
     * @throws \Exception If transaction fails or stock validation fails
     */
    public function createMovement(array $data): InventoryMovement
    {
        try {
            $movement = null;

            DB::transaction(function () use ($data, &$movement) {
                // Cast movement_type to enum if it's a string
                $movementType = MovementTypeEnum::ensureEnum($data['movement_type']);

                // Step 1: Create movement record (audit trail)
                $movement = $this->inventoryMovement->create([
                    'movement_type' => $movementType,
                    'document_id' => $data['document_id'] ?? null,
                    'document_type' => $data['document_type'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Step 2: Create movement items and update stock
                foreach ($data['items'] as $item) {
                    $movement->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'] ?? 0,
                        'total_price' => $item['quantity'] * ($item['unit_price'] ?? 0),
                    ]);

                    // Step 3: Update actual stock levels (single source of truth)
                    $this->inventoryStockService->adjustStock(
                        $item['product_id'],
                        $item['quantity'],
                        $movementType,
                        $data
                    );
                }
            });

            return $movement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * Create a reversal movement for a document cancellation
     *
     * @param  InventoryMovement  $originalMovement  The original movement to reverse
     * @return InventoryMovement The reversal movement
     *
     * @throws \LogicException If movement type cannot be reversed
     */
    public function createReversalMovement(InventoryMovement $originalMovement): InventoryMovement
    {
        if (! $originalMovement->movement_type->isReversible()) {
            throw new \LogicException(
                "Cannot reverse movement type: {$originalMovement->movement_type->value}"
            );
        }

        $reversalType = $originalMovement->movement_type->reverse();

        // Build reversal data
        // We need to swap origin and destination warehouses from the original data context
        // This assumes the original data context is available or we reconstruct it
        $reversalData = [
            'document_id' => $originalMovement->document_id,
            'document_type' => $originalMovement->document_type,
            'movement_type' => $reversalType,
            'notes' => "Reversal of movement #{$originalMovement->id}",
            'items' => $originalMovement->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray(),
        ];

        // For reversal, we need to know the original warehouses
        // If it was an 'In' movement to a destination, now it's an 'Out' from that destination
        if ($originalMovement->movement_type === MovementTypeEnum::In) {
            // How do we know which warehouse it was? 
            // We should probably store the warehouse_id in the movement_items or keep it in the data
            // Or extract it from the document (Purchase/Sale)
            $document = $originalMovement->document;
            if ($document instanceof \App\Models\Purchase) {
                $reversalData['origin_warehouse_id'] = $document->warehouse_id;
            }
        }

        return $this->createMovement($reversalData);
    }

    public function getMovementById(int $id)
    {
        return $this->inventoryMovement->with('items')->find($id);
    }

    public function listMovements(array $filters = [])
    {
        return $this->inventoryMovement->with('items')->paginate(15);
    }
}
