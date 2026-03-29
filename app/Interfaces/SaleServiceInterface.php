<?php

namespace App\Interfaces;

use App\Models\Sale;

interface SaleServiceInterface
{
    public function createSale(array $data): Sale;
    public function getSaleById(int|string $id): ?Sale;
    public function updateSale(Sale $sale, array $data): Sale;
    public function updateSaleStatus(Sale $sale, string $status): Sale;
    public function getAllSales();
    public function deleteSale(Sale $sale): bool;
}
