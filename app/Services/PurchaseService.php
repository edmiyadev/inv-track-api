<?php

namespace App\Services;

use App\Interfaces\PurchaseServiceInterface;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PurchaseService implements PurchaseServiceInterface
{
    public function getAllPurchases()
    {
        return Purchase::with(["items"])->get();
    }

    public function getPurchaseById(int $id)
    {
        return Purchase::with(["items"])->find($id);
    }

    public function createPurchase(array $data)
    {
        $items = $data["items"] ?? [];
        $purchaseWithoutItems = Arr::except($data, ["items"]);
        $newPurchase = null;

        try {
            DB::transaction(function () use (
                $purchaseWithoutItems,
                $items,
                &$newPurchase,
            ) {
                $newPurchase = Purchase::create([
                    ...$purchaseWithoutItems,
                    "total_amount" => 0,
                ]);

                $linesToInsert = [];
                $totalAmount = 0;

                foreach ($items as $item) {
                    $subtotal = $item["quantity"] * $item["unit_price"];
                    $totalAmount += $subtotal;

                    $linesToInsert[] = [
                        "purchase_id" => $newPurchase->id,
                        "product_id" => $item["product_id"],
                        "quantity" => $item["quantity"],
                        "unit_price" => $item["unit_price"],
                        "total_price" => $subtotal,
                    ];
                }
                $newPurchase->items()->insert($linesToInsert);
                $newPurchase->update(["total_amount" => $totalAmount]);

                return $newPurchase->with("items");
            });

            return $newPurchase?->toArray();
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function updatePurchase(Purchase $purchase, array $data)
    {
        if (!$data) {
            return false;
        }

        $items = $data["items"] ?? null;
        $purchaseData = Arr::except($data, ["items"]);
        $updatedPurchase = $purchase;

        try {
            $updatedPurchase = DB::transaction(function () use (
                $purchase,
                $purchaseData,
                $items,
            ) {
                if (!empty($purchaseData)) {
                    $purchase->update($purchaseData);
                }

                $totalAmount = $purchase->total_amount;

                if (is_array($items)) {
                    $purchase->items()->delete();

                    $linesToInsert = [];
                    $totalAmount = 0;

                    foreach ($items as $item) {
                        $quantity = $item["quantity"] ?? 0;
                        $unitPrice = $item["unit_price"] ?? 0;
                        $subtotal = $quantity * $unitPrice;
                        $totalAmount += $subtotal;

                        $linesToInsert[] = [
                            "purchase_id" => $purchase->id,
                            "product_id" => $item["product_id"],
                            "quantity" => $quantity,
                            "unit_price" => $unitPrice,
                            "total_price" => $subtotal,
                        ];
                    }

                    if (!empty($linesToInsert)) {
                        $purchase->items()->insert($linesToInsert);
                    }

                    $purchase->update(["total_amount" => $totalAmount]);
                }
                return $purchase->with("items");
            });

            return $updatedPurchase;
        } catch (\Throwable $th) {
            return false;
        }
    }

    public function deletePurchase(Purchase $purchase)
    {
        return $purchase->delete();
    }
}
