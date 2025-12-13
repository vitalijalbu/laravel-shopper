<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Discount;
use Illuminate\Database\Eloquent\Factories\Factory;

class DiscountFactory extends Factory
{
    protected $model = Discount::class;

    public function definition(): array
    {
        $type = $this->faker->randomElement(['percentage', 'fixed', 'buy_x_get_y', 'free_shipping']);

        return [
            'code' => strtoupper($this->faker->unique()->bothify('????-####')),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'type' => $type,
            'value' => $type === 'percentage' ? $this->faker->numberBetween(5, 50) : $this->faker->numberBetween(500, 5000),
            'min_purchase_amount' => $this->faker->optional()->numberBetween(1000, 10000),
            'max_discount_amount' => $this->faker->optional()->numberBetween(1000, 5000),
            'usage_limit' => $this->faker->optional()->numberBetween(10, 1000),
            'usage_limit_per_customer' => $this->faker->optional()->numberBetween(1, 5),
            'times_used' => $this->faker->numberBetween(0, 50),
            'starts_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            'ends_at' => $this->faker->dateTimeBetween('now', '+6 months'),
            'status' => $this->faker->randomElement(['active', 'inactive', 'scheduled', 'expired']),
            'applies_to' => $this->faker->randomElement(['all', 'products', 'categories', 'customers']),
            'target_selection' => [],
            'prerequisite_subtotal_range' => null,
            'prerequisite_quantity_range' => null,
            'customer_selection' => $this->faker->randomElement(['all', 'prerequisite', 'segment']),
            'allocation_method' => $this->faker->randomElement(['each', 'across']),
        ];
    }

    public function active(): self
    {
        return $this->state(['status' => 'active']);
    }
}
