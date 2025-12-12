<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Channel;
use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShippingRateFactory extends Factory
{
    protected $model = ShippingRate::class;

    /**
     * Provide the default attribute values for a ShippingRate model instance used by the factory.
     *
     * The returned array maps ShippingRate attributes to their default values or generators:
     * - `shipping_zone_id`: factory for an associated ShippingZone.
     * - `channel_id`: a randomly selected existing Channel id.
     * - `name`: "Standard Shipping".
     * - `code`: unique uppercase identifier like "SHIP-123".
     * - `description`: an 8-word sentence.
     * - `calculation_method`: "flat_rate".
     * - `price`: float between 5.00 and 20.00.
     * - `currency`: "EUR".
     * - `min_price`, `max_price`, `min_weight`, `max_weight`, `min_order_value`, `max_order_value`, `carrier`, `service_code`, `carrier_settings`, `data`: null by default.
     * - `weight_unit`: "kg".
     * - `min_delivery_days`: 2, `max_delivery_days`: 5.
     * - `is_active`: true.
     * - `priority`: 0.
     *
     * @return array<string,mixed> Associative array of attribute names and their default values/generators for the factory.
     */
    public function definition(): array
    {
        return [
            'shipping_zone_id' => fn () => ShippingZone::factory(),
            'channel_id' => Channel::query()->inRandomOrder()->value('id'),
            'name' => 'Standard Shipping',
            'code' => Str::upper($this->faker->unique()->bothify('SHIP-###')),
            'description' => $this->faker->sentence(8),
            'calculation_method' => 'flat_rate',
            'price' => $this->faker->randomFloat(2, 5, 20),
            'currency' => 'EUR',
            'min_price' => null,
            'max_price' => null,
            'min_weight' => null,
            'max_weight' => null,
            'weight_unit' => 'kg',
            'min_order_value' => null,
            'max_order_value' => null,
            'min_delivery_days' => 2,
            'max_delivery_days' => 5,
            'carrier' => null,
            'service_code' => null,
            'carrier_settings' => null,
            'is_active' => true,
            'priority' => 0,
            'data' => null,
        ];
    }
}