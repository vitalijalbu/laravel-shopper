<?php

declare(strict_types=1);

namespace Database\Factories\Cartino\Models;

use Cartino\Models\Channel;
use Cartino\Models\Collection;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        $title = $this->faker->words(3, true);
        $slug = Str::slug($title);

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'channel_id' => Channel::query()->inRandomOrder()->value('id'),
            'title' => ucfirst($title),
            'slug' => $slug,
            'handle' => $slug,
            'description' => $this->faker->sentence(10),
            'body_html' => $this->faker->paragraph(),
            'collection_type' => 'manual',
            'rules' => null,
            'sort_order' => 'manual',
            'disjunctive' => false,
            'meta_title' => ucfirst($title),
            'meta_description' => $this->faker->sentence(12),
            'seo' => null,
            'status' => 'published',
            'published_at' => now()->subDays($this->faker->numberBetween(1, 20)),
            'published_scope' => 'web',
            'template_suffix' => null,
            'data' => null,
        ];
    }
}
