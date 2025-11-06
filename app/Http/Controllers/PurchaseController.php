<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePurchaseRequest;
use App\Http\Requests\UpdatePurchaseRequest;
use App\Interfaces\PurchaseServiceInterface;
use App\Models\Purchase;

class PurchaseController extends Controller
{
    public function __construct(private readonly PurchaseServiceInterface $purchaseService)
    {
    }

    public function index()
    {
        $purchases = $this->purchaseService->getAllPurchases();
        return response([
            "status" => 'success',
            'message' => 'Purchases retrieved successfully',
            'data' => $purchases
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePurchaseRequest $request)
    {
        $purchase = $this->purchaseService->createPurchase($request->validated());

        if (!$purchase) {
            return response([
                "status" => 'error',
                'message' => 'Error creating purchase'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Purchase created successfully',
            'data' => $purchase
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(int|string $purchaseId)
    {
        $purchase = $this->purchaseService->getPurchaseById($purchaseId);
        // implement authorization here

        if (!$purchase) {
            return response([
                "status" => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }

        return response([
            "status" => 'success',
            'message' => 'Purchase retrieved successfully',
            'data' => $purchase
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePurchaseRequest $request, int|string $purchaseId)
    {

        $purchase = $this->purchaseService->getPurchaseById($purchaseId);
        // implement authorization here
        if (!$purchase) {
            return response([
                "status" => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }
        $purchaseUpdated = $this->purchaseService->updatePurchase($purchase, $request->validated());

        if (!$purchaseUpdated) {
            return response([
                "status" => 'error',
                'message' => 'Error updating purchase'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Purchase updated successfully',
            'data' => $purchase
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int|string $purchaseId)
    {

        $purchase = $this->purchaseService->getPurchaseById($purchaseId);
        // implement authorization here

        if (!$purchase) {
            return response([
                "status" => 'error',
                'message' => 'Purchase not found'
            ], 404);
        }

        if (!$this->purchaseService->deletePurchase($purchase)) {
            return response([
                "status" => 'error',
                'message' => 'Error deleting purchase'
            ], 500);
        }

        return response([
            "status" => 'success',
            'message' => 'Purchase deleted successfully'
        ]);
    }
}
