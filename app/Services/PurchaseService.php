<?php

namespace App\Services;

use App\Enums\PurchaseStatusEnum;
use App\Interfaces\PurchaseServiceInterface;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Facades\DB;

class PurchaseService implements PurchaseServiceInterface
{
    public function __construct(private readonly Purchase $purchase) {}

    public function getAllPurchases()
    {
        return $this->purchase->with(['supplier', 'warehouse'])->get();
    }

    public function getPurchaseById(int|string $purchaseId): ?Purchase
    {
        return $this->purchase->with(['items.product', 'supplier', 'warehouse'])->find($purchaseId);
    }

    public function createPurchase(array $data): ?Purchase
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;

            $purchase = $this->purchase->create([
                'supplier_id' => $data['supplier_id'],
                'warehouse_id' => $data['warehouse_id'],
                'status' => PurchaseStatusEnum::Draft,
                'date' => $data['date'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $taxPercentage = $item['tax_percentage'] ?? 0;
                $taxAmount = ($item['unit_price'] * $item['quantity']) * ($taxPercentage / 100);
                $totalPrice = ($item['unit_price'] * $item['quantity']) + $taxAmount;

                $purchase->items()->create([
                    'product_id' => $item['product_id'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'total_price' => $totalPrice,
                ]);

                $totalAmount += $totalPrice;
            }

            $purchase->update(['total_amount' => $totalAmount]);

            return $purchase->load(['items.product', 'supplier', 'warehouse']);
        });
    }

    public function updatePurchase(Purchase $purchase, array $data): ?Purchase
    {
        // 1. Guard (Last line of defense in service)
        if ($purchase->status !== PurchaseStatusEnum::Draft) {
            throw new \DomainException("Cannot update purchase #{$purchase->id} because it is not in 'draft' status.");
        }

        return DB::transaction(function () use ($purchase, $data) {
            // 2. Update basic fields
            $purchase->update([
                'supplier_id' => $data['supplier_id'] ?? $purchase->supplier_id,
                'warehouse_id' => $data['warehouse_id'] ?? $purchase->warehouse_id,
                'date' => $data['date'] ?? $purchase->date,
                'notes' => $data['notes'] ?? $purchase->notes,
            ]);

            // 3. Update items if provided
            if (isset($data['items'])) {
                $purchase->items()->delete();
                $totalAmount = 0;

                foreach ($data['items'] as $item) {
                    $taxPercentage = $item['tax_percentage'] ?? 0;
                    $taxAmount = ($item['unit_price'] * $item['quantity']) * ($taxPercentage / 100);
                    $totalPrice = ($item['unit_price'] * $item['quantity']) + $taxAmount;

                    $purchase->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $item['unit_price'],
                        'tax_percentage' => $taxPercentage,
                        'tax_amount' => $taxAmount,
                        'total_price' => $totalPrice,
                    ]);

                    $totalAmount += $totalPrice;
                }

                $purchase->update(['total_amount' => $totalAmount]);
            }

            return $purchase->load(['items.product', 'supplier', 'warehouse']);
        });
    }

    public function deletePurchase(Purchase $purchase): bool
    {
        if ($purchase->status !== PurchaseStatusEnum::Draft) {
            throw new \DomainException("Cannot delete purchase #{$purchase->id} because it is not in 'draft' status.");
        }

        return $purchase->delete();
    }
}
