<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum PurchaseStatusEnum: string
{
    use EnumTrait;

    case Draft = 'draft';
    case Posted = 'posted';
    case Canceled = 'canceled';

    /**
     * Check if transition from current status to target status is valid
     */
    public function canTransitionTo(self $targetStatus): bool
    {
        return match ($this) {
            self::Draft => in_array($targetStatus, [self::Posted, self::Canceled]),
            self::Posted => false, // Final state: cannot be modified or canceled
            self::Canceled => $targetStatus === self::Draft,
        };
    }

    /**
     * Get all valid transition targets from current status
     *
     * @return array<self>
     */
    public function validTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Posted, self::Canceled],
            self::Posted => [],
            self::Canceled => [self::Draft],
        };
    }

    /**
     * Check if this status triggers inventory movement
     */
    public function triggersInventoryMovement(): bool
    {
        return $this === self::Posted;
    }

    /**
     * Check if this is a terminal state (no further transitions allowed)
     */
    public function isTerminal(): bool
    {
        return $this === self::Posted;
    }

    /**
     * Get formatted error message for invalid transition
     */
    public function getTransitionErrorMessage(self $targetStatus): string
    {
        return "Cannot transition from '{$this->value}' to '{$targetStatus->value}'. ".
               'Valid transitions: '.implode(', ', array_map(
                   fn ($s) => $s->value,
                   $this->validTransitions()
               ));
    }
}
