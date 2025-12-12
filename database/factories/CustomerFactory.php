<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Customer;
use Cartino\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    /**
     * Define default attribute values for a Customer factory.
     *
     * @return array An associative array of attributes suitable for creating a Customer model instance.
     */
    public function definition(): array
    {
        $first = $this->faker->firstName();
        $last = $this->faker->lastName();

        return [
            'site_id' => Site::query()->inRandomOrder()->value('id'),
            'first_name' => $first,
            'last_name' => $last,
            'email' => strtolower(Str::slug($first.'.'.$last)).'@example.com',
            'email_verified_at' => now(),
            'password' => bcrypt('password'),
            'phone' => $this->faker->e164PhoneNumber(),
            'date_of_birth' => $this->faker->dateTimeBetween('-60 years', '-18 years'),
            'gender' => $this->faker->randomElement(['male', 'female', 'other']),
            'status' => 'active',
            'last_login_at' => now()->subDays($this->faker->numberBetween(1, 30)),
            'last_login_ip' => $this->faker->ipv4(),
            'data' => null,
        ];
    }

    /**
     * Configure the factory to produce a customer with an inactive status.
     *
     * @return static The factory instance configured to set `status` to 'inactive'.
     */
    public function inactive(): static
    {
        return $this->state(fn () => ['status' => 'inactive']);
    }
}