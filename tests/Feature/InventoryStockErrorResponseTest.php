<?php

use App\Models\Customer;
use App\Models\InventoryStock;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;

uses(RefreshDatabase::class);

test('inventory movement returns structured insufficient stock payload for one item', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'inventory_movements.create', 'guard_name' => 'sanctum']);
    $user->givePermissionTo('inventory_movements.create');

    $originWarehouse = Warehouse::factory()->create();
    $destinationWarehouse = Warehouse::factory()->create();
    $category = ProductCategory::factory()->create();
    $product = Product::factory()->create(['product_category_id' => $category->id]);

    InventoryStock::create([
        'product_id' => $product->id,
        'warehouse_id' => $originWarehouse->id,
        'quantity' => 0,
        'reorder_point' => 10,
    ]);

    $payload = [
        'movement_type' => 'transfer',
        'origin_warehouse_id' => $originWarehouse->id,
        'destination_warehouse_id' => $destinationWarehouse->id,
        'items' => [[
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => 100,
        ]],
    ];

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/inventory/movements', $payload);

    $response->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('code', 'STOCK_INSUFFICIENT')
        ->assertJsonPath('errors.items.0.item_index', 0)
        ->assertJsonPath('errors.items.0.product_id', $product->id)
        ->assertJsonPath('errors.items.0.available', 0)
        ->assertJsonPath('errors.items.0.requested', 1)
        ->assertJsonPath('errors.items.0.missing', 1);
});

test('posting sale returns structured insufficient stock payload for all affected items', function () {
    $user = User::factory()->create();
    Permission::create(['name' => 'sales.create', 'guard_name' => 'sanctum']);
    Permission::create(['name' => 'sales.edit', 'guard_name' => 'sanctum']);
    $user->givePermissionTo(['sales.create', 'sales.edit']);

    $warehouse = Warehouse::factory()->create();
    $customer = Customer::factory()->create();
    $category = ProductCategory::factory()->create();

    $productA = Product::factory()->create(['product_category_id' => $category->id, 'price' => 120]);
    $productB = Product::factory()->create(['product_category_id' => $category->id, 'price' => 80]);

    InventoryStock::create([
        'product_id' => $productA->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 1,
        'reorder_point' => 10,
    ]);

    InventoryStock::create([
        'product_id' => $productB->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 0,
        'reorder_point' => 10,
    ]);

    $saleResponse = $this->actingAs($user, 'sanctum')->postJson('/api/sales', [
        'customer_id' => $customer->id,
        'warehouse_id' => $warehouse->id,
        'items' => [
            ['product_id' => $productA->id, 'quantity' => 2, 'unit_price' => 120],
            ['product_id' => $productB->id, 'quantity' => 1, 'unit_price' => 80],
        ],
    ]);

    $saleResponse->assertCreated();
    $saleId = (int) $saleResponse->json('data.id');

    $postResponse = $this->actingAs($user, 'sanctum')
        ->patchJson("/api/sales/{$saleId}/status", ['status' => 'posted']);

    $postResponse->assertStatus(422)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('code', 'STOCK_INSUFFICIENT')
        ->assertJsonPath('errors.items.0.item_index', 0)
        ->assertJsonPath('errors.items.0.product_id', $productA->id)
        ->assertJsonPath('errors.items.0.available', 1)
        ->assertJsonPath('errors.items.0.requested', 2)
        ->assertJsonPath('errors.items.0.missing', 1)
        ->assertJsonPath('errors.items.1.item_index', 1)
        ->assertJsonPath('errors.items.1.product_id', $productB->id)
        ->assertJsonPath('errors.items.1.available', 0)
        ->assertJsonPath('errors.items.1.requested', 1)
        ->assertJsonPath('errors.items.1.missing', 1);
});
