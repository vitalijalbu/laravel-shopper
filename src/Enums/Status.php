<?php

declare(strict_types=1);

namespace Cartino\Enums;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
    case Draft = 'draft';
    case Archived = 'archived';
    case Pending = 'pending';
    case Suspended = 'suspended';
    case Maintenance = 'maintenance';
    case Deprecated = 'deprecated';
    case Discontinued = 'discontinued';
    case Hidden = 'hidden';
    case Restricted = 'restricted';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Inactive => 'Inactive',
            self::Draft => 'Draft',
            self::Archived => 'Archived',
            self::Pending => 'Pending',
            self::Suspended => 'Suspended',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::Inactive => 'gray',
            self::Draft => 'yellow',
            self::Archived => 'red',
            self::Pending => 'blue',
            self::Suspended => 'orange',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function isVisible(): bool
    {
        return in_array($this, [self::Active, self::Draft]);
    }

    public static function default(): self
    {
        return self::Active;
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
