<?php

namespace App\Services;

use App\Interfaces\SaleServiceInterface;
use App\Models\Sale;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Enums\SaleStatusEnum;

class SaleService implements SaleServiceInterface
{
    public function __construct(private readonly Sale $sale) {}

    public function createSale(array $data): Sale
    {
        return DB::transaction(function () use ($data) {
            $totalAmount = 0;

            // 1. Crear la cabecera de la venta
            $sale = $this->sale->create([
                'customer_id' => $data['customer_id'],
                'user_id' => $data['user_id'] ?? auth()->id(),
                'warehouse_id' => $data['warehouse_id'],
                'status' => SaleStatusEnum::Draft,
                'total_amount' => 0,
            ]);

            // 2. Crear los ítems de venta
            foreach ($data['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                
                $unitPrice = $item['unit_price'] ?? $product->price;
                $quantity = $item['quantity'];
                
                // Impuestos inmutables (ej: 19% IVA)
                $taxPercentage = $item['tax_percentage'] ?? 19.00;
                $taxAmount = ($unitPrice * $quantity) * ($taxPercentage / 100);
                $subtotal = ($unitPrice * $quantity) + $taxAmount;

                $sale->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'tax_percentage' => $taxPercentage,
                    'tax_amount' => $taxAmount,
                    'subtotal' => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            // 3. Actualizar el total de la venta
            $sale->update(['total_amount' => $totalAmount]);

            return $sale->load(['items', 'customer', 'warehouse']);
        });
    }

    public function getSaleById(int|string $id): ?Sale
    {
        return $this->sale->with(['items.product', 'customer', 'warehouse', 'user'])->find($id);
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
}
