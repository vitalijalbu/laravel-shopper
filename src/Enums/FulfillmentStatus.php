<?php

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum FulfillmentStatus: string
{
    use EnumSerializable;

    case UNFULFILLED = 'unfulfilled';
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::UNFULFILLED => 'Unfulfilled',
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::DELIVERED, self::FAILED, self::CANCELLED]);
    }
}
