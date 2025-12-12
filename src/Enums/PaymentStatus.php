<?php

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum PaymentStatus: string
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
            self::PENDING => __('cartino::payment.status.pending'),
            self::CONFIRMED => __('cartino::payment.status.confirmed'),
            self::PROCESSING => __('cartino::payment.status.processing'),
            self::SHIPPED => __('cartino::payment.status.shipped'),
            self::DELIVERED => __('cartino::payment.status.delivered'),
            self::CANCELLED => __('cartino::payment.status.cancelled'),
            self::REFUNDED => __('cartino::payment.status.refunded'),
        };
    }
}
