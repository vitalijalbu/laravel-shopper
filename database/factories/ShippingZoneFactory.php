<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\ShippingZone;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ShippingZoneFactory extends Factory
{
    protected $model = ShippingZone::class;

    /**
     * Generate default attribute values for a ShippingZone model instance.
     *
     * The returned array contains attributes used when creating a ShippingZone:
     * - `site_id` (int|null): id of a randomly selected Site, or null if none exist.
     * - `name` (string): human-readable zone name (e.g., "EU Zone 3").
     * - `description` (string): a short descriptive sentence.
     * - `countries` (array): list of ISO country codes included in the zone.
     * - `regions` (null|array): region restrictions, null by default.
     * - `postal_codes` (null|array): postal code restrictions, null by default.
     * - `priority` (int): priority value used for sorting or selection.
     * - `is_active` (bool): whether the zone is active.
     * - `data` (mixed): extensible payload, null by default.
     *
     * @return array<string,mixed> Attributes for a ShippingZone model.
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'name' => 'EU Zone '.$this->faker->unique()->numberBetween(1, 9),
            'description' => $this->faker->sentence(8),
            'countries' => ['IT', 'FR', 'DE', 'ES'],
            'regions' => null,
            'postal_codes' => null,
            'priority' => $this->faker->numberBetween(0, 10),
            'is_active' => true,
            'data' => null,
        ];
    }
}