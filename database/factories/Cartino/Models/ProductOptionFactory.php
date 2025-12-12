<?php

declare(strict_types=1);

namespace Database\Factories\Cartino\Models;

use Cartino\Models\ProductOption;
use Cartino\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductOptionFactory extends Factory
{
    protected $model = ProductOption::class;

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
