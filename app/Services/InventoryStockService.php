<?php

namespace App\Services;

use App\Enums\MovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Interfaces\InventoryStockServiceInterface;
use App\Models\InventoryStock;
use App\Models\Product;

class InventoryStockService implements InventoryStockServiceInterface
{
    public function __construct(
        private readonly InventoryStock $inventoryStock
    ) {}

    public function adjustStock(int $productId, int $quantity, MovementTypeEnum|string $movementType, array $data): void
    {
        // Convert string to enum if needed (for backwards compatibility)
        $type = MovementTypeEnum::ensureEnum($movementType);

        switch ($type) {
            case MovementTypeEnum::In:
                $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
                break;

            case MovementTypeEnum::Out:
                $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity, $data);
                break;

            case MovementTypeEnum::Transfer:
                $this->decrementStock($data['origin_warehouse_id'], $productId, $quantity, $data);
                $this->incrementStock($data['destination_warehouse_id'], $productId, $quantity);
                break;

            case MovementTypeEnum::Adjustment:
                // Para ajustes, permitimos ajuste positivo o negativo
                if ($quantity >= 0) {
                    $this->incrementStock($data['destination_warehouse_id'], $productId, abs($quantity));
                } else {
                    $this->decrementStock($data['destination_warehouse_id'], $productId, abs($quantity), $data);
                }
                break;

            default:
                throw new \InvalidArgumentException("Unknown movement type: {$type->value}");
        }
    }

    public function listStocks(array $filters = []): array
    {
        $query = $this->inventoryStock->newQuery();

        if (isset($filters['product_id'])) {
            $query->where('product_id', $filters['product_id']);
        }

        if (isset($filters['warehouse_id'])) {
            $query->where('warehouse_id', $filters['warehouse_id']);
        }

        return $query->with(['product', 'warehouse'])->get()->toArray();
    }

    private function incrementStock(int $warehouseId, int $productId, int $quantity)
    {
        $stock = InventoryStock::firstOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['quantity' => 0, 'reorder_point' => 10]
        );

        $stock->increment('quantity', $quantity);
    }

    private function decrementStock(int $warehouseId, int $productId, int $quantity, array $data = []): void
    {
        $stock = InventoryStock::where('warehouse_id', $warehouseId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();

        if (! $stock) {
            $stock = InventoryStock::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'quantity' => 0,
                'reorder_point' => 10,
            ]);
        }

        // Validar que no quede negativo
        if ($stock->quantity < $quantity) {
            $productName = Product::where('id', $productId)->value('name');
            $itemIndex = isset($data['item_index']) ? (int) $data['item_index'] : null;

            throw InsufficientStockException::fromData(
                productId: $productId,
                productName: $productName,
                warehouseId: $warehouseId,
                available: (int) $stock->quantity,
                requested: $quantity,
                itemIndex: $itemIndex,
            );
        }

        $stock->decrement('quantity', $quantity);
    }

    public function setReorderPoint(int $warehouseId, int $productId, int $reorderPoint): InventoryStock
    {
        return InventoryStock::updateOrCreate(
            ['warehouse_id' => $warehouseId, 'product_id' => $productId],
            ['reorder_point' => $reorderPoint]
        );
    }

    public function getStockByWarehouse(int $warehouseId): array
    {
        return InventoryStock::where('warehouse_id', $warehouseId)
            ->with('product')
            ->get()
            ->map(fn ($stock) => [
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->name,
                'product_sku' => $stock->product->sku,
                'quantity' => $stock->quantity,
                'reorder_point' => $stock->reorder_point,
                'needs_reorder' => $stock->quantity <= $stock->reorder_point,
                'status' => $stock->quantity <= $stock->reorder_point ? 'low_stock' : 'ok',
            ])
            ->toArray();
    }

    public function getProductsNeedingReorder(?int $warehouseId = null): array
    {
        $query = InventoryStock::whereRaw('quantity <= reorder_point')
            ->with(['product', 'warehouse']);

        if ($warehouseId) {
            $query->where('warehouse_id', $warehouseId);
        }

        return $query->get()
            ->map(fn ($stock) => [
                'product_id' => $stock->product_id,
                'product_name' => $stock->product->name,
                'product_sku' => $stock->product->sku,
                'warehouse_id' => $stock->warehouse_id,
                'warehouse_name' => $stock->warehouse->name,
                'quantity' => $stock->quantity,
                'reorder_point' => $stock->reorder_point,
                'deficit' => $stock->reorder_point - $stock->quantity,
            ])
            ->toArray();
    }
}
