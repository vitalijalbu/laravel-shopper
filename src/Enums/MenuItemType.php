<?php

namespace Shopper\Enums;

enum MenuItemType: string
{
    case LINK = 'link';
    case PAGE = 'page';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::LINK => __('shopper::menu.item_type.link'),
            self::PAGE => __('shopper::menu.item_type.page'),
            self::CATEGORY => __('shopper::menu.item_type.category'),
            self::PRODUCT => __('shopper::menu.item_type.product'),
            self::CUSTOM => __('shopper::menu.item_type.custom'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
