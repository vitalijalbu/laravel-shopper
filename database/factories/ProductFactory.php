<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Brand;
use Cartino\Models\Product;
use Cartino\Models\ProductType;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $title = $this->faker->unique()->productName();
        $slug = Str::slug($title);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'title' => $title,
            'slug' => $slug,
            'handle' => Str::slug($slug.'-'.$this->faker->unique()->numberBetween(10, 9999)),
            'excerpt' => $this->faker->sentence(10),
            'description' => $this->faker->paragraphs(3, true),
            'product_type' => 'physical',
            'brand_id' => fn () => Brand::query()->inRandomOrder()->value('id'),
            'product_type_id' => fn () => ProductType::query()->inRandomOrder()->value('id'),
            'options' => [
                ['name' => 'Color', 'values' => ['Red', 'Blue', 'Green']],
                ['name' => 'Size', 'values' => ['S', 'M', 'L']],
            ],
            'tags' => ['featured', 'demo'],
            'template_suffix' => null,
            'requires_selling_plan' => false,
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'published_scope' => 'web',
            'default_variant_id' => null,
            'variants_count' => 0,
            'images_count' => 0,
            'price_min' => null,
            'price_max' => null,
            'data' => null,
        ];
    }

    public function forSite(int $siteId): static
    {
        return $this->state(fn () => ['site_id' => $siteId]);
    }

    public function digital(): static
    {
        return $this->state(fn () => ['product_type' => 'digital']);
    }
}
