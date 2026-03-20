<?php

namespace App\Observers;

use App\Models\Purchase;
use App\Services\InventoryMovementService;

class PurchaseObserver
{
    public function __construct(private readonly InventoryMovementService $inventoryMovementService) {}

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "updated" event.
     */
    public function updated(Purchase $purchase): void
    {
        if ($purchase->wasChanged('status') && $purchase->status->from('posted')) {

            $this->inventoryMovementService->createMovement([
                'movement_type' => 'in',
                'destination_warehouse_id' => $purchase['warehouse_id'] ?? null,
                'items' => $purchase->items,
            ]);
        }
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        //
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        //
    }
}
