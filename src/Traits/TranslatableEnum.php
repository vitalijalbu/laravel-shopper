<?php

declare(strict_types=1);

namespace Cartino\Traits;

use Illuminate\Support\Str;

/**
 * Trait TranslatableEnum
 *
 * Provides translation capabilities to PHP enums.
 * Automatically generates translation keys based on enum class name and case name.
 *
 * @example
 * enum OrderStatus: string {
 *     use TranslatableEnum;
 *     case PENDING = 'pending';
 * }
 * OrderStatus::PENDING->label() // Returns translation from 'cartino::enums.OrderStatus.pending'
 */
trait TranslatableEnum
{
    /**
     * Get the translated label for this enum case.
     *
     * @param string|null $locale Optional locale override
     * @return string Translated label or fallback
     */
    public function label(?string $locale = null): string
    {
        $key = $this->translationKey();
        $translation = __($key, [], $locale ?? app()->getLocale());

        // Fallback to humanized name if translation not found
        if ($translation === $key) {
            return $this->humanizedName();
        }

        return $translation;
    }

    /**
     * Get the translation key for this enum case.
     *
     * @return string Translation key in format: cartino::enums.{ClassName}.{case_name}
     */
    protected function translationKey(): string
    {
        $class = class_basename($this);
        $name = Str::lower(Str::snake($this->name));

        return "cartino::enums.{$class}.{$name}";
    }

    /**
     * Get a human-readable fallback name.
     *
     * @return string Humanized case name
     */
    protected function humanizedName(): string
    {
        return Str::headline($this->name);
    }

    /**
     * Get all enum cases with their translations.
     *
     * @param string|null $locale Optional locale override
     * @return array<string, string> Array of [value => label]
     */
    public static function translatedOptions(?string $locale = null): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn ($case) => [
                $case->value ?? $case->name => $case->label($locale),
            ])
            ->toArray();
    }

    /**
     * Get all enum cases with full details.
     *
     * @param string|null $locale Optional locale override
     * @return array<int, array> Array of enum case details
     */
    public static function toSelectOptions(?string $locale = null): array
    {
        return collect(self::cases())
            ->map(fn ($case) => [
                'value' => $case->value ?? $case->name,
                'label' => $case->label($locale),
                'name' => $case->name,
                'color' => method_exists($case, 'color') ? $case->color() : null,
            ])
            ->toArray();
    }

    /**
     * Get translations for all locales.
     *
     * @param array|null $locales List of locales, or null for all configured locales
     * @return array<string, array> Array of [locale => [value => label]]
     */
    public static function allTranslations(?array $locales = null): array
    {
        $locales = $locales ?? config('cartino.locales', ['en', 'it']);

        return collect($locales)
            ->mapWithKeys(fn ($locale) => [
                $locale => static::translatedOptions($locale),
            ])
            ->toArray();
    }

    /**
     * Find enum case by translated label.
     *
     * @param string $label The translated label to search for
     * @param string|null $locale Optional locale
     * @return static|null The matching enum case or null
     */
    public static function fromLabel(string $label, ?string $locale = null): ?static
    {
        foreach (self::cases() as $case) {
            if ($case->label($locale) === $label) {
                return $case;
            }
        }

        return null;
    }
}
