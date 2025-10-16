<?php

namespace App\Interfaces;

use App\Models\Purchase;

interface PurchaseServiceInterface
{
    public function getAllPurchases();
    public function createPurchase(array $data);
    public function getPurchaseById(int $id);
    public function updatePurchase(Purchase $purchase, array $data);
    public function deletePurchase(Purchase $purchase);

}
