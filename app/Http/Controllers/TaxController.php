<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTaxRequest;
use App\Http\Requests\UpdateTaxRequest;
use App\Interfaces\TaxServiceInterface;
use App\Models\Tax;
use App\Traits\Authorizes;

class TaxController extends Controller
{
    use Authorizes;

    public function __construct(private readonly TaxServiceInterface $taxService) {}

    public function index()
    {
        $this->authorize('viewAny', Tax::class);
        $taxes = $this->taxService->getAllTaxes();

        return response([
            'status' => 'success',
            'message' => 'Taxes retrieved successfully',
            'data' => $taxes,
        ]);
    }

    public function store(StoreTaxRequest $request)
    {
        $this->authorize('create', Tax::class);
        $tax = $this->taxService->createTax($request->validated());

        return response([
            'status' => 'success',
            'message' => 'Tax created successfully',
            'data' => $tax,
        ], 201);
    }

    public function show(int|string $id)
    {
        $tax = $this->taxService->getTaxById($id);
        $this->authorize('view', $tax);

        if (! $tax) {
            return response([
                'status' => 'error',
                'message' => 'Tax not found',
            ], 404);
        }

        return response([
            'status' => 'success',
            'message' => 'Tax retrieved successfully',
            'data' => $tax,
        ]);
    }

    public function update(UpdateTaxRequest $request, int|string $id)
    {
        $tax = $this->taxService->getTaxById($id);
        $this->authorize('update', $tax);

        if (! $tax) {
            return response([
                'status' => 'error',
                'message' => 'Tax not found',
            ], 404);
        }

        $taxUpdated = $this->taxService->updateTax($tax, $request->validated());

        return response([
            'status' => 'success',
            'message' => 'Tax updated successfully',
            'data' => $taxUpdated,
        ]);
    }

    public function destroy(int|string $id)
    {
        $tax = $this->taxService->getTaxById($id);
        $this->authorize('delete', $tax);

        if (! $tax) {
            return response([
                'status' => 'error',
                'message' => 'Tax not found',
            ], 404);
        }

        $this->taxService->deleteTax($tax);

        return response([
            'status' => 'success',
            'message' => 'Tax deleted successfully',
        ]);
    }
}
