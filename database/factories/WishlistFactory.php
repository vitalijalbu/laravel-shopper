<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class WishlistFactory extends Factory
{
    protected $model = Wishlist::class;

    /**
     * Provide default attributes for creating a Wishlist model instance.
     *
     * Returned array contains attribute values used when the factory creates a Wishlist:
     * - `customer_id`: a closure that produces an associated Customer factory.
     * - `name`: one of "Wishlist", "Favorites", or "Gift Ideas".
     * - `description`: a faker-generated sentence of eight words.
     * - `is_public`: boolean with about a 10% chance of being true.
     * - `share_token`: a 16-character random string.
     * - `metadata`: explicitly null.
     *
     * @return array<string,mixed> Associative array of default Wishlist attributes.
     */
    public function definition(): array
    {
        return [
            'customer_id' => fn () => Customer::factory(),
            'name' => $this->faker->randomElement(['Wishlist', 'Favorites', 'Gift Ideas']),
            'description' => $this->faker->sentence(8),
            'is_public' => $this->faker->boolean(10),
            'share_token' => Str::random(16),
            'metadata' => null,
        ];
    }
}