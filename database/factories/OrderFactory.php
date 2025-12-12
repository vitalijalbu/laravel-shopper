<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\Order;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    /**
     * Generate attribute values for a new Order model instance.
     *
     * Returns an associative array of attributes suitable for creating an Order,
     * including site and currency ids, a unique order_number, optional customer
     * information, computed monetary totals (subtotal, tax_total, shipping_total,
     * discount_total, total), statuses (order, payment, fulfillment), payment and
     * shipping details, nullable timestamps and other meta fields.
     *
     * @return array<string,mixed> Associative array of Order attributes.
     */
    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 20, 500);
        $tax = round($subtotal * 0.22, 2);
        $shipping = $this->faker->randomFloat(2, 0, 20);
        $discount = $this->faker->randomFloat(2, 0, 30);
        $total = $subtotal + $tax + $shipping - $discount;

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'order_number' => 'ORD-'.$this->faker->unique()->numerify('#######'),
            'customer_id' => null,
            'customer_email' => $this->faker->safeEmail(),
            'customer_details' => null,
            'currency_id' => Currency::query()->inRandomOrder()->value('id'),
            'subtotal' => $subtotal,
            'tax_total' => $tax,
            'shipping_total' => $shipping,
            'discount_total' => $discount,
            'total' => $total,
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'fulfillment_status' => 'unfulfilled',
            'shipping_address' => null,
            'billing_address' => null,
            'applied_discounts' => null,
            'shipping_method' => 'standard',
            'payment_method' => 'card',
            'payment_details' => ['method' => 'card'],
            'notes' => null,
            'shipped_at' => null,
            'delivered_at' => null,
            'data' => null,
        ];
    }

    /**
     * Configure the factory to produce orders associated with the given customer.
     *
     * Sets the generated order's `customer_id`, `customer_email`, and `site_id` to the provided customer's values.
     *
     * @param Customer $customer The customer to associate with generated orders.
     * @return static The factory instance scoped to the specified customer.
     */
    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn () => [
            'customer_id' => $customer->id,
            'customer_email' => $customer->email,
            'site_id' => $customer->site_id,
        ]);
    }
}