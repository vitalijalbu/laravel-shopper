<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\CustomerGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerGroupFactory extends Factory
{
    protected $model = CustomerGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->randomElement([
                'Retail Customers',
                'Wholesale',
                'VIP Members',
                'Business Partners',
                'Premium Club',
                'Regular Members',
            ]),
            'description' => $this->faker->sentence(),
            'is_default' => false,
            'discount_percentage' => $this->faker->randomFloat(2, 0, 25),
            'settings' => [
                'min_order_value' => $this->faker->numberBetween(0, 100),
                'free_shipping' => $this->faker->boolean(),
            ],
        ];
    }

    public function default(): self
    {
        return $this->state(['is_default' => true, 'discount_percentage' => 0]);
    }
}
