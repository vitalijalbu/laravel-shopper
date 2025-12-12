<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\UserGroup;
use Illuminate\Database\Eloquent\Factories\Factory;

class UserGroupFactory extends Factory
{
    protected $model = UserGroup::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->unique()->words(2, true),
            'handle' => $this->faker->unique()->slug(2),
            'permissions' => $this->faker->randomElements(['view', 'create', 'edit', 'delete'], $this->faker->numberBetween(1, 4)),
        ];
    }
}
