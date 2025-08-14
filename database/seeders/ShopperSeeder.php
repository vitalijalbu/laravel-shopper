<?php

namespace VitaliJalbu\LaravelShopper\Database\Seeders;

use Illuminate\Database\Seeder;
use VitaliJalbu\LaravelShopper\Core\Models\Currency;
use VitaliJalbu\LaravelShopper\Core\Models\Channel;
use VitaliJalbu\LaravelShopper\Core\Models\CustomerGroup;
use VitaliJalbu\LaravelShopper\Core\Models\Country;
use VitaliJalbu\LaravelShopper\Core\Models\Brand;
use VitaliJalbu\LaravelShopper\Core\Models\Category;
use VitaliJalbu\LaravelShopper\Core\Models\ProductType;

class ShopperSeeder extends Seeder
{
    public function run(): void
    {
        $this->seedCurrencies();
        $this->seedChannels();
        $this->seedCustomerGroups();
        $this->seedCountries();
        $this->seedBrands();
        $this->seedCategories();
        $this->seedProductTypes();
    }

    private function seedCurrencies(): void
    {
        $currencies = [
            ['code' => 'USD', 'name' => 'US Dollar', 'exchange_rate' => 1.0000, 'default' => true, 'enabled' => true],
            ['code' => 'EUR', 'name' => 'Euro', 'exchange_rate' => 0.8500, 'default' => false, 'enabled' => true],
            ['code' => 'GBP', 'name' => 'British Pound', 'exchange_rate' => 0.7500, 'default' => false, 'enabled' => true],
            ['code' => 'JPY', 'name' => 'Japanese Yen', 'exchange_rate' => 110.0000, 'default' => false, 'enabled' => true],
        ];

        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['code' => $currency['code']], $currency);
        }
    }

    private function seedChannels(): void
    {
        $channels = [
            ['name' => 'Web Store', 'handle' => 'web', 'url' => 'https://example.com', 'default' => true],
            ['name' => 'Mobile App', 'handle' => 'mobile', 'url' => null, 'default' => false],
            ['name' => 'Marketplace', 'handle' => 'marketplace', 'url' => null, 'default' => false],
        ];

        foreach ($channels as $channel) {
            Channel::firstOrCreate(['handle' => $channel['handle']], $channel);
        }
    }

    private function seedCustomerGroups(): void
    {
        $groups = [
            ['name' => 'Retail', 'handle' => 'retail', 'default' => true],
            ['name' => 'Wholesale', 'handle' => 'wholesale', 'default' => false],
            ['name' => 'VIP', 'handle' => 'vip', 'default' => false],
        ];

        foreach ($groups as $group) {
            CustomerGroup::firstOrCreate(['handle' => $group['handle']], $group);
        }
    }

    private function seedCountries(): void
    {
        $countries = [
            [
                'name' => 'United States',
                'iso2' => 'US',
                'iso3' => 'USA',
                'numeric_code' => '840',
                'phone_code' => '+1',
                'capital' => 'Washington D.C.',
                'currency' => 'USD',
                'currency_name' => 'US Dollar',
                'currency_symbol' => '$',
                'tld' => '.us',
                'region' => 'Americas',
                'subregion' => 'Northern America'
            ],
            [
                'name' => 'United Kingdom',
                'iso2' => 'GB',
                'iso3' => 'GBR',
                'numeric_code' => '826',
                'phone_code' => '+44',
                'capital' => 'London',
                'currency' => 'GBP',
                'currency_name' => 'British Pound',
                'currency_symbol' => '£',
                'tld' => '.uk',
                'region' => 'Europe',
                'subregion' => 'Northern Europe'
            ],
            [
                'name' => 'Germany',
                'iso2' => 'DE',
                'iso3' => 'DEU',
                'numeric_code' => '276',
                'phone_code' => '+49',
                'capital' => 'Berlin',
                'currency' => 'EUR',
                'currency_name' => 'Euro',
                'currency_symbol' => '€',
                'tld' => '.de',
                'region' => 'Europe',
                'subregion' => 'Western Europe'
            ],
        ];

        foreach ($countries as $country) {
            Country::firstOrCreate(['iso2' => $country['iso2']], $country);
        }
    }

    private function seedBrands(): void
    {
        $brands = [
            ['name' => 'Apple', 'slug' => 'apple', 'description' => 'Technology products'],
            ['name' => 'Nike', 'slug' => 'nike', 'description' => 'Athletic wear and equipment'],
            ['name' => 'Samsung', 'slug' => 'samsung', 'description' => 'Electronics and technology'],
            ['name' => 'Adidas', 'slug' => 'adidas', 'description' => 'Sports apparel and footwear'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }
    }

    private function seedCategories(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets', 'parent_id' => null, 'position' => 1],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Apparel and fashion', 'parent_id' => null, 'position' => 2],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and apparel', 'parent_id' => null, 'position' => 3],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }

        // Sub-categories
        $electronicsId = Category::where('slug', 'electronics')->first()->id;
        $clothingId = Category::where('slug', 'clothing')->first()->id;
        
        $subCategories = [
            ['name' => 'Smartphones', 'slug' => 'smartphones', 'description' => 'Mobile phones', 'parent_id' => $electronicsId, 'position' => 1],
            ['name' => 'Laptops', 'slug' => 'laptops', 'description' => 'Portable computers', 'parent_id' => $electronicsId, 'position' => 2],
            ['name' => 'T-Shirts', 'slug' => 't-shirts', 'description' => 'Casual t-shirts', 'parent_id' => $clothingId, 'position' => 1],
            ['name' => 'Jeans', 'slug' => 'jeans', 'description' => 'Denim jeans', 'parent_id' => $clothingId, 'position' => 2],
        ];

        foreach ($subCategories as $category) {
            Category::firstOrCreate(['slug' => $category['slug']], $category);
        }
    }

    private function seedProductTypes(): void
    {
        $types = [
            ['name' => 'Physical Product'],
            ['name' => 'Digital Product'],
            ['name' => 'Service'],
            ['name' => 'Subscription'],
        ];

        foreach ($types as $type) {
            ProductType::firstOrCreate(['name' => $type['name']], $type);
        }
    }
}
