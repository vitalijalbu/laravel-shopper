<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum DiscountType: string
{
    use EnumSerializable;

    case PERCENTAGE = 'percentage';
    case FIXED_AMOUNT = 'fixed_amount';
    case FREE_SHIPPING = 'free_shipping';
    case BUY_X_GET_Y = 'buy_x_get_y';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => __('cartino::discount.type.percentage'),
            self::FIXED_AMOUNT => __('cartino::discount.type.fixed_amount'),
            self::FREE_SHIPPING => __('cartino::discount.type.free_shipping'),
            self::BUY_X_GET_Y => __('cartino::discount.type.buy_x_get_y'),
        };
    }

    public function requiresValue(): bool
    {
        return in_array($this, [self::PERCENTAGE, self::FIXED_AMOUNT]);
    }

    public function requiresQuantityRules(): bool
    {
        return $this === self::BUY_X_GET_Y;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
