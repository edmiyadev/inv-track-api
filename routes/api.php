<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\SupplierController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    // dd($request->user()->with('roles')->get());
    return $request->user()->with('roles','permissions')->get();
})->middleware('auth:sanctum');

Route::prefix('auth')->group(function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('register', [AuthController::class, 'register']);
    Route::get('logout', [AuthController::class, 'logout'])->middleware(['auth:sanctum']);
});

Route::middleware(['auth:sanctum'])->group(function () {
    Route::apiResource('suppliers', SupplierController::class);
});
