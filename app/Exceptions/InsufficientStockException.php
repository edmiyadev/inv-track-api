<?php

namespace App\Exceptions;

use RuntimeException;

class InsufficientStockException extends RuntimeException
{
    /**
     * @var array<int, array{
     *   item_index:int|null,
     *   product_id:int,
     *   product_name:string|null,
     *   warehouse_id:int,
     *   available:int,
     *   requested:int,
     *   missing:int
     * }>
     */
    private readonly array $items;

    public function __construct(
        private readonly int $productId,
        private readonly ?string $productName,
        private readonly int $warehouseId,
        private readonly int $available,
        private readonly int $requested,
        private readonly ?int $itemIndex = null,
        array $items = [],
    ) {
        $this->items = empty($items) ? [[
            'item_index' => $this->itemIndex,
            'product_id' => $this->productId,
            'product_name' => $this->productName,
            'warehouse_id' => $this->warehouseId,
            'available' => $this->available,
            'requested' => $this->requested,
            'missing' => max(0, $this->requested - $this->available),
        ]] : $items;

        parent::__construct("Insufficient stock. Available: {$available}, Requested: {$requested}");
    }

    public static function fromData(
        int $productId,
        ?string $productName,
        int $warehouseId,
        int $available,
        int $requested,
        ?int $itemIndex = null,
    ): self {
        return new self(
            productId: $productId,
            productName: $productName,
            warehouseId: $warehouseId,
            available: $available,
            requested: $requested,
            itemIndex: $itemIndex,
        );
    }

    /**
     * @param  array<int, array{
     *   item_index:int|null,
     *   product_id:int,
     *   product_name:string|null,
     *   warehouse_id:int,
     *   available:int,
     *   requested:int,
     *   missing:int
     * }>  $items
     */
    public static function fromItems(array $items): self
    {
        if (empty($items)) {
            throw new \InvalidArgumentException('Insufficient stock items payload cannot be empty.');
        }

        $first = $items[0];

        return new self(
            productId: $first['product_id'],
            productName: $first['product_name'],
            warehouseId: $first['warehouse_id'],
            available: $first['available'],
            requested: $first['requested'],
            itemIndex: $first['item_index'],
            items: $items,
        );
    }

    /**
     * @return array<int, array{
     *   item_index:int|null,
     *   product_id:int,
     *   product_name:string|null,
     *   warehouse_id:int,
     *   available:int,
     *   requested:int,
     *   missing:int
     * }>
     */
    public function items(): array
    {
        return $this->items;
    }
}
