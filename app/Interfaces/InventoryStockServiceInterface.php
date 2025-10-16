<?php

namespace App\Interfaces;

interface InventoryStockServiceInterface
{
    public function adjustStock(array $data);
    public function listStocks(array $filters = []);
}
