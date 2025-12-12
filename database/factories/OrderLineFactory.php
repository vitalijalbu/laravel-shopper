<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Order;
use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderLineFactory extends Factory
{
    protected $model = \Cartino\Models\OrderLine::class;

    /**
     * Provide default attribute values for an OrderLine factory instance.
     *
     * The returned array contains attributes used to create an OrderLine, including factory closures
     * for related models (`order_id`, `product_id`), placeholder/nullable fields, generated product
     * metadata, pricing, quantity, and the computed `line_total`.
     *
     * @return array{
     *     order_id: (\Closure)|\Illuminate\Database\Eloquent\Factories\Factory,
     *     product_id: (\Closure)|\Illuminate\Database\Eloquent\Factories\Factory,
     *     product_variant_id: null|int,
     *     product_name: string,
     *     product_sku: string,
     *     product_options: null|array,
     *     quantity: int,
     *     unit_price: float,
     *     line_total: float,
     *     meta: null|array
     * }
     */
    public function definition(): array
    {
        $quantity = $this->faker->numberBetween(1, 3);
        $unitPrice = $this->faker->randomFloat(2, 5, 150);

        return [
            'order_id' => fn () => Order::factory(),
            'product_id' => fn () => Product::factory(),
            'product_variant_id' => null,
            'product_name' => $this->faker->productName(),
            'product_sku' => 'SKU-'.$this->faker->unique()->numerify('####'),
            'product_options' => null,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $unitPrice * $quantity,
            'meta' => null,
        ];
    }

    /**
     * Configure the factory to create an OrderLine tied to the given product variant and order.
     *
     * @param \Cartino\Models\ProductVariant $variant The product variant to use for product fields and pricing.
     * @param \Cartino\Models\Order $order The order to which the generated order line will belong.
     * @return static The factory instance configured with the variant's product/product variant IDs, title, SKU, price, a quantity of 1, and the corresponding line total for the specified order.
     */
    public function forVariant(ProductVariant $variant, Order $order): static
    {
        return $this->state(fn () => [
            'order_id' => $order->id,
            'product_id' => $variant->product_id,
            'product_variant_id' => $variant->id,
            'product_name' => $variant->title,
            'product_sku' => $variant->sku,
            'unit_price' => $variant->price,
            'quantity' => 1,
            'line_total' => $variant->price,
        ]);
    }
}