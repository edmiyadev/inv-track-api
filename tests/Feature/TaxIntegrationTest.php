<?php

use App\Models\Tax;
use App\Models\Product;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\ProductCategory;
use App\Interfaces\SaleServiceInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('taxes are correctly applied to sales dynamically', function () {
    // 1. Setup
    $user = User::factory()->create();
    $customer = Customer::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $category = ProductCategory::factory()->create();
    
    // Create a specific tax (e.g. 25% Tax)
    $tax = Tax::create([
        'name' => 'Super Tax',
        'percentage' => 25.00,
        'is_active' => true
    ]);

    $product = Product::factory()->create([
        'product_category_id' => $category->id,
        'price' => 100.00,
        'tax_id' => $tax->id // Default tax for product
    ]);

    $saleService = app(SaleServiceInterface::class);

    // 2. Act: Create a sale
    $saleData = [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'user_id' => $user->id,
        'items' => [
            [
                'product_id' => $product->id,
                'quantity' => 2,
                'unit_price' => 100.00,
                // tax_id will be taken from product
            ]
        ]
    ];

    $sale = $saleService->createSale($saleData);

    // 3. Assert: Calculation 2 * 100 = 200 + 25% Tax (50) = 250
    $saleItem = $sale->items->first();
    
    expect($saleItem->tax_id)->toBe($tax->id);
    expect($saleItem->tax_percentage)->toEqual('25.00');
    expect($saleItem->tax_amount)->toEqual('50.00');
    expect($saleItem->subtotal)->toEqual('250.00');
    expect($sale->total_amount)->toEqual('250.00');
});
