<?php

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum OrderStatus: string
{
    use EnumSerializable;
    
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => __('cartino::order.status.pending'),
            self::CONFIRMED => __('cartino::order.status.confirmed'),
            self::PROCESSING => __('cartino::order.status.processing'),
            self::SHIPPED => __('cartino::order.status.shipped'),
            self::DELIVERED => __('cartino::order.status.delivered'),
            self::CANCELLED => __('cartino::order.status.cancelled'),
            self::REFUNDED => __('cartino::order.status.refunded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'orange',
            self::CONFIRMED => 'blue',
            self::PROCESSING => 'purple',
            self::SHIPPED => 'indigo',
            self::DELIVERED => 'green',
            self::CANCELLED => 'red',
            self::REFUNDED => 'gray',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
