<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum DiscountTargetType: string
{
    use EnumSerializable;

    case ALL = 'all';
    case SPECIFIC_PRODUCTS = 'specific_products';
    case SPECIFIC_COLLECTIONS = 'specific_collections';
    case CATEGORIES = 'categories';

    public function label(): string
    {
        return match ($this) {
            self::ALL => __('cartino::discount.target.all'),
            self::SPECIFIC_PRODUCTS => __('cartino::discount.target.specific_products'),
            self::SPECIFIC_COLLECTIONS => __('cartino::discount.target.specific_collections'),
            self::CATEGORIES => __('cartino::discount.target.categories'),
        };
    }

    public function requiresSelection(): bool
    {
        return $this !== self::ALL;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
