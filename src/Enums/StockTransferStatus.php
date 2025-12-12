<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum StockTransferStatus: string
{
    use EnumSerializable;

    case PENDING = 'pending';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('cartino::stock.transfer.pending'),
            self::IN_TRANSIT => __('cartino::stock.transfer.in_transit'),
            self::RECEIVED => __('cartino::stock.transfer.received'),
            self::CANCELLED => __('cartino::stock.transfer.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'gray',
            self::IN_TRANSIT => 'info',
            self::RECEIVED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function canBeShipped(): bool
    {
        return $this === self::PENDING;
    }

    public function canBeReceived(): bool
    {
        return $this === self::IN_TRANSIT;
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::RECEIVED, self::CANCELLED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
