<?php

declare(strict_types=1);

namespace Shopper\Enums;

enum ProductType: string
{
    case Physical = 'physical';
    case Digital = 'digital';
    case Service = 'service';
    case Subscription = 'subscription';
    case Bundle = 'bundle';

    public function label(): string
    {
        return match ($this) {
            self::Physical => 'Physical Product',
            self::Digital => 'Digital Product',
            self::Service => 'Service',
            self::Subscription => 'Subscription',
            self::Bundle => 'Product Bundle',
        };
    }

    public function requiresShipping(): bool
    {
        return $this === self::Physical;
    }

    public function hasInventory(): bool
    {
        return in_array($this, [self::Physical, self::Bundle]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
