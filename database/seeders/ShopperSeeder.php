<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Shopper\Models\Brand;
use Shopper\Models\Channel;
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
                'is_enabled' => true,
            ],
            [
                'handle' => 'it',
                'name' => 'Store Italia',
                'url' => 'http://localhost/it',
                'locale' => 'it_IT',
                'lang' => 'it',
                'attributes' => [],
                'order' => 2,
                'is_enabled' => true,
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

        // Create permissions and roles
        $this->command->info('🔑 Creating permissions and roles...');
        $this->createPermissionsAndRoles();

        // Create default admin user
        $this->command->info('👤 Creating admin user...');
        $user = User::firstOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Admin User',
                'email' => 'admin@admin.com',
                'email_verified_at' => now(),
                'password' => Hash::make('password'),
            ]
        );

        // Assign super-admin role to user
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
            $this->command->info('✅ Assigned super-admin role to admin user');
        }

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
            'admin_email' => 'admin@admin.com',
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
                'type' => is_bool($value) ? 'boolean' : 'string',
            ]);
        }

        $this->command->info('✅ Laravel Shopper multi-site seeding completed!');
        $this->command->info('📍 Sites created: Main Store (main), Store Italia (it)');
        $this->command->info('🔑 Admin login: admin@admin.com / password');
    }

    /**
     * Create permissions and roles for the control panel.
     */
    protected function createPermissionsAndRoles(): void
    {
        // Clear Spatie Permission cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $this->command->info('  Creating permissions...');

        // Define Control Panel permissions (similar to Statamic)
        $permissions = [
            // Dashboard
            'access-cp' => 'Access Control Panel',
            'view-dashboard' => 'View Dashboard',

            // Products
            'view-products' => 'View Products',
            'create-products' => 'Create Products',
            'edit-products' => 'Edit Products',
            'delete-products' => 'Delete Products',
            'manage-product-variants' => 'Manage Product Variants',

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
            'edit-orders' => 'Edit Orders',
            'delete-orders' => 'Delete Orders',
            'manage-order-status' => 'Manage Order Status',

            // Customers
            'view-customers' => 'View Customers',
            'create-customers' => 'Create Customers',
            'edit-customers' => 'Edit Customers',
            'delete-customers' => 'Delete Customers',

            // Settings
            'view-settings' => 'View Settings',
            'edit-settings' => 'Edit Settings',
            'manage-sites' => 'Manage Sites',
            'manage-channels' => 'Manage Channels',

            // Users & Permissions
            'view-users' => 'View Users',
            'create-users' => 'Create Users',
            'edit-users' => 'Edit Users',
            'delete-users' => 'Delete Users',
            'manage-roles' => 'Manage Roles',
            'manage-permissions' => 'Manage Permissions',

            // Analytics & Reports
            'view-analytics' => 'View Analytics',
            'view-reports' => 'View Reports',

            // Media
            'view-media' => 'View Media',
            'upload-media' => 'Upload Media',
            'delete-media' => 'Delete Media',
        ];

        // Create permissions
        $createdPermissions = 0;
        foreach ($permissions as $name => $description) {
            $permission = Permission::firstOrCreate(
                ['name' => $name, 'guard_name' => 'web'],
                ['name' => $name, 'guard_name' => 'web']
            );
            $createdPermissions++;
        }

        $this->command->info("  ✓ Created {$createdPermissions} permissions");
        $this->command->info('  Creating roles...');

        // Define roles
        $roles = [
            'super-admin' => [
                'description' => 'Super Administrator - Full access to everything',
                'permissions' => array_keys($permissions), // All permissions
            ],
            'admin' => [
                'description' => 'Administrator - Full access to store management',
                'permissions' => [
                    'access-cp', 'view-dashboard',
                    'view-products', 'create-products', 'edit-products', 'delete-products', 'manage-product-variants',
                    'view-categories', 'create-categories', 'edit-categories', 'delete-categories',
                    'view-brands', 'create-brands', 'edit-brands', 'delete-brands',
                    'view-collections', 'create-collections', 'edit-collections', 'delete-collections',
                    'view-orders', 'edit-orders', 'manage-order-status',
                    'view-customers', 'create-customers', 'edit-customers',
                    'view-settings', 'edit-settings',
                    'view-analytics', 'view-reports',
                    'view-media', 'upload-media',
                ],
            ],
            'manager' => [
                'description' => 'Store Manager - Limited administrative access',
                'permissions' => [
                    'access-cp', 'view-dashboard',
                    'view-products', 'create-products', 'edit-products',
                    'view-categories', 'create-categories', 'edit-categories',
                    'view-brands', 'create-brands', 'edit-brands',
                    'view-collections', 'create-collections', 'edit-collections',
                    'view-orders', 'edit-orders', 'manage-order-status',
                    'view-customers', 'create-customers', 'edit-customers',
                    'view-analytics', 'view-reports',
                    'view-media', 'upload-media',
                ],
            ],
            'editor' => [
                'description' => 'Content Editor - Content management only',
                'permissions' => [
                    'access-cp', 'view-dashboard',
                    'view-products', 'create-products', 'edit-products',
                    'view-categories', 'create-categories', 'edit-categories',
                    'view-brands', 'create-brands', 'edit-brands',
                    'view-collections', 'create-collections', 'edit-collections',
                    'view-media', 'upload-media',
                ],
            ],
        ];

        // Create roles and assign permissions
        $createdRoles = 0;
        foreach ($roles as $roleName => $roleData) {
            $role = Role::firstOrCreate(
                ['name' => $roleName, 'guard_name' => 'web'],
                ['name' => $roleName, 'guard_name' => 'web']
            );

            // Sync permissions
            $availablePermissions = Permission::whereIn('name', $roleData['permissions'])->get();
            $role->syncPermissions($availablePermissions);

            $createdRoles++;
            $this->command->info("  ✓ Created role: {$roleName} with {$availablePermissions->count()} permissions");
        }

        $this->command->info("✅ Roles and permissions setup completed ({$createdRoles} roles, {$createdPermissions} permissions)");
    }
}
