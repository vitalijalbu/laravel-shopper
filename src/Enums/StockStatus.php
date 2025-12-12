<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum StockStatus: string
{
    use EnumSerializable;

    case InStock = 'in_stock';
    case OutOfStock = 'out_of_stock';
    case OnBackorder = 'on_backorder';
    case LowStock = 'low_stock';
    case Discontinued = 'discontinued';

    public function label(): string
    {
        return match ($this) {
            self::InStock => 'In Stock',
            self::OutOfStock => 'Out of Stock',
            self::OnBackorder => 'On Backorder',
            self::LowStock => 'Low Stock',
            self::Discontinued => 'Discontinued',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::InStock => 'green',
            self::OutOfStock => 'red',
            self::OnBackorder => 'yellow',
            self::LowStock => 'orange',
            self::Discontinued => 'gray',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this, [self::InStock, self::OnBackorder, self::LowStock]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
