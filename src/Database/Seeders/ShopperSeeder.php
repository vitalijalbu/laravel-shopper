<?php

namespace Shopper\Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Shopper\Models\Brand;
use Shopper\Models\Channel;
use Shopper\Models\Collection;
use Shopper\Models\Country;
use Shopper\Models\Currency;
use Shopper\Models\CustomerGroup;
use Shopper\Models\ProductType;
use Shopper\Models\Setting;
use Shopper\Models\Site;
use Shopper\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ShopperSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Laravel Shopper with multi-site support...');

        // Clear any cached permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions and roles
        $this->createPermissionsAndRoles();

        // Create default currency
        $this->createDefaultCurrency();

        // Create default country
        $this->createDefaultCountry();

        // Create default site
        $site = $this->createDefaultSite();

        // Create default channel
        $channel = $this->createDefaultChannel($site);

        // Create customer group
        $this->createCustomerGroup();

        // Create product types
        $this->createProductTypes();

        // Create sample categories
        $this->createSampleCategories();

        // Create sample brands
        $this->createSampleBrands();

        // Create settings
        $this->createSettings($site, $channel);

        // Create admin user
        $this->createAdminUser();

        $this->command->info('✅ Laravel Shopper seeded successfully!');
    }

    /**
     * Create permissions and roles
     */
    private function createPermissionsAndRoles(): void
    {
        $this->command->info('📝 Creating permissions and roles...');

        // Get the default guard
        $guard = config('auth.defaults.guard', 'web');

        // Define permissions
        $permissions = [
            // Control Panel
            'access-cp' => 'Access Control Panel',
            'view-dashboard' => 'View Dashboard',
            'view-analytics' => 'View Analytics',
            'view-reports' => 'View Reports',

            // Products
            'view-products' => 'View Products',
            'create-products' => 'Create Products',
            'edit-products' => 'Edit Products',
            'delete-products' => 'Delete Products',

            // Categories
            'view-categories' => 'View Categories',
            'create-categories' => 'Create Categories',
            'edit-categories' => 'Edit Categories',
            'delete-categories' => 'Delete Categories',

            // Brands
            'view-brands' => 'View Brands',
            'create-brands' => 'Create Brands',
            'edit-brands' => 'Edit Brands',
            'delete-brands' => 'Delete Brands',

            // Collections
            'view-collections' => 'View Collections',
            'create-collections' => 'Create Collections',
            'edit-collections' => 'Edit Collections',
            'delete-collections' => 'Delete Collections',

            // Orders
            'view-orders' => 'View Orders',
            'create-orders' => 'Create Orders',
            'edit-orders' => 'Edit Orders',
            'delete-orders' => 'Delete Orders',

            // Customers
            'view-customers' => 'View Customers',
            'create-customers' => 'Create Customers',
            'edit-customers' => 'Edit Customers',
            'delete-customers' => 'Delete Customers',

            // Users & Permissions
            'view-users' => 'View Users',
            'create-users' => 'Create Users',
            'edit-users' => 'Edit Users',
            'delete-users' => 'Delete Users',
            'manage-roles' => 'Manage Roles',
            'manage-permissions' => 'Manage Permissions',

            // Settings
            'view-settings' => 'View Settings',
            'edit-settings' => 'Edit Settings',
        ];

        $createdPermissions = 0;
        foreach ($permissions as $name => $description) {
            Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => $guard],
                []
            );
            $createdPermissions++;
        }        $this->command->line("   ✓ Created {$createdPermissions} permissions");

        // Define roles with their permissions
        $roles = [
            'super-admin' => [
                'name' => 'Super Administrator',
                'permissions' => array_keys($permissions), // All permissions
            ],
            'admin' => [
                'name' => 'Administrator',
                'permissions' => [
                    'access-cp', 'view-dashboard', 'view-analytics', 'view-reports',
                    'view-products', 'create-products', 'edit-products', 'delete-products',
                    'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                    'view-brands', 'create-brands', 'edit-brands', 'delete-brands',
                    'view-collections', 'create-collections', 'edit-collections', 'delete-collections',
                    'view-orders', 'create-orders', 'edit-orders', 'delete-orders',
                    'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
                    'view-users', 'create-users', 'edit-users',
                    'view-settings', 'edit-settings',
                ],
            ],
            'manager' => [
                'name' => 'Manager',
                'permissions' => [
                    'access-cp', 'view-dashboard', 'view-analytics',
                    'view-products', 'create-products', 'edit-products',
                    'view-categories', 'create-categories', 'edit-categories',
                    'view-brands', 'create-brands', 'edit-brands',
                    'view-collections', 'create-collections', 'edit-collections',
                    'view-orders', 'edit-orders',
                    'view-customers', 'create-customers', 'edit-customers',
                    'view-settings',
                ],
            ],
            'editor' => [
                'name' => 'Editor',
                'permissions' => [
                    'access-cp', 'view-dashboard',
                    'view-products', 'create-products', 'edit-products',
                    'view-categories', 'create-categories', 'edit-categories',
                    'view-brands', 'create-brands', 'edit-brands',
                    'view-collections', 'create-collections', 'edit-collections',
                    'view-orders',
                    'view-customers',
                ],
            ],
        ];

        $createdRoles = 0;
        foreach ($roles as $roleKey => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleKey, 'guard_name' => $guard],
                []
            );

            // Sync permissions
            $role->syncPermissions($roleData['permissions']);
            $createdRoles++;
        }

        $this->command->line("   ✓ Created {$createdRoles} roles");
    }

    /**
     * Create default currency
     */
    private function createDefaultCurrency(): void
    {
        $this->command->info('💰 Creating default currency...');

        Currency::firstOrCreate([
            'name' => 'US Dollar',
            'code' => 'USD',
            'symbol' => '$',
            'rate' => 1.00,
            'is_default' => true,
            'is_enabled' => true,
        ]);

        $this->command->line('   ✓ USD currency created');
    }

    /**
     * Create default country
     */
    private function createDefaultCountry(): void
    {
        $this->command->info('🌍 Creating default country...');

        Country::firstOrCreate([
            'name' => 'United States',
            'code' => 'US',
            'phone_code' => '+1',
            'is_enabled' => true,
        ]);

        $this->command->line('   ✓ US country created');
    }

    /**
     * Create default site
     */
    private function createDefaultSite(): Site
    {
        $this->command->info('🏢 Creating default site...');

        $site = Site::firstOrCreate([
            'handle' => 'default',
            'name' => 'Default Store',
            'url' => config('app.url', 'http://localhost'),
            'locale' => config('app.locale', 'en'),
            'lang' => 'en',
            'order' => 1,
            'is_enabled' => true,
        ]);

        $this->command->line('   ✓ Default site created');

        return $site;
    }

    /**
     * Create default channel
     */
    private function createDefaultChannel(Site $site): Channel
    {
        $this->command->info('📺 Creating default channel...');

        $channel = Channel::firstOrCreate([
            'site_id' => $site->id,
            'name' => 'Web Store',
            'slug' => 'web-store',
        ], [
            'description' => 'Default web store channel',
            'url' => config('app.url', 'http://localhost'),
            'is_default' => true,
            'is_enabled' => true,
        ]);

        $this->command->line('   ✓ Default channel created');

        return $channel;
    }

    /**
     * Create customer group
     */
    private function createCustomerGroup(): void
    {
        $this->command->info('👥 Creating customer group...');

        CustomerGroup::firstOrCreate([
            'name' => 'Default Customers',
            'slug' => 'default-customers',
            'description' => 'Default customer group for all new customers',
            'is_default' => true,
            'is_enabled' => true,
        ]);

        $this->command->line('   ✓ Default customer group created');
    }

    /**
     * Create product types
     */
    private function createProductTypes(): void
    {
        $this->command->info('📦 Creating product types...');

        $types = [
            [
                'name' => 'Physical Product',
                'slug' => 'physical',
                'description' => 'Physical products that can be shipped',
                'is_enabled' => true,
            ],
            [
                'name' => 'Digital Product',
                'slug' => 'digital',
                'description' => 'Digital products and downloads',
                'is_enabled' => true,
            ],
            [
                'name' => 'Service',
                'slug' => 'service',
                'description' => 'Services and consultations',
                'is_enabled' => true,
            ],
        ];

        foreach ($types as $type) {
            ProductType::firstOrCreate(
                ['slug' => $type['slug']],
                $type
            );
        }

        $this->command->line('   ✓ Product types created');
    }

    /**
     * Create sample categories
     */
    private function createSampleCategories(): void
    {
        $this->command->info('📂 Creating sample categories...');

        $categories = [
            [
                'name' => 'Electronics',
                'slug' => 'electronics',
                'description' => 'Electronic devices and accessories',
                'sort_order' => 1,
                'is_enabled' => true,
            ],
            [
                'name' => 'Clothing',
                'slug' => 'clothing',
                'description' => 'Apparel and fashion items',
                'sort_order' => 2,
                'is_enabled' => true,
            ],
            [
                'name' => 'Books',
                'slug' => 'books',
                'description' => 'Books and publications',
                'sort_order' => 3,
                'is_enabled' => true,
            ],
            [
                'name' => 'Home & Garden',
                'slug' => 'home-garden',
                'description' => 'Home and garden products',
                'sort_order' => 4,
                'is_enabled' => true,
            ],
        ];

        foreach ($categories as $category) {
            Collection::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }

        $this->command->line('   ✓ Sample categories created');
    }

    /**
     * Create sample brands
     */
    private function createSampleBrands(): void
    {
        $this->command->info('🏷️ Creating sample brands...');

        $brands = [
            [
                'name' => 'Sample Brand',
                'slug' => 'sample-brand',
                'description' => 'A sample brand for demonstration',
                'website' => 'https://example.com',
                'is_enabled' => true,
            ],
            [
                'name' => 'Demo Company',
                'slug' => 'demo-company',
                'description' => 'Demo company for testing',
                'website' => 'https://demo.com',
                'is_enabled' => true,
            ],
        ];

        foreach ($brands as $brand) {
            Brand::firstOrCreate(
                ['slug' => $brand['slug']],
                $brand
            );
        }

        $this->command->line('   ✓ Sample brands created');
    }

    /**
     * Create settings
     */
    private function createSettings(Site $site, Channel $channel): void
    {
        $this->command->info('⚙️ Creating settings...');

        $settings = [
            [
                'key' => 'shop_name',
                'value' => 'Laravel Shopper',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Shop name',
            ],
            [
                'key' => 'shop_email',
                'value' => config('mail.from.address', 'admin@admin.com'),
                'type' => 'string',
                'group' => 'general',
                'description' => 'Shop email address',
            ],
            [
                'key' => 'default_currency',
                'value' => 'USD',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default currency code',
            ],
            [
                'key' => 'default_country',
                'value' => 'US',
                'type' => 'string',
                'group' => 'general',
                'description' => 'Default country code',
            ],
        ];

        foreach ($settings as $setting) {
            Setting::firstOrCreate(
                ['key' => $setting['key']],
                $setting
            );
        }

        $this->command->line('   ✓ Settings created');
    }

    /**
     * Create admin user
     */
    private function createAdminUser(): void
    {
        $this->command->info('👤 Creating admin user...');

        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Assign super-admin role
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
        }

        $this->command->line('   ✓ Admin user created (admin@admin.com / password)');
    }
}
