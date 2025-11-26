<?php

namespace App\Interfaces;

interface InventoryStockServiceInterface
{
    public function adjustStock(int $productId, int $quantity, string $movementType, array $data);
    public function listStocks(array $filters = []);
}
