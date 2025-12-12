<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Category;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    /**
     * Provides a default set of attributes for creating a Category model instance.
     *
     * The returned associative array contains typical fields used when seeding or testing,
     * including hierarchical/nested-set values, display and SEO metadata, visibility flags,
     * timestamps, and relational identifiers.
     *
     * @return array<string,mixed> Associative array of Category attributes:
     *  - site_id: integer|null ID of a random existing Site.
     *  - parent_id: null (root by default).
     *  - level: integer Nesting level (0 for root).
     *  - path: string URL-friendly path/slug for the category.
     *  - left: integer Left value for nested-set.
     *  - right: integer Right value for nested-set.
     *  - name: string Human-readable category name (capitalized).
     *  - slug: string URL slug derived from the name.
     *  - description: string Long description.
     *  - short_description: string Short description.
     *  - sort_order: integer Sort order value.
     *  - icon: mixed|null Icon reference.
     *  - color: mixed|null Color value.
     *  - is_featured: bool Whether the category is featured.
     *  - meta_title: string Meta title for SEO.
     *  - meta_description: string Meta description for SEO.
     *  - seo: mixed|null Additional SEO data.
     *  - is_active: bool Whether the category is active.
     *  - is_visible: bool Whether the category is visible.
     *  - published_at: \Illuminate\Support\Carbon Publication timestamp in the past.
     *  - include_in_menu: bool Include in menus.
     *  - include_in_search: bool Include in search.
     *  - products_count: int Number of products in the category.
     *  - template: mixed|null Presentation template.
     *  - layout_settings: mixed|null Layout configuration.
     *  - data: mixed|null Arbitrary additional data.
     */
    public function definition(): array
    {
        $name = $this->faker->unique()->words(2, true);
        $slug = Str::slug($name);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'parent_id' => null,
            'level' => 0,
            'path' => $slug,
            'left' => 1,
            'right' => 2,
            'name' => ucfirst($name),
            'slug' => $slug,
            'description' => $this->faker->sentence(10),
            'short_description' => $this->faker->sentence(6),
            'sort_order' => $this->faker->numberBetween(0, 20),
            'icon' => null,
            'color' => null,
            'is_featured' => $this->faker->boolean(30),
            'meta_title' => ucfirst($name),
            'meta_description' => $this->faker->sentence(12),
            'seo' => null,
            'is_active' => true,
            'is_visible' => true,
            'published_at' => now()->subDays($this->faker->numberBetween(1, 15)),
            'include_in_menu' => true,
            'include_in_search' => true,
            'products_count' => 0,
            'template' => null,
            'layout_settings' => null,
            'data' => null,
        ];
    }

    /**
     * Create a factory state configured for a child of the given parent category.
     *
     * The state sets `parent_id`, `level` (parent level + 1), `path` (parent path plus a generated slug),
     * and the provided nested-set `left` and `right` values.
     *
     * @param Category $parent The parent category to inherit hierarchy values from.
     * @param int $left The left value for the nested set position.
     * @param int $right The right value for the nested set position.
     * @return static A factory instance with attributes set for the child category.
     */
    public function child(Category $parent, int $left, int $right): static
    {
        return $this->state(fn () => [
            'parent_id' => $parent->id,
            'level' => $parent->level + 1,
            'path' => $parent->path.'/'.$this->faker->slug(),
            'left' => $left,
            'right' => $right,
        ]);
    }
}