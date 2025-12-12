<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\ProductOption;
use Cartino\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

    /**
     * Define default attribute values for a ProductOption factory.
     *
     * Returns an associative array of attributes used when creating a ProductOption:
     * - `product_id`: lazy factory closure that creates an associated Product.
     * - `name`: option name.
     * - `position`: ordering index for the option.
     * - `values`: array of selectable option values.
     *
     * @return array<string,mixed> Associative array of model attributes.
     */
    public function definition(): array
    {
        return [
            'product_id' => fn () => Product::factory(),
            'name' => 'Size',
            'position' => 1,
            'values' => ['S', 'M', 'L'],
        ];
    }
}