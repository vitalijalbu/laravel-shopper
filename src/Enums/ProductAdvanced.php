<?php

declare(strict_types=1);

namespace Shopper\Enums;

enum WeightUnit: string
{
    case Kilogram = 'kg';
    case Gram = 'g';
    case Pound = 'lb';
    case Ounce = 'oz';

    public function label(): string
    {
        return match ($this) {
            self::Kilogram => 'Kilograms',
            self::Gram => 'Grams',
            self::Pound => 'Pounds',
            self::Ounce => 'Ounces',
        };
    }

    public function abbreviation(): string
    {
        return $this->value;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum InventoryPolicy: string
{
    case Deny = 'deny';
    case Continue = 'continue';

    public function label(): string
    {
        return match ($this) {
            self::Deny => 'Stop selling when out of stock',
            self::Continue => 'Continue selling when out of stock',
        };
    }

    public function allowsOutOfStockSales(): bool
    {
        return $this === self::Continue;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum InventoryManagement: string
{
    case Shopify = 'shopify';
    case NotManaged = 'not_managed';
    case FulfillmentService = 'fulfillment_service';

    public function label(): string
    {
        return match ($this) {
            self::Shopify => 'Track quantity',
            self::NotManaged => 'Don\'t track quantity',
            self::FulfillmentService => 'Fulfillment service',
        };
    }

    public function tracksInventory(): bool
    {
        return $this !== self::NotManaged;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum CollectionType: string
{
    case Manual = 'manual';
    case Smart = 'smart';
    case Custom = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::Manual => 'Manual',
            self::Smart => 'Automated',
            self::Custom => 'Custom',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Manual => 'Add products manually',
            self::Smart => 'Products added automatically based on conditions',
            self::Custom => 'Custom collection logic',
        };
    }

    public function isAutomated(): bool
    {
        return $this === self::Smart;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}

enum PublishedScope: string
{
    case Web = 'web';
    case Global = 'global';

    public function label(): string
    {
        return match ($this) {
            self::Web => 'Online Store',
            self::Global => 'Online Store and other sales channels',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
