<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum ReturnStatus: string
{
    use EnumSerializable;

    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => __('cartino::return.status.requested'),
            self::APPROVED => __('cartino::return.status.approved'),
            self::REJECTED => __('cartino::return.status.rejected'),
            self::IN_TRANSIT => __('cartino::return.status.in_transit'),
            self::RECEIVED => __('cartino::return.status.received'),
            self::PROCESSED => __('cartino::return.status.processed'),
            self::REFUNDED => __('cartino::return.status.refunded'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::REQUESTED => 'gray',
            self::APPROVED => 'primary',
            self::REJECTED => 'danger',
            self::IN_TRANSIT => 'info',
            self::RECEIVED => 'warning',
            self::PROCESSED => 'success',
            self::REFUNDED => 'success',
        };
    }

    public function canBeApproved(): bool
    {
        return $this === self::REQUESTED;
    }

    public function canBeRejected(): bool
    {
        return $this === self::REQUESTED;
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::REFUNDED, self::REJECTED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
