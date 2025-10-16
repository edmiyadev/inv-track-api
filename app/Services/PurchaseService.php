<?php

namespace App\Services;

use App\Interfaces\PurchaseServiceInterface;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;

class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(private readonly Purchase $purchase)
    {
    }

    public function getAllPurchases()
    {
        return $this->purchase->all();
    }

    public function getPurchaseById(int $id)
    {
        return $this->purchase->find($id);
    }


    public function createPurchase(array $data)
    {
        $purchase = new Purchase();

        try {
            DB::transaction(function () use ($data) {
                $items = $data['items'] ?? [];
                $purchaseWithoutItems = Arr::except($data, ['items']);
                $totalAmount = 0;
                $newPurchase = $this->purchase::create([...$purchaseWithoutItems, 'total_amount' => 0]);

                $linesToInsert = [];
                foreach ($items as $item) {
                    $subtotal = $item['quantity'] * $item['unit_price'];
                    $totalAmount += $subtotal;

                    $linesToInsert[] = array_merge($item, [
                        'purchase_id' => $newPurchase->id,
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'total_price' => $subtotal,
                    ]);
                }

                PurchaseItem::insert($linesToInsert);

                $newPurchase->update(attributes: ['total_amount' => $totalAmount]);
            });

        } catch (\Throwable $th) {
            return false;
        }

        return $newPurchase->with('items');

    }



    public function updatePurchase(Purchase $purchase, array $data)
    {

        if (!$data) {
            return false;
        }

        $purchase->update($data);
        return $purchase;
    }

    public function deletePurchase(Purchase $purchase)
    {
        return $purchase->delete();
    }
}