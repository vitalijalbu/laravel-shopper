<?php

declare(strict_types=1);

namespace Database\Factories;

use Cartino\Models\TaxRate;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TaxRateFactory extends Factory
{
    protected $model = TaxRate::class;

    /**
     * Provide default attribute values for creating a TaxRate model instance.
     *
     * @return array{
     *     name: string,
     *     code: string,
     *     rate: float,
     *     type: string,
     *     is_compound: bool,
     *     is_inclusive: bool,
     *     countries: string[],
     *     states: null|array,
     *     postcodes: null|array,
     *     product_collections: null|array,
     *     min_amount: null|float,
     *     max_amount: null|float,
     *     status: string,
     *     effective_from: \Illuminate\Support\Carbon,
     *     effective_until: null|\Illuminate\Support\Carbon,
     *     description: string
     * } Associative array of default attributes used to create a TaxRate.
     */
    public function definition(): array
    {
        $code = Str::upper($this->faker->lexify('TAX???'));

        return [
            'name' => 'VAT '.$this->faker->numberBetween(10, 25).'%',
            'code' => $code,
            'rate' => $this->faker->randomFloat(4, 0.05, 0.22),
            'type' => 'percentage',
            'is_compound' => false,
            'is_inclusive' => false,
            'countries' => ['IT'],
            'states' => null,
            'postcodes' => null,
            'product_collections' => null,
            'min_amount' => null,
            'max_amount' => null,
            'status' => 'active',
            'effective_from' => now()->subMonth(),
            'effective_until' => null,
            'description' => 'Standard VAT rate',
        ];
    }
}