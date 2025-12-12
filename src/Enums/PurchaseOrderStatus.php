<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum PurchaseOrderStatus: string
{
    use EnumSerializable;

    case DRAFT = 'draft';
    case SENT = 'sent';
    case CONFIRMED = 'confirmed';
    case PARTIAL = 'partial';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => __('cartino::purchase_order.status.draft'),
            self::SENT => __('cartino::purchase_order.status.sent'),
            self::CONFIRMED => __('cartino::purchase_order.status.confirmed'),
            self::PARTIAL => __('cartino::purchase_order.status.partial'),
            self::COMPLETED => __('cartino::purchase_order.status.completed'),
            self::CANCELLED => __('cartino::purchase_order.status.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::SENT => 'info',
            self::CONFIRMED => 'primary',
            self::PARTIAL => 'warning',
            self::COMPLETED => 'success',
            self::CANCELLED => 'danger',
        };
    }

    public function canBeEdited(): bool
    {
        return $this === self::DRAFT;
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::COMPLETED, self::CANCELLED]);
    }

    public function canReceiveItems(): bool
    {
        return in_array($this, [self::CONFIRMED, self::PARTIAL]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
