<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum ShippingMethodType: string
{
    use EnumSerializable;

    case FLAT_RATE = 'flat_rate';
    case FREE = 'free';
    case CALCULATED = 'calculated';
    case PICKUP = 'pickup';

    public function label(): string
    {
        return match ($this) {
            self::FLAT_RATE => __('cartino::shipping.method.flat_rate'),
            self::FREE => __('cartino::shipping.method.free'),
            self::CALCULATED => __('cartino::shipping.method.calculated'),
            self::PICKUP => __('cartino::shipping.method.pickup'),
        };
    }

    public function requiresShipping(): bool
    {
        return $this !== self::PICKUP;
    }

    public function hasFixedCost(): bool
    {
        return in_array($this, [self::FLAT_RATE, self::FREE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
