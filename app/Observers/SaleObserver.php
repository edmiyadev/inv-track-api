<?php

namespace App\Observers;

use App\Enums\MovementTypeEnum;
use App\Enums\SaleStatusEnum;
use App\Models\Sale;
use App\Services\InventoryMovementService;
use Illuminate\Support\Facades\Log;

class SaleObserver
{
    public function __construct(
        private readonly InventoryMovementService $inventoryMovementService
    ) {}

    public function updating(Sale $sale): void
    {
        $oldValue = $sale->getOriginal('status');
        $oldStatus = $oldValue instanceof SaleStatusEnum 
            ? $oldValue 
            : SaleStatusEnum::from($oldValue);

        // Inmutabilidad para ventas completadas
        if ($oldStatus === SaleStatusEnum::Completed) {
            throw new \DomainException(
                "Sale #{$sale->id} is 'completed' and immutable."
            );
        }

        // Validar transiciones
        if ($sale->isDirty('status')) {
            $newStatus = $sale->status;
            if (! $oldStatus->canTransitionTo($newStatus)) {
                throw new \DomainException($oldStatus->getTransitionErrorMessage($newStatus));
            }
        }
    }

    public function updated(Sale $sale): void
    {
        if (! $sale->wasChanged('status')) {
            return;
        }

        $newStatus = $sale->status;

        // Si se completa la venta, crear el movimiento de inventario (SALIDA)
        if ($newStatus === SaleStatusEnum::Completed) {
            $this->handleCompletion($sale);
        }
    }

    private function handleCompletion(Sale $sale): void
    {
        $sale->loadMissing(['items', 'items.product']);

        if ($sale->items->isEmpty()) {
            throw new \DomainException("Cannot complete sale #{$sale->id}: no items found.");
        }

        if (! $sale->warehouse_id) {
            throw new \DomainException("Cannot complete sale #{$sale->id}: warehouse_id is required.");
        }

        // Crear el movimiento de salida (Polimórfico)
        $this->inventoryMovementService->createMovement([
            'document_id' => $sale->id,
            'document_type' => Sale::class,
            'movement_type' => MovementTypeEnum::Out,
            'origin_warehouse_id' => $sale->warehouse_id,
            'notes' => "Sale #{$sale->id} completed",
            'items' => $sale->items->map(fn ($item) => [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_price' => $item->unit_price,
            ])->toArray(),
        ]);

        Log::info("Created inventory 'out' movement for sale #{$sale->id}");
    }

    public function deleting(Sale $sale): void
    {
        if ($sale->status === SaleStatusEnum::Completed) {
            throw new \DomainException("Cannot delete a 'completed' sale.");
        }
    }
}
