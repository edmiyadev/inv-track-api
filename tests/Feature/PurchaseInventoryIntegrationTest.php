<?php

use App\Enums\PurchaseStatusEnum;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use App\Models\Warehouse;
use App\Models\User;
use App\Models\Supplier;
use App\Models\ProductCategory;
use App\Models\InventoryStock;
use App\Models\InventoryMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a purchase update to posted status creates a polymorphic inventory movement and updates stock', function () {
    // 1. Setup Data
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create();
    $category = ProductCategory::factory()->create();
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);

    $purchase = Purchase::create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'status' => PurchaseStatusEnum::Draft,
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    $quantity = 50;
    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => $quantity,
        'unit_price' => 2.00,
        'total_price' => 100.00,
        'tax_percentage' => 0,
        'tax_amount' => 0,
    ]);

    // 2. Act: Change status to Posted (should trigger Observer)
    $purchase->update(['status' => PurchaseStatusEnum::Posted]);

    // 3. Assert: Inventory Movement exists and is polymorphic
    $movement = InventoryMovement::where('document_id', $purchase->id)
        ->where('document_type', Purchase::class)
        ->first();

    expect($movement)->not->toBeNull();
    expect($movement->movement_type->value)->toBe('in');

    // 4. Assert: Stock was updated
    $stock = InventoryStock::where('product_id', $product->id)
        ->where('warehouse_id', $warehouse->id)
        ->first();

    expect($stock)->not->toBeNull();
    expect($stock->quantity)->toBe($quantity);
});

test('a posted purchase is immutable and cannot change its attributes', function () {
    $warehouse = Warehouse::factory()->create();
    $product = Product::factory()->create(['product_category_id' => ProductCategory::factory()->create()->id]);

    $purchase = Purchase::create([
        'supplier_id' => Supplier::factory()->create()->id,
        'warehouse_id' => $warehouse->id,
        'status' => PurchaseStatusEnum::Draft,
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => $product->id,
        'quantity' => 10,
        'unit_price' => 10.00,
        'total_price' => 100.00,
        'tax_percentage' => 0,
        'tax_amount' => 0,
    ]);

    // Post the purchase
    $purchase->update(['status' => PurchaseStatusEnum::Posted]);

    // Attempt to change an attribute (should throw DomainException)
    expect(fn() => $purchase->update(['total_amount' => 500.00]))
        ->toThrow(\DomainException::class, "is 'posted' and immutable");
});

test('a posted purchase cannot be transitioned to canceled', function () {
    $purchase = Purchase::create([
        'supplier_id' => Supplier::factory()->create()->id,
        'warehouse_id' => Warehouse::factory()->create()->id,
        'status' => PurchaseStatusEnum::Draft,
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    PurchaseItem::create([
        'purchase_id' => $purchase->id,
        'product_id' => Product::factory()->create(['product_category_id' => ProductCategory::factory()->create()->id])->id,
        'quantity' => 1,
        'unit_price' => 100.00,
        'total_price' => 100.00,
        'tax_percentage' => 0,
        'tax_amount' => 0,
    ]);

    // Post the purchase
    $purchase->update(['status' => PurchaseStatusEnum::Posted]);

    // Attempt to cancel (should throw DomainException from Observer/Enum)
    expect(fn() => $purchase->update(['status' => PurchaseStatusEnum::Canceled]))
        ->toThrow(\DomainException::class, "is 'posted' and immutable");
});

test('a posted purchase cannot be deleted', function () {
    $purchase = Purchase::create([
        'supplier_id' => Supplier::factory()->create()->id,
        'warehouse_id' => Warehouse::factory()->create()->id,
        'status' => PurchaseStatusEnum::Posted, // Created directly as posted for speed
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    // Attempt to delete (should throw DomainException from Observer)
    expect(fn() => $purchase->delete())
        ->toThrow(\DomainException::class, "Cannot delete Purchase");
    
    // Ensure it still exists in DB
    $this->assertDatabaseHas('purchases', ['id' => $purchase->id]);
});

test('a draft purchase can be canceled without creating inventory movement', function () {
    $purchase = Purchase::create([
        'supplier_id' => Supplier::factory()->create()->id,
        'warehouse_id' => Warehouse::factory()->create()->id,
        'status' => PurchaseStatusEnum::Draft,
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    // Act: Cancel from draft
    $purchase->update(['status' => PurchaseStatusEnum::Canceled]);

    // Assert: Status is canceled
    expect($purchase->status)->toBe(PurchaseStatusEnum::Canceled);

    // Assert: No movements created
    $movements = InventoryMovement::where('document_id', $purchase->id)
        ->where('document_type', Purchase::class)
        ->count();
    
    expect($movements)->toBe(0);
});

test('a canceled purchase can be transitioned back to draft', function () {
    $purchase = Purchase::create([
        'supplier_id' => Supplier::factory()->create()->id,
        'warehouse_id' => Warehouse::factory()->create()->id,
        'status' => PurchaseStatusEnum::Draft,
        'total_amount' => 100.00,
        'date' => now(),
    ]);

    $purchase->update(['status' => PurchaseStatusEnum::Canceled]);
    $purchase->update(['status' => PurchaseStatusEnum::Draft]);

    expect($purchase->fresh()->status)->toBe(PurchaseStatusEnum::Draft);
});
