<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Cart;
use Cartino\Models\Customer;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartFactory extends Factory
{
    protected $model = Cart::class;

    /**
     * Provide the default attribute values for a Cart model factory.
     *
     * The returned array contains the cart's attributes used when creating model instances:
     * - `site_id`: identifier of an existing Site (randomly selected).
     * - `session_id`: unique session identifier (UUID).
     * - `customer_id`: associated customer id or `null`.
     * - `email`: contact email for the cart.
     * - `status`: cart state, e.g. `active` or `abandoned`.
     * - `items`: serialized items or `null`.
     * - `subtotal`, `tax_amount`, `shipping_amount`, `discount_amount`, `total_amount`: monetary values initialized to 0.
     * - `currency`: currency code (default `EUR`).
     * - `last_activity_at`: timestamp of last activity.
     * - `shipping_address`, `billing_address`: address data or `null`.
     * - `metadata`, `data`: arbitrary additional data or `null`.
     *
     * @return array<string,mixed> Associative array of default Cart attributes.
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'session_id' => $this->faker->uuid(),
            'customer_id' => null,
            'email' => $this->faker->safeEmail(),
            'status' => $this->faker->randomElement(['active', 'abandoned']),
            'items' => null,
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => 0,
            'currency' => 'EUR',
            'last_activity_at' => now(),
            'shipping_address' => null,
            'billing_address' => null,
            'metadata' => null,
            'data' => null,
        ];
    }

    /**
     * Configure the factory to produce carts associated with the given Customer.
     *
     * @param Customer $customer The Customer whose `id`, `email`, and `site_id` will be applied to generated carts.
     * @return static The factory instance configured with the customer's `customer_id`, `email`, and `site_id`.
     */
    public function withCustomer(Customer $customer): static
    {
        return $this->state(fn () => [
            'customer_id' => $customer->id,
            'email' => $customer->email,
            'site_id' => $customer->site_id,
        ]);
    }
}