<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\InventoryStockController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProductCategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StockMovementController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WarehouseController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user()->load('roles.permissions');
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::prefix('settings')->group(function () {
        Route::apiResource('users', UserController::class);
        Route::apiResource('roles', RoleController::class);
        Route::get('permissions', [PermissionController::class, 'index']);
    });

    Route::prefix('inventory')->group(function () {
        Route::apiResource('stocks', InventoryStockController::class)->only(['index', 'store']); //Todo: retire store if not needed
        Route::apiResource('movements', StockMovementController::class)->except(['update', 'destroy']);
    });

    Route::apiResource('suppliers', SupplierController::class);
    Route::apiResource('products', ProductController::class);
    Route::apiResource('categories', ProductCategoryController::class);
    Route::apiResource('warehouses', WarehouseController::class);
});
