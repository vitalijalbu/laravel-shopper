<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum StockReservationStatus: string
{
    use EnumSerializable;

    case RESERVED = 'reserved';
    case FULFILLED = 'fulfilled';
    case CANCELLED = 'cancelled';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::RESERVED => __('cartino::stock.reservation.reserved'),
            self::FULFILLED => __('cartino::stock.reservation.fulfilled'),
            self::CANCELLED => __('cartino::stock.reservation.cancelled'),
            self::EXPIRED => __('cartino::stock.reservation.expired'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::RESERVED => 'warning',
            self::FULFILLED => 'success',
            self::CANCELLED => 'gray',
            self::EXPIRED => 'danger',
        };
    }

    public function isActive(): bool
    {
        return $this === self::RESERVED;
    }

    public function isFinalized(): bool
    {
        return in_array($this, [self::FULFILLED, self::CANCELLED, self::EXPIRED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
