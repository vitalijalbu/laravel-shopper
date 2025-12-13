<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return [
            'menu_id' => Menu::query()->inRandomOrder()->value('id') ?? Menu::factory(),
            'parent_id' => null,
            'title' => $this->faker->words(2, true),
            'url' => $this->faker->url(),
            'target' => $this->faker->randomElement(['_self', '_blank']),
            'icon' => $this->faker->optional()->word(),
            'order' => $this->faker->numberBetween(1, 100),
            'is_active' => $this->faker->boolean(90),
        ];
    }
}
