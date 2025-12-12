<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Cart;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class CartLineFactory extends Factory
{
    protected $model = \Cartino\Models\CartLine::class;

    /**
     * Provide default attributes for creating a CartLine model instance.
     *
     * The returned array maps model attributes to their default values for the factory:
     * - `cart_id` and `product_id` produce related Cart and Product factory instances.
     * - `product_variant_id`, `product_options`, and `meta` default to `null`.
     * - `quantity` is a random integer between 1 and 3.
     * - `unit_price` is a random float between 5 and 150 with two decimals.
     * - `line_total` is calculated as `unit_price * quantity`.
     *
     * @return array<string,mixed> Associative array of default attributes for the CartLine factory.
     */
    public function definition(): array
    {
        return [
            'cart_id' => fn () => Cart::factory(),
            'product_id' => fn () => Product::factory(),
            'product_variant_id' => null,
            'quantity' => $this->faker->numberBetween(1, 3),
            'unit_price' => $this->faker->randomFloat(2, 5, 150),
            'line_total' => fn (array $attributes) => ($attributes['unit_price'] ?? 0) * ($attributes['quantity'] ?? 1),
            'product_options' => null,
            'meta' => null,
        ];
    }

    /**
     * Configure the factory to produce a cart line tied to the given product variant.
     *
     * Sets product_id, product_variant_id, unit_price, and line_total to values from the provided variant.
     *
     * @param ProductVariant $variant The product variant to bind to the generated cart line.
     * @return static The factory instance with state configured for the given variant.
     */
    public function withVariant(ProductVariant $variant): static
    {
        return $this->state(fn () => [
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'unit_price' => $variant->price,
            'line_total' => $variant->price,
        ]);
    }
}