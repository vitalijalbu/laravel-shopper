<?php

declare(strict_types=1);

namespace Cartino\Enums;

enum Gender: string
{
    case Male = 'male';
    case Female = 'female';
    case Other = 'other';
    case PreferNotToSay = 'prefer_not_to_say';

    public function label(): string
    {
        return match ($this) {
            self::Male => 'Male',
            self::Female => 'Female',
            self::Other => 'Other',
            self::PreferNotToSay => 'Prefer not to say',
        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
