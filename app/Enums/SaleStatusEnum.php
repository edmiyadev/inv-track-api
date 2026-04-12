<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum SaleStatusEnum: string
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
            self::Posted => false, // Final state: immutable once posted
            self::Canceled => false, // Terminal state
        };
    }

    /**
     * Get all valid transition targets from current status
     */
    public function validTransitions(): array
    {
        return match ($this) {
            self::Draft => [self::Posted, self::Canceled],
            self::Posted => [],
            self::Canceled => [],
        };
    }

    /**
     * Get formatted error message for invalid transition
     */
    public function getTransitionErrorMessage(self $targetStatus): string
    {
        return "Cannot transition sale from '{$this->value}' to '{$targetStatus->value}'.";
    }
}
