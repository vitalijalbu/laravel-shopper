<?php

declare(strict_types=1);

namespace Cartino\Support;

/**
 * Class EnumExtension
 *
 * Represents an extended enum case added by plugins/addons.
 * Mimics the structure of native PHP enum cases while adding extensibility.
 *
 * @example
 * new EnumExtension(
 *     value: 'on_hold',
 *     label: 'On Hold',
 *     metadata: ['color' => 'orange', 'icon' => 'pause']
 * );
 */
class EnumExtension
{
    /**
     * Create a new enum extension.
     *
     * @param string $value The enum value (backing value)
     * @param string|null $label Default label (can be overridden by translations)
     * @param string|null $name The enum case name (defaults to uppercase value)
     * @param array<string, mixed> $metadata Additional metadata (colors, icons, etc.)
     */
    public function __construct(
        public readonly string $value,
        public readonly ?string $label = null,
        public readonly ?string $name = null,
        public readonly array $metadata = []
    ) {
    }

    /**
     * Get the label for this enum extension.
     *
     * @param string|null $locale Optional locale for translation
     * @return string
     */
    public function label(?string $locale = null): string
    {
        // Check if there's a translation registered
        // This will be handled by EnumRegistry

        return $this->label ?? $this->value;
    }

    /**
     * Get the color for this enum extension.
     *
     * @return string|null
     */
    public function color(): ?string
    {
        return $this->metadata['color'] ?? null;
    }

    /**
     * Get metadata value by key.
     *
     * @param string $key
     * @param mixed $default
     * @return mixed
     */
    public function getMeta(string $key, mixed $default = null): mixed
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Convert to array representation.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'value' => $this->value,
            'label' => $this->label,
            'name' => $this->name ?? strtoupper($this->value),
            'color' => $this->color(),
            'metadata' => $this->metadata,
            'extended' => true,
        ];
    }
}
