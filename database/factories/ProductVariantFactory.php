<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Product;
use Cartino\Models\ProductVariant;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Generate an attribute array for creating a ProductVariant populated with realistic default values.
     *
     * @return array Associative array of ProductVariant attributes, including:
     *               - product_id: closure returning a Product factory
     *               - site_id: selected site id
     *               - title, sku, barcode
     *               - option1, option2, option3
     *               - price, compare_at_price, cost
     *               - inventory fields (inventory_quantity, track_quantity, inventory_management, inventory_policy, fulfillment_service, inventory_quantity_adjustment, allow_out_of_stock_purchases)
     *               - weight, weight_unit
     *               - dimensions: ['length','width','height']
     *               - requires_shipping, taxable, tax_code
     *               - position, status, available, data
     */
    public function definition(): array
    {
        $sku = 'SKU-'.$this->faker->unique()->bothify('??###');

        return [
            'product_id' => fn () => Product::factory(),
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'title' => $this->faker->colorName().' / '.$this->faker->randomElement(['S', 'M', 'L']),
            'sku' => $sku,
            'barcode' => $this->faker->ean13(),
            'option1' => $this->faker->safeColorName(),
            'option2' => $this->faker->randomElement(['S', 'M', 'L']),
            'option3' => null,
            'price' => $this->faker->randomFloat(2, 9, 199),
            'compare_at_price' => $this->faker->randomFloat(2, 0, 299),
            'cost' => $this->faker->randomFloat(2, 3, 80),
            'inventory_quantity' => $this->faker->numberBetween(5, 200),
            'track_quantity' => true,
            'inventory_management' => 'shopify',
            'inventory_policy' => 'deny',
            'fulfillment_service' => 'manual',
            'inventory_quantity_adjustment' => 0,
            'allow_out_of_stock_purchases' => false,
            'weight' => $this->faker->randomFloat(2, 0.1, 5),
            'weight_unit' => 'kg',
            'dimensions' => [
                'length' => $this->faker->randomFloat(2, 10, 40),
                'width' => $this->faker->randomFloat(2, 10, 40),
                'height' => $this->faker->randomFloat(2, 5, 30),
            ],
            'requires_shipping' => true,
            'taxable' => true,
            'tax_code' => null,
            'position' => $this->faker->numberBetween(1, 3),
            'status' => 'active',
            'available' => true,
            'data' => null,
        ];
    }

    /**
     * Configure the factory to create product variants belonging to a specific site.
     *
     * @param int $siteId The ID of the site to assign to generated variants.
     * @return static The factory instance configured with `site_id` set to the provided value.
     */
    public function forSite(int $siteId): static
    {
        return $this->state(fn () => ['site_id' => $siteId]);
    }
}