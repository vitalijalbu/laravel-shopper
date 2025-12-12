<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Product;
use Cartino\Models\Wishlist;
use Illuminate\Database\Eloquent\Factories\Factory;

class WishlistItemFactory extends Factory
{
    protected $model = \Cartino\Models\WishlistItem::class;

    /**
     * Provide default attribute values for a WishlistItem model instance.
     *
     * @return array{
     *     wishlist_id: \Illuminate\Database\Eloquent\Factories\Factory|\Cartino\Models\Wishlist,
     *     product_type: string,
     *     product_id: \Illuminate\Database\Eloquent\Factories\Factory|\Cartino\Models\Product,
     *     product_handle: string|null,
     *     product_data: array|null,
     *     variant_data: array|null,
     *     quantity: int,
     *     price_at_time: float,
     *     note: string|null
     * }
     */
    public function definition(): array
    {
        return [
            'wishlist_id' => fn () => Wishlist::factory(),
            'product_type' => 'entry',
            'product_id' => fn () => Product::factory(),
            'product_handle' => null,
            'product_data' => null,
            'variant_data' => null,
            'quantity' => 1,
            'price_at_time' => $this->faker->randomFloat(2, 10, 200),
            'note' => $this->faker->optional()->sentence(6),
        ];
    }
}