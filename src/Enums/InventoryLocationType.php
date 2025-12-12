<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum InventoryLocationType: string
{
    use EnumSerializable;

    case WAREHOUSE = 'warehouse';
    case STORE = 'store';
    case DROPSHIP = 'dropship';
    case VENDOR = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'Warehouse',
            self::STORE => 'Store',
            self::DROPSHIP => 'Dropship',
            self::VENDOR => 'Vendor',
        };
    }
}
