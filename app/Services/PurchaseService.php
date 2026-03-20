<?php

namespace App\Services;

use App\Enums\PurchaseStatusEnum;
use App\Interfaces\PurchaseServiceInterface;
use App\Models\Purchase;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(private readonly InventoryMovementService $inventoryMovementService) {}

    public function getAllPurchases()
    {
        return Purchase::with(['items', 'items.product', 'supplier', 'warehouse'])->get();
    }

    public function getPurchaseById(int $id)
    {
        return Purchase::with(['items', 'items.product', 'supplier', 'warehouse'])->find($id);
    }

    public function createPurchase(array $data)
    {
        $items = $data['items'] ?? [];
        $purchaseWithoutItems = Arr::except($data, ['items']);

        return DB::transaction(function () use ($purchaseWithoutItems, $items, &$purchase) {
            $purchase = Purchase::create([
                ...$purchaseWithoutItems,
                'status' => PurchaseStatusEnum::Draft,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($items as $item) {
                $line = $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'total_price' => $item['quantity'] * $item['unit_price'],
                ]);

                $totalAmount += $line->total_price;
            }

            $purchase->update(['total_amount' => $totalAmount]);

            return $purchase;
        });
    }

    public function updatePurchase(Purchase $purchase, array $data)
    {
        if (! $data) {
            return false;
        }

        $items = $data['items'] ?? null;
        $purchaseData = Arr::except($data, ['items']);

        $updatedPurchase = DB::transaction(function () use ($purchase, $purchaseData, $items) {
            if (! empty($purchaseData)) {
                $purchase->update($purchaseData);
            }

            // Only update items if provided
            if ($items !== null) {
                $itemIds = collect($items)->pluck('id')->filter()->all();
                $purchase->items()->whereNotIn('id', $itemIds)->delete();

                $totalAmount = 0;

                foreach ($items as $item) {
                    $line = $purchase->items()->updateOrCreate(
                        ['id' => $item['id'] ?? null],
                        [
                            'product_id' => $item['product_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'total_price' => $item['quantity'] * $item['unit_price'],
                        ]
                    );

                    $totalAmount += $line->total_price;
                }

                $purchase->update(['total_amount' => $totalAmount]);
            }

            return $purchase->with('items');
        });

        return $updatedPurchase;
    }

    public function deletePurchase(Purchase $purchase)
    {
        return $purchase->delete();
    }
}
