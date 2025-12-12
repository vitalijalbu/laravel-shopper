<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum ShippingCalculationMethod: string
{
    use EnumSerializable;

    case FLAT_RATE = 'flat_rate';
    case PER_ITEM = 'per_item';
    case WEIGHT_BASED = 'weight_based';
    case PRICE_BASED = 'price_based';
    case CARRIER_CALCULATED = 'carrier_calculated';

    public function label(): string
    {
        return match ($this) {
            self::FLAT_RATE => __('cartino::shipping.calculation.flat_rate'),
            self::PER_ITEM => __('cartino::shipping.calculation.per_item'),
            self::WEIGHT_BASED => __('cartino::shipping.calculation.weight_based'),
            self::PRICE_BASED => __('cartino::shipping.calculation.price_based'),
            self::CARRIER_CALCULATED => __('cartino::shipping.calculation.carrier_calculated'),
        };
    }

    public function requiresWeight(): bool
    {
        return $this === self::WEIGHT_BASED;
    }

    public function requiresPrice(): bool
    {
        return $this === self::PRICE_BASED;
    }

    public function requiresExternalAPI(): bool
    {
        return $this === self::CARRIER_CALCULATED;
    }

    public function requiresItemCount(): bool
    {
        return $this === self::PER_ITEM;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
