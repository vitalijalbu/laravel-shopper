<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum SupplierStatus: string
{
    use EnumSerializable;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('cartino::supplier.status.active'),
            self::INACTIVE => __('cartino::supplier.status.inactive'),
            self::SUSPENDED => __('cartino::supplier.status.suspended'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'warning',
        };
    }

    public function canPlaceOrders(): bool
    {
        return $this === self::ACTIVE;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
