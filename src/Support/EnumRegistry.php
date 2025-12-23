<?php

declare(strict_types=1);

namespace Cartino\Support;

use BackedEnum;
use Illuminate\Support\Collection;
use UnitEnum;

/**
 * Class EnumRegistry
 *
 * Manages extensible enums by allowing plugins/addons to register additional cases.
 * Provides a central registry for enum extensions while maintaining type safety.
 *
 * @example
 * // In a plugin ServiceProvider:
 * EnumRegistry::extend(OrderStatus::class, [
 *     new EnumExtension('on_hold', 'On Hold', ['color' => 'orange']),
 * ]);
 *
 * // Get all cases (original + extended):
 * $allStatuses = EnumRegistry::cases(OrderStatus::class);
 */
class EnumRegistry
{
    /**
     * Registry of extended enum cases.
     *
     * @var array<class-string, array<EnumExtension>>
     */
    protected static array $extensions = [];

    /**
     * Registry of enum translations from plugins.
     *
     * @var array<class-string, array<string, array<string, string>>>
     */
    protected static array $translations = [];

    /**
     * Register additional enum cases for a given enum class.
     *
     * @param class-string $enumClass The enum class to extend
     * @param array<EnumExtension> $cases Array of EnumExtension instances
     * @return void
     */
    public static function extend(string $enumClass, array $cases): void
    {
        if (! isset(self::$extensions[$enumClass])) {
            self::$extensions[$enumClass] = [];
        }

        foreach ($cases as $case) {
            if (! $case instanceof EnumExtension) {
                throw new \InvalidArgumentException(
                    'All cases must be instances of EnumExtension'
                );
            }

            self::$extensions[$enumClass][] = $case;
        }
    }

    /**
     * Register translations for extended enum cases.
     *
     * @param class-string $enumClass The enum class
     * @param string $locale The locale code
     * @param array<string, string> $translations Array of [value => translation]
     * @return void
     */
    public static function addTranslations(string $enumClass, string $locale, array $translations): void
    {
        if (! isset(self::$translations[$enumClass])) {
            self::$translations[$enumClass] = [];
        }

        if (! isset(self::$translations[$enumClass][$locale])) {
            self::$translations[$enumClass][$locale] = [];
        }

        self::$translations[$enumClass][$locale] = array_merge(
            self::$translations[$enumClass][$locale],
            $translations
        );
    }

    /**
     * Get all enum cases (original + extended).
     *
     * @param class-string $enumClass The enum class
     * @return array<UnitEnum|EnumExtension> All enum cases
     */
    public static function cases(string $enumClass): array
    {
        $originalCases = $enumClass::cases();
        $extendedCases = self::$extensions[$enumClass] ?? [];

        return array_merge($originalCases, $extendedCases);
    }

    /**
     * Get translated options for select inputs (original + extended).
     *
     * @param class-string $enumClass The enum class
     * @param string|null $locale Optional locale
     * @return array<string, string> Array of [value => label]
     */
    public static function translatedOptions(string $enumClass, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $options = [];

        // Get original enum options
        if (method_exists($enumClass, 'translatedOptions')) {
            $options = $enumClass::translatedOptions($locale);
        }

        // Add extended cases
        $extensions = self::$extensions[$enumClass] ?? [];
        foreach ($extensions as $extension) {
            $label = self::$translations[$enumClass][$locale][$extension->value]
                ?? $extension->label
                ?? $extension->value;

            $options[$extension->value] = $label;
        }

        return $options;
    }

    /**
     * Get full select options with metadata (original + extended).
     *
     * @param class-string $enumClass The enum class
     * @param string|null $locale Optional locale
     * @return array<int, array> Array of option details
     */
    public static function toSelectOptions(string $enumClass, ?string $locale = null): array
    {
        $locale = $locale ?? app()->getLocale();
        $options = [];

        // Get original enum options
        if (method_exists($enumClass, 'toSelectOptions')) {
            $options = $enumClass::toSelectOptions($locale);
        }

        // Add extended cases
        $extensions = self::$extensions[$enumClass] ?? [];
        foreach ($extensions as $extension) {
            $label = self::$translations[$enumClass][$locale][$extension->value]
                ?? $extension->label
                ?? $extension->value;

            $options[] = [
                'value' => $extension->value,
                'label' => $label,
                'name' => $extension->name ?? strtoupper($extension->value),
                'color' => $extension->metadata['color'] ?? null,
                'extended' => true, // Mark as extended case
            ];
        }

        return $options;
    }

    /**
     * Get all translations for all locales (original + extended).
     *
     * @param class-string $enumClass The enum class
     * @param array|null $locales List of locales
     * @return array<string, array> Array of [locale => [value => label]]
     */
    public static function allTranslations(string $enumClass, ?array $locales = null): array
    {
        $locales = $locales ?? config('cartino.locales', ['en', 'it']);
        $translations = [];

        foreach ($locales as $locale) {
            $translations[$locale] = self::translatedOptions($enumClass, $locale);
        }

        return $translations;
    }

    /**
     * Check if an enum has extensions.
     *
     * @param class-string $enumClass The enum class
     * @return bool
     */
    public static function hasExtensions(string $enumClass): bool
    {
        return ! empty(self::$extensions[$enumClass]);
    }

    /**
     * Get only the extended cases for an enum.
     *
     * @param class-string $enumClass The enum class
     * @return array<EnumExtension>
     */
    public static function getExtensions(string $enumClass): array
    {
        return self::$extensions[$enumClass] ?? [];
    }

    /**
     * Clear all extensions (useful for testing).
     *
     * @return void
     */
    public static function clearExtensions(): void
    {
        self::$extensions = [];
        self::$translations = [];
    }

    /**
     * Find an enum case by value (searches both original and extended).
     *
     * @param class-string $enumClass The enum class
     * @param string $value The value to search for
     * @return UnitEnum|EnumExtension|null
     */
    public static function findByValue(string $enumClass, string $value): UnitEnum|EnumExtension|null
    {
        // Search original cases
        if (method_exists($enumClass, 'tryFrom')) {
            $case = $enumClass::tryFrom($value);
            if ($case !== null) {
                return $case;
            }
        }

        // Search extended cases
        $extensions = self::$extensions[$enumClass] ?? [];
        foreach ($extensions as $extension) {
            if ($extension->value === $value) {
                return $extension;
            }
        }

        return null;
    }
}
