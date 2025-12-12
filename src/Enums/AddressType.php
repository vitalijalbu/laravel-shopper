<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum AddressType: string
{
    use EnumSerializable;

    case BILLING = 'billing';
    case SHIPPING = 'shipping';

    public function label(): string
    {
        return match ($this) {
            self::BILLING => __('cartino::address.type.billing'),
            self::SHIPPING => __('cartino::address.type.shipping'),
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
