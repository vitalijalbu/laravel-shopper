<?php

declare(strict_types=1);

namespace Cartino\Enums;

use Cartino\Helpers\EnumSerializable;

enum AppInstallationStatus: string
{
    use EnumSerializable;

    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => __('cartino::app.installation.active'),
            self::INACTIVE => __('cartino::app.installation.inactive'),
            self::SUSPENDED => __('cartino::app.installation.suspended'),
            self::CANCELLED => __('cartino::app.installation.cancelled'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ACTIVE => 'success',
            self::INACTIVE => 'gray',
            self::SUSPENDED => 'warning',
            self::CANCELLED => 'danger',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::ACTIVE;
    }

    public function canBeReactivated(): bool
    {
        return in_array($this, [self::INACTIVE, self::SUSPENDED]);
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
