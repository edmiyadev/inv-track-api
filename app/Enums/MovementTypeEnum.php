<?php

namespace App\Enums;

use App\Traits\EnumTrait;

enum MovementTypeEnum: string
{
    use EnumTrait;

    case In = 'in';
    case Out = 'out';
    case Transfer = 'transfer';
    case Adjustment = 'adjustment';

    /**
     * Get the reverse movement type for cancellations
     *
     * @throws \LogicException If the movement type cannot be reversed
     */
    public function reverse(): self
    {
        return match ($this) {
            self::In => self::Out,
            self::Out => self::In,
            self::Transfer => throw new \LogicException('Cannot reverse transfer movements'),
            self::Adjustment => throw new \LogicException('Cannot reverse adjustment movements'),
        };
    }

    /**
     * Check if this movement type can be reversed
     */
    public function isReversible(): bool
    {
        return match ($this) {
            self::In, self::Out => true,
            self::Transfer, self::Adjustment => false,
        };
    }

    /**
     * Ensure value is an enum (convert string if needed)
     */
    public static function ensureEnum(string|self $value): self
    {
        return is_string($value) ? self::from($value) : $value;
    }
}
