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

    /**
     * Provide default attribute values for a Product model instance created by the factory.
     *
     * The returned array contains all model attributes used to create a Product, including generated
     * text fields (title, slug, handle, excerpt, description), relationship IDs, product metadata,
     * option templates, tags, publication info, and counts/price placeholders.
     *
     * Note: `brand_id` and `product_type_id` are lazy closures that resolve to a random existing ID
     * at factory invocation time; `published_at` is set to now minus a random 1–30 days.
     *
     * @return array<string, mixed> Associative array of Product attributes suitable for model creation.
     */
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

    /**
     * Configure the factory to assign a specific site ID to created products.
     *
     * @param int $siteId The site ID to assign.
     * @return static A factory instance that will set `site_id` to the given value.
     */
    public function forSite(int $siteId): static
    {
        return $this->state(fn () => ['site_id' => $siteId]);
    }

    /**
     * Configure the factory to produce a product with its `product_type` set to "digital".
     *
     * @return static A factory instance configured with `product_type` = "digital".
     */
    public function digital(): static
    {
        return $this->state(fn () => ['product_type' => 'digital']);
    }
}