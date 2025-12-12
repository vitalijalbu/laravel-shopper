<?php

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum MenuItemType: string
{
    use EnumSerializable;

    case LINK = 'link';
    case PAGE = 'page';
    case CATEGORY = 'category';
    case PRODUCT = 'product';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::LINK => __('cartino::menu.item_type.link'),
            self::PAGE => __('cartino::menu.item_type.page'),
            self::CATEGORY => __('cartino::menu.item_type.category'),
            self::PRODUCT => __('cartino::menu.item_type.product'),
            self::CUSTOM => __('cartino::menu.item_type.custom'),
        };
    }


}
