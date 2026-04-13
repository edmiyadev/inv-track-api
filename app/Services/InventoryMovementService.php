<?php

namespace App\Services;

use App\Enums\MovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Interfaces\InventoryMovementServiceInterface;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use App\Models\Product;
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
    public function createMovement(array $data): InventoryMovement | null
    {
        try {
            $movement = null;

            DB::transaction(function () use ($data, &$movement) {
                // Cast movement_type to enum if it's a string
                $movementType = MovementTypeEnum::ensureEnum($data['movement_type']);

                if (in_array($movementType, [MovementTypeEnum::Out, MovementTypeEnum::Transfer], true)) {
                    $this->validateStockAvailability($data, $movementType);
                }

                // Step 1: Create movement record (audit trail)
                $movement = $this->inventoryMovement->create([
                    'movement_type' => $movementType,
                    'document_id' => $data['document_id'] ?? null,
                    'document_type' => $data['document_type'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Step 2: Create movement items and update stock
                foreach ($data['items'] as $index => $item) {
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
                        array_merge($data, ['item_index' => $index])
                    );
                }
            });

            return $movement;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    /**
     * @throws InsufficientStockException
     */
    private function validateStockAvailability(array $data, MovementTypeEnum $movementType): void
    {
        $originWarehouseId = $movementType === MovementTypeEnum::Transfer
            ? (int) $data['origin_warehouse_id']
            : (int) ($data['origin_warehouse_id'] ?? 0);

        if ($originWarehouseId <= 0) {
            return;
        }

        $insufficientItems = [];

        foreach ($data['items'] as $index => $item) {
            $productId = (int) $item['product_id'];
            $requested = (int) $item['quantity'];
            $available = (int) InventoryStock::query()
                ->where('warehouse_id', $originWarehouseId)
                ->where('product_id', $productId)
                ->lockForUpdate()
                ->value('quantity');

            if ($available >= $requested) {
                continue;
            }

            $insufficientItems[] = [
                'item_index' => $index,
                'product_id' => $productId,
                'product_name' => Product::where('id', $productId)->value('name'),
                'warehouse_id' => $originWarehouseId,
                'available' => $available,
                'requested' => $requested,
                'missing' => max(0, $requested - $available),
            ];
        }

        if (! empty($insufficientItems)) {
            throw InsufficientStockException::fromItems($insufficientItems);
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
            'items' => $originalMovement->items->map(fn($item) => [
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
