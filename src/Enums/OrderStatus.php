<?php

namespace Shopper\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case CONFIRMED = 'confirmed';
    case PROCESSING = 'processing';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match($this) {
            self::PENDING => __('shopper::order.status.pending'),
            self::CONFIRMED => __('shopper::order.status.confirmed'),
            self::PROCESSING => __('shopper::order.status.processing'),
            self::SHIPPED => __('shopper::order.status.shipped'),
            self::DELIVERED => __('shopper::order.status.delivered'),
            self::CANCELLED => __('shopper::order.status.cancelled'),
            self::REFUNDED => __('shopper::order.status.refunded'),
        };
    }

    public function color(): string
    {
        return match($this) {
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
