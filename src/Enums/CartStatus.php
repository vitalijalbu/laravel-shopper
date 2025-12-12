<?php

namespace Cartino\Enums;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('cartino::cart.status.active'),
            self::ABANDONED => __('cartino::cart.status.abandoned'),
            self::CONVERTED => __('cartino::cart.status.converted'),
            self::EXPIRED => __('cartino::cart.status.expired'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'green',
            self::ABANDONED => 'orange',
            self::CONVERTED => 'blue',
            self::EXPIRED => 'red',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
