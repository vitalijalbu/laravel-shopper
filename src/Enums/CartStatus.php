<?php

namespace Shopper\Enums;

enum CartStatus: string
{
    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match($this) {
            self::ACTIVE => __('shopper::cart.status.active'),
            self::ABANDONED => __('shopper::cart.status.abandoned'),
            self::CONVERTED => __('shopper::cart.status.converted'),
            self::EXPIRED => __('shopper::cart.status.expired'),
        };
    }

    public function color(): string
    {
        return match($this) {
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
