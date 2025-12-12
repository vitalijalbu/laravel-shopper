<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum ProductRelationType: string
{
    use EnumSerializable;
    case UPSELL = 'upsell';
    case CROSS_SELL = 'cross_sell';
    case RELATED = 'related';
    case ALTERNATIVE = 'alternative';
    case ACCESSORY = 'accessory';

    public function label(): string
    {
        return match ($this) {
            self::UPSELL => 'Upsell',
            self::CROSS_SELL => 'Cross-sell',
            self::RELATED => 'Related',
            self::ALTERNATIVE => 'Alternative',
            self::ACCESSORY => 'Accessory',
        };
    }
}