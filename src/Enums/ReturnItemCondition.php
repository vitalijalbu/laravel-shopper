<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum ReturnItemCondition: string
{
    use EnumSerializable;

    case NEW = 'new';
    case OPENED = 'opened';
    case DAMAGED = 'damaged';
    case DEFECTIVE = 'defective';

    public function label(): string
    {
        return match ($this) {
            self::NEW => __('cartino::return.condition.new'),
            self::OPENED => __('cartino::return.condition.opened'),
            self::DAMAGED => __('cartino::return.condition.damaged'),
            self::DEFECTIVE => __('cartino::return.condition.defective'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::NEW => 'success',
            self::OPENED => 'warning',
            self::DAMAGED => 'danger',
            self::DEFECTIVE => 'danger',
        };
    }

    public function isResellable(): bool
    {
        return in_array($this, [self::NEW, self::OPENED]);
    }

    public function restockPercentage(): int
    {
        return match ($this) {
            self::NEW => 100,
            self::OPENED => 80,
            self::DAMAGED => 0,
            self::DEFECTIVE => 0,
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
