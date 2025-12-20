<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum StockMovementType: string
{
    use EnumSerializable;

    case PURCHASE = 'purchase';
    case SALE = 'sale';
    case RETURN = 'return';
    case TRANSFER = 'transfer';
    case ADJUSTMENT = 'adjustment';
    case DAMAGE = 'damage';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => __('cartino::stock.movement.purchase'),
            self::SALE => __('cartino::stock.movement.sale'),
            self::RETURN => __('cartino::stock.movement.return'),
            self::TRANSFER => __('cartino::stock.movement.transfer'),
            self::ADJUSTMENT => __('cartino::stock.movement.adjustment'),
            self::DAMAGE => __('cartino::stock.movement.damage'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PURCHASE, self::RETURN => 'success',
            self::SALE, self::TRANSFER => 'primary',
            self::ADJUSTMENT => 'info',
            self::DAMAGE => 'danger',
        };
    }

    public function affectsQuantity(): int
    {
        return match ($this) {
            self::PURCHASE, self::RETURN => 1, // aumenta
            self::SALE, self::DAMAGE => -1, // diminuisce
            self::TRANSFER, self::ADJUSTMENT => 0, // varia
        };
    }

    public function requiresReference(): bool
    {
        return in_array($this, [self::PURCHASE, self::SALE, self::RETURN]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
