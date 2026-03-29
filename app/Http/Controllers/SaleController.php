<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreSaleRequest;
use App\Http\Requests\UpdateSaleStatusRequest;
use App\Interfaces\SaleServiceInterface;
use App\Models\Sale;
use App\Traits\Authorizes;

class SaleController extends Controller
{
    use Authorizes;

    public function __construct(private readonly SaleServiceInterface $saleService) {}

    public function index()
    {
        $this->authorize('viewAny', Sale::class);
        $sales = $this->saleService->getAllSales();

        return response([
            'status' => 'success',
            'message' => 'Sales retrieved successfully',
            'data' => $sales,
        ]);
    }

    public function store(StoreSaleRequest $request)
    {
        $this->authorize('create', Sale::class);
        $sale = $this->saleService->createSale($request->validated());

        if (! $sale) {
            return response([
                'status' => 'error',
                'message' => 'Error creating sale',
            ], 500);
        }

        return response([
            'status' => 'success',
            'message' => 'Sale created successfully',
            'data' => $sale,
        ], 201);
    }

    public function show(int|string $id)
    {
        $sale = $this->saleService->getSaleById($id);
        $this->authorize('view', $sale);

        if (! $sale) {
            return response([
                'status' => 'error',
                'message' => 'Sale not found',
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Sale retrieved successfully',
            'data' => $sale,
        ]);
    }

    public function updateStatus(UpdateSaleStatusRequest $request, int|string $id)
    {
        $sale = $this->saleService->getSaleById($id);
        $this->authorize('update', $sale);

        if (! $sale) {
            return response([
                'status' => 'error',
                'message' => 'Sale not found',
            ], 404);
        }

        $saleUpdated = $this->saleService->updateSaleStatus($sale, $request->validated()['status']);

        return response([
            'status' => 'success',
            'message' => 'Sale status updated successfully',
            'data' => $saleUpdated,
        ]);
    }
}
