<?php

namespace App\Interfaces;

use App\Models\Purchase;

interface PurchaseServiceInterface
{
    public function getAllPurchases();

    public function getPurchaseById(int|string $purchaseId): ?Purchase;

    public function createPurchase(array $data): ?Purchase;

    public function updatePurchase(Purchase $purchase, array $data): ?Purchase;

    public function deletePurchase(Purchase $purchase): bool;

    public function updatePurchaseStatus(Purchase $purchase, string $status): ?Purchase;
}
