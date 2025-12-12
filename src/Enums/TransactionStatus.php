<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum TransactionStatus: string
{
    use EnumSerializable;

    case PENDING = 'pending';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('cartino::transaction.status.pending'),
            self::COMPLETED => __('cartino::transaction.status.completed'),
            self::FAILED => __('cartino::transaction.status.failed'),
            self::CANCELLED => __('cartino::transaction.status.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'warning',
            self::COMPLETED => 'success',
            self::FAILED => 'danger',
            self::CANCELLED => 'gray',
        };
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }

    public function canBeRefunded(): bool
    {
        return $this === self::COMPLETED;
    }

    public function canBeCancelled(): bool
    {
        return $this === self::PENDING;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
