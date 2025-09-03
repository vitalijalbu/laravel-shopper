<?php

namespace Shopper\Enums;

enum AddressType: string
{
    case BILLING = 'billing';
    case SHIPPING = 'shipping';

    public function label(): string
    {
        return match($this) {
            self::BILLING => __('shopper::address.type.billing'),
            self::SHIPPING => __('shopper::address.type.shipping'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
