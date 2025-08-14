<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use LaravelShopper\Models\Site;
use LaravelShopper\Models\Currency;
use LaravelShopper\Models\Country;
use LaravelShopper\Models\Channel;
use LaravelShopper\Models\CustomerGroup;
use LaravelShopper\Models\Setting;
use LaravelShopper\Models\User;
use LaravelShopper\Models\Brand;
use LaravelShopper\Models\Category;
use LaravelShopper\Models\ProductType;

class ShopperSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Laravel Shopper with multi-site support...');
        
        // Load data from files
        $currencies = include database_path('data/currencies.php');
        $countries = include database_path('data/countries.php');
        $channels = include database_path('data/channels.php');
        $customerGroups = include database_path('data/customer_groups.php');

        // Seed Sites first
        $this->command->info('🌐 Seeding sites...');
        $sites = [
            [
                'handle' => 'main',
                'name' => 'Main Store',
                'url' => 'http://localhost',
                'locale' => 'en_US',
                'lang' => 'en',
                'attributes' => [],
                'order' => 1,
                'is_enabled' => true
            ],
            [
                'handle' => 'it',
                'name' => 'Store Italia',
                'url' => 'http://localhost/it',
                'locale' => 'it_IT',
                'lang' => 'it',
                'attributes' => [],
                'order' => 2,
                'is_enabled' => true
            ],
        ];

        foreach ($sites as $site) {
            Site::firstOrCreate(['handle' => $site['handle']], $site);
        }

        // Seed currencies
        $this->command->info('💰 Seeding currencies...');
        foreach ($currencies as $currency) {
            Currency::firstOrCreate(['code' => $currency['code']], $currency);
        }

        // Seed countries
        $this->command->info('🌍 Seeding countries...');
        foreach ($countries as $country) {
            Country::firstOrCreate(['code' => $country['code']], $country);
        }

        // Create default admin user
        $this->command->info('👤 Creating admin user...');
        $user = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'first_name' => 'Admin',
                'last_name' => 'User',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'password' => bcrypt('password'),
            ]
        );

        // Get main site
        $mainSite = Site::where('handle', 'main')->first();

        // Seed channels
        $this->command->info('📺 Seeding channels...');
        foreach ($channels as $channel) {
            $currency = Currency::where('code', $channel['currency_code'])->first();
            $channel['currency_id'] = $currency?->id;
            $channel['site_id'] = $mainSite->id;
            unset($channel['currency_code']);
            Channel::firstOrCreate(['handle' => $channel['handle']], $channel);
        }

        // Seed customer groups
        $this->command->info('👥 Seeding customer groups...');
        foreach ($customerGroups as $group) {
            CustomerGroup::firstOrCreate(['name' => $group['name']], $group);
        }

        // Seed basic categories
        $this->command->info('📁 Seeding categories...');
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and accessories', 'site_id' => $mainSite->id],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel', 'site_id' => $mainSite->id],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and literature', 'site_id' => $mainSite->id],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home and garden products', 'site_id' => $mainSite->id],
        ];

        foreach ($categories as $category) {
            Category::firstOrCreate(['slug' => $category['slug'], 'site_id' => $category['site_id']], $category);
        }

        // Seed basic brands
        $this->command->info('🏷️ Seeding brands...');
        $brands = [
            ['name' => 'Generic', 'slug' => 'generic', 'description' => 'Generic brand for unbranded products'],
            ['name' => 'Premium', 'slug' => 'premium', 'description' => 'Premium quality products'],
            ['name' => 'Budget', 'slug' => 'budget', 'description' => 'Budget-friendly products'],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(['slug' => $brand['slug']], $brand);
        }

        // Seed product types
        $this->command->info('📦 Seeding product types...');
        $productTypes = [
            ['name' => 'Physical', 'slug' => 'physical', 'description' => 'Physical products that require shipping'],
            ['name' => 'Digital', 'slug' => 'digital', 'description' => 'Digital products (downloads)'],
            ['name' => 'Service', 'slug' => 'service', 'description' => 'Service-based products'],
        ];

        foreach ($productTypes as $type) {
            ProductType::firstOrCreate(['slug' => $type['slug']], $type);
        }

        // Basic settings
        $this->command->info('⚙️ Seeding settings...');
        $settings = [
            'site_name' => 'Laravel Shopper Multi-Site',
            'site_description' => 'Complete e-commerce platform with Statamic CMS architecture and multi-site support',
            'admin_email' => 'admin@example.com',
            'timezone' => 'Europe/Rome',
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'currency_format' => '{{amount}} {{symbol}}',
            'pagination_limit' => 25,
            'enable_reviews' => true,
            'enable_wishlists' => true,
            'enable_inventory' => true,
            'enable_seo' => true,
            'enable_analytics' => true,
            'enable_multisite' => true,
            'default_site' => 'main',
            'maintenance_mode' => false,
            'allow_guest_checkout' => true,
            'require_email_verification' => false,
            'enable_multi_currency' => true,
            'enable_multi_language' => true,
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], [
                'key' => $key,
                'value' => is_bool($value) ? ($value ? '1' : '0') : (string) $value,
                'type' => is_bool($value) ? 'boolean' : 'string'
            ]);
        }

        $this->command->info('✅ Laravel Shopper multi-site seeding completed!');
        $this->command->info('📍 Sites created: Main Store (main), Store Italia (it)');
        $this->command->info('🔑 Admin login: admin@example.com / password');
    }
}
