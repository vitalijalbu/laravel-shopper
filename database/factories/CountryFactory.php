<?php

declare(strict_types=1);

namespace Cartino\Database\Factories;

use Cartino\Models\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

class CountryFactory extends Factory
{
    protected $model = Country::class;

    public function definition(): array
    {
        $countryCode = $this->faker->unique()->countryCode();

        return [
            'name' => $this->faker->country(),
            'iso3' => strtoupper($this->faker->lexify('???')),
            'iso2' => $countryCode,
            'phonecode' => $this->faker->numberBetween(1, 999),
            'capital' => $this->faker->city(),
            'currency_code' => $this->faker->currencyCode(),
            'currency_symbol' => $this->faker->randomElement(['€', '$', '£', '¥']),
            'tld' => '.'.strtolower($countryCode),
            'native' => $this->faker->country(),
            'region' => $this->faker->randomElement(['Europe', 'Asia', 'Americas', 'Africa', 'Oceania']),
            'subregion' => $this->faker->randomElement(['Southern Europe', 'Western Europe', 'Northern Europe', 'Eastern Europe']),
            'timezones' => [
                [
                    'zoneName' => $this->faker->timezone(),
                    'gmtOffset' => $this->faker->numberBetween(-43200, 43200),
                    'gmtOffsetName' => 'UTC'.$this->faker->randomElement(['+01:00', '+02:00', '-05:00']),
                    'abbreviation' => strtoupper($this->faker->lexify('???')),
                    'tzName' => $this->faker->timezone(),
                ],
            ],
            'translations' => [
                'en' => $this->faker->country(),
                'it' => $this->faker->country(),
            ],
            'latitude' => $this->faker->latitude(),
            'longitude' => $this->faker->longitude(),
            'emoji' => $this->faker->emoji(),
            'emojiU' => 'U+'.strtoupper($this->faker->lexify('????')),
        ];
    }
}
