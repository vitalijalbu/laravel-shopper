<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\Address;
use Cartino\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class AddressFactory extends Factory
{
    protected $model = Address::class;

    /**
     * Provide default attribute values for creating an Address model instance.
     *
     * @return array<string,mixed> Associative array of Address attributes to default values (uses Faker for names, company, address, contact and coordinates); keys include `addressable_type`, `addressable_id`, `type`, `label`, `first_name`, `last_name`, `company`, `address_line_1`, `address_line_2`, `city`, `state`, `postal_code`, `country_id`, `phone`, `email`, `latitude`, `longitude`, `formatted_address`, `place_id`, `is_validated`, `validated_at`, `validation_source`, `is_default`, `is_default_billing`, `is_default_shipping`, `metadata`, and `notes`.
     */
    public function definition(): array
    {
        return [
            'addressable_type' => null,
            'addressable_id' => null,
            'type' => 'shipping',
            'label' => 'Home',
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'company' => $this->faker->company(),
            'address_line_1' => $this->faker->streetAddress(),
            'address_line_2' => null,
            'city' => $this->faker->city(),
            'state' => $this->faker->state(),
            'postal_code' => $this->faker->postcode(),
            'country_id' => Country::query()->inRandomOrder()->value('id'),
            'phone' => $this->faker->phoneNumber(),
            'email' => $this->faker->safeEmail(),
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'formatted_address' => null,
            'place_id' => null,
            'is_validated' => false,
            'validated_at' => null,
            'validation_source' => null,
            'is_default' => true,
            'is_default_billing' => true,
            'is_default_shipping' => true,
            'metadata' => null,
            'notes' => null,
        ];
    }
}