<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Channel;
use Cartino\Models\CustomerGroup;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Cartino\Models\VariantPrice;
use Illuminate\Database\Eloquent\Factories\Factory;

class VariantPriceFactory extends Factory
{
    protected $model = VariantPrice::class;

    /**
     * Provide default attribute values for creating a VariantPrice model instance.
     *
     * Returns an associative array of attributes used by the factory to create a VariantPrice,
     * including related model references, currency and pricing fields, quantity limits,
     * active period, priority, and optional metadata.
     *
     * @return array{
     *   product_variant_id: (\Closure|ProductVariant::class),
     *   site_id: int|null,
     *   channel_id: int|null,
     *   customer_group_id: int|null,
     *   catalog_id: null,
     *   currency: string,
     *   price: float,
     *   compare_at_price: null,
     *   cost: float,
     *   min_quantity: int,
     *   max_quantity: null,
     *   starts_at: \Illuminate\Support\Carbon,
     *   ends_at: null,
     *   priority: int,
     *   data: null
     * }
     */
    public function definition(): array
    {
        return [
            'product_variant_id' => fn () => ProductVariant::factory(),
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'channel_id' => Channel::query()->inRandomOrder()->value('id'),
            'customer_group_id' => CustomerGroup::query()->inRandomOrder()->value('id'),
            'catalog_id' => null,
            'currency' => 'EUR',
            'price' => $this->faker->randomFloat(4, 5, 200),
            'compare_at_price' => null,
            'cost' => $this->faker->randomFloat(4, 2, 150),
            'min_quantity' => 1,
            'max_quantity' => null,
            'starts_at' => now()->subDays(5),
            'ends_at' => null,
            'priority' => 0,
            'data' => null,
        ];
    }
}