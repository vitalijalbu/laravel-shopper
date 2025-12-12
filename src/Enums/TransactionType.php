<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum TransactionType: string
{
    use EnumSerializable;

    case PAYMENT = 'payment';
    case REFUND = 'refund';
    case CAPTURE = 'capture';
    case VOID = 'void';

    public function label(): string
    {
        return match ($this) {
            self::PAYMENT => __('cartino::transaction.type.payment'),
            self::REFUND => __('cartino::transaction.type.refund'),
            self::CAPTURE => __('cartino::transaction.type.capture'),
            self::VOID => __('cartino::transaction.type.void'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PAYMENT => 'success',
            self::REFUND => 'warning',
            self::CAPTURE => 'info',
            self::VOID => 'gray',
        };
    }

    public function affectsBalance(): bool
    {
        return in_array($this, [self::PAYMENT, self::CAPTURE]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
