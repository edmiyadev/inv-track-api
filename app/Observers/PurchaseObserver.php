<?php

namespace App\Observers;

use App\Enums\MovementTypeEnum;
use App\Enums\PurchaseStatusEnum;
use App\Models\Purchase;
use App\Services\InventoryMovementService;
use Illuminate\Support\Facades\Log;

class PurchaseObserver
{
    public function __construct(
        private readonly InventoryMovementService $inventoryMovementService
    ) {}

    /**
     * Handle the Purchase "created" event.
     */
    public function created(Purchase $purchase): void
    {
        // No action needed on creation
    }

    /**
     * Handle the Purchase "updating" event (before save)
     *
     * This validates state transitions before they happen
     */
    public function updating(Purchase $purchase): void
    {
        $oldValue = $purchase->getOriginal('status');
        $oldStatus = $oldValue instanceof PurchaseStatusEnum 
            ? $oldValue 
            : PurchaseStatusEnum::from($oldValue);

        // Rule 1: Once Posted, NOTHING can be changed (status or attributes)
        if ($oldStatus === PurchaseStatusEnum::Posted) {
            throw new \DomainException(
                "Purchase #{$purchase->id} is 'posted' and immutable. ".
                "Use inventory adjustments to correct discrepancies."
            );
        }

        // Rule 2: Validate state transitions from Draft
        if ($purchase->isDirty('status')) {
            $newStatus = $purchase->status;

            if (! $oldStatus->canTransitionTo($newStatus)) {
                throw new \DomainException($oldStatus->getTransitionErrorMessage($newStatus));
            }
        }
    }

    /**
     * Handle the Purchase "updated" event (after save)
     *
     * This handles inventory movements based on status transitions
     */
    public function updated(Purchase $purchase): void
    {
        // Only proceed if status was actually changed
        if (! $purchase->wasChanged('status')) {
            return;
        }

        // Get the old and new status values
        $oldValue = $purchase->getOriginal('status');
        $oldStatus = $oldValue instanceof PurchaseStatusEnum 
            ? $oldValue 
            : PurchaseStatusEnum::from($oldValue);
        
        $newStatus = $purchase->status;

        Log::info("Purchase #{$purchase->id} status changed from {$oldStatus->value} to {$newStatus->value}");

        // Handle transition to 'posted' status (create inventory movement)
        if ($newStatus === PurchaseStatusEnum::Posted) {
            $this->handlePostTransition($purchase, $oldStatus);
        }

        // Handle transition to 'canceled' status (reverse inventory if needed)
        if ($newStatus === PurchaseStatusEnum::Canceled) {
            $this->handleCancelTransition($purchase, $oldStatus);
        }
    }

    /**
     * Handle transition to Posted status
     */
    private function handlePostTransition(Purchase $purchase, PurchaseStatusEnum $fromStatus): void
    {
        // Validate warehouse exists
        if (! $purchase->warehouse_id) {
            throw new \DomainException(
                "Cannot post purchase #{$purchase->id}: warehouse_id is required"
            );
        }

        // Prevent duplicate inventory movements
        if ($purchase->hasInventoryMovement()) {
            Log::warning(
                "Purchase #{$purchase->id} already has inventory movement, skipping creation"
            );

            return;
        }

        // Load items with product relationship (only if not already loaded)
        $purchase->loadMissing(['items', 'items.product']);

        // Validate items exist
        if ($purchase->items->isEmpty()) {
            throw new \DomainException(
                "Cannot post purchase #{$purchase->id}: no items found"
            );
        }

        // Create inventory movement (incoming stock)
        $this->inventoryMovementService->createMovement([
            'document_id' => $purchase->id,
            'document_type' => Purchase::class,
            'movement_type' => MovementTypeEnum::In,
            'destination_warehouse_id' => $purchase->warehouse_id,
            'notes' => "Purchase #{$purchase->id} posted",
            'items' => $purchase->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray(),
        ]);

        Log::info("Created inventory movement for purchase #{$purchase->id}");
    }

    /**
     * Handle transition to Canceled status
     */
    private function handleCancelTransition(Purchase $purchase, PurchaseStatusEnum $fromStatus): void
    {
        // Only reverse inventory if the purchase was previously posted
        if ($fromStatus !== PurchaseStatusEnum::Posted) {
            Log::info(
                "Purchase #{$purchase->id} canceled from '{$fromStatus->value}' status, ".
                'no inventory reversal needed'
            );

            return;
        }

        // Load all movements once to avoid N+1 queries
        $purchase->loadMissing('inventoryMovements');
        $movements = $purchase->inventoryMovements;

        // Check if inventory has already been reversed
        $hasReversed = $movements->where('movement_type', MovementTypeEnum::Out->value)->isNotEmpty();
        if ($hasReversed) {
            Log::warning(
                "Purchase #{$purchase->id} inventory already reversed, skipping"
            );

            return;
        }

        // Get the original inventory movement
        $originalMovement = $movements
            ->where('movement_type', MovementTypeEnum::In->value)
            ->first();

        if (! $originalMovement) {
            Log::error(
                "Purchase #{$purchase->id} was posted but no inventory movement found, ".
                'cannot reverse inventory'
            );

            return;
        }

        // Load items for the reversal movement if not already loaded
        $originalMovement->loadMissing('items');

        // Create reversal movement
        $this->inventoryMovementService->createReversalMovement($originalMovement);

        Log::info(
            "Created reversal movement for purchase #{$purchase->id}, ".
            "reversing movement #{$originalMovement->id}"
        );
    }

    public function deleting(Purchase $purchase): void
    {
        if ($purchase->status === PurchaseStatusEnum::Posted) {
            throw new \DomainException(
                "Cannot delete Purchase #{$purchase->id} as it is already 'posted'. ".
                "Use credit notes or manual inventory adjustments for corrections."
            );
        }
    }

    /**
     * Handle the Purchase "deleted" event.
     */
    public function deleted(Purchase $purchase): void
    {
        // ... Log deleted logic
    }

    /**
     * Handle the Purchase "restored" event.
     */
    public function restored(Purchase $purchase): void
    {
        // If soft deletes are implemented, handle restoration logic here
    }

    /**
     * Handle the Purchase "force deleted" event.
     */
    public function forceDeleted(Purchase $purchase): void
    {
        // Cascade delete will handle inventory_movements
    }
}
