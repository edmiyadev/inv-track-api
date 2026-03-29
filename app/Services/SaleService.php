<?php

namespace App\Services;

use App\Interfaces\SaleServiceInterface;
use App\Models\Sale;
use App\Models\Product;
use App\Models\Tax;
use Illuminate\Support\Facades\DB;
use App\Enums\SaleStatusEnum;

class SaleService implements SaleServiceInterface
{
    public function __construct(private readonly Sale $sale) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;

            $sale = $this->sale->create([
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => SaleStatusEnum::Draft,
                'total_amount' => 0,
            ]);

            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $unitPrice = $item['unit_price'] ?? $product->price;
                $quantity = $item['quantity'];
                
                $taxId = $item['tax_id'] ?? $product->tax_id ?? null;
                $taxPercentage = 0;
                
                if ($taxId) {
                    $tax = Tax::find($taxId);
                    $taxPercentage = $tax ? $tax->percentage : 0;
                } else {
                    $taxPercentage = $item['tax_percentage'] ?? 19.00; // Default fallback
                }

                $taxAmount = ($unitPrice * $quantity) * ($taxPercentage / 100);
                $subtotal = ($unitPrice * $quantity) + $taxAmount;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'tax_id' => $taxId,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $sale->update(['total_amount' => $totalAmount]);

            return $sale->load(['items', 'customer', 'warehouse']);
        });
    }

    public function updateSale(Sale $sale, array $data): Sale
    {
        if ($sale->status !== SaleStatusEnum::Draft) {
            throw new \DomainException("Cannot update sale #{$sale->id} because it is not in 'draft' status.");
        }

        return DB::transaction(function () use ($sale, $data) {
            $sale->update([
                'customer_id' => $data['customer_id'] ?? $sale->customer_id,
                'warehouse_id' => $data['warehouse_id'] ?? $sale->warehouse_id,
                'user_id' => $data['user_id'] ?? $sale->user_id,
            ]);

            if (isset($data['items'])) {
                $sale->items()->delete();
                $totalAmount = 0;

                foreach ($data['items'] as $item) {
                    $product = Product::findOrFail($item['product_id']);
                    $unitPrice = $item['unit_price'] ?? $product->price;
                    $quantity = $item['quantity'];
                    
                    $taxId = $item['tax_id'] ?? $product->tax_id ?? null;
                    $taxPercentage = 0;

                    if ($taxId) {
                        $tax = Tax::find($taxId);
                        $taxPercentage = $tax ? $tax->percentage : 0;
                    } else {
                        $taxPercentage = $item['tax_percentage'] ?? 19.00;
                    }

                    $taxAmount = ($unitPrice * $quantity) * ($taxPercentage / 100);
                    $subtotal = ($unitPrice * $quantity) + $taxAmount;

                    $sale->items()->create([
                        'product_id' => $product->id,
                        'tax_id' => $taxId,
                        'quantity' => $quantity,
                        'unit_price' => $unitPrice,
                        'tax_percentage' => $taxPercentage,
                        'tax_amount' => $taxAmount,
                        'subtotal' => $subtotal,
                    ]);

                    $totalAmount += $subtotal;
                }

                $sale->update(['total_amount' => $totalAmount]);
            }

            return $sale->load(['items', 'customer', 'warehouse']);
        });
    }

    public function getSaleById(int|string $id): ?Sale
    {
        return $this->sale->with(['items.product', 'customer', 'warehouse', 'user', 'items.tax'])->find($id);
    }

    public function updateSaleStatus(Sale $sale, string $status): Sale
    {
        $sale->status = SaleStatusEnum::from($status);
        $sale->save();

        return $sale;
    }

    public function getAllSales()
    {
        return $this->sale->with(['customer', 'warehouse'])->get();
    }

    public function deleteSale(Sale $sale): bool
    {
        if ($sale->status !== SaleStatusEnum::Draft) {
            throw new \DomainException("Cannot delete sale #{$sale->id} because it is not in 'draft' status.");
        }

        return $sale->delete();
    }
}
