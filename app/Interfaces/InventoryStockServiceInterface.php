<?php

namespace App\Interfaces;

use App\Models\InventoryStock;

interface InventoryStockServiceInterface
{
    public function adjustStock(int $productId, int $quantity, string $movementType, array $data): void;

    public function listStocks(array $filters = []): array;

    public function setReorderPoint(int $warehouseId, int $productId, int $reorderPoint): InventoryStock;

    public function getStockByWarehouse(int $warehouseId): array;

    public function getProductsNeedingReorder(?int $warehouseId = null): array;
}
