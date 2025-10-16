<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdjustInventoryStock;
use App\Interfaces\InventoryStockServiceInterface;
use Illuminate\Http\Request;

class InventoryStockController extends Controller
{
    public function __construct(private readonly InventoryStockServiceInterface $inventoryStockService)
    {
    }

    public function index()
    {
        return $this->inventoryStockService->listStocks();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AdjustInventoryStock $request)
    {
        $this->inventoryStockService->adjustStock($request->validated());
    }
}
