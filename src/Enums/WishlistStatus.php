<?php

namespace Shopper\Enums;

enum WishlistStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('shopper::wishlist.status.active'),
            self::INACTIVE => __('shopper::wishlist.status.inactive'),
            self::ARCHIVED => __('shopper::wishlist.status.archived'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::INACTIVE => 'gray',
            self::ARCHIVED => 'orange',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
