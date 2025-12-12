<?php

namespace Database\Seeders;

use Cartino\Models\Address;
use Cartino\Models\Brand;
use Cartino\Models\Cart;
use Cartino\Models\CartLine;
use Cartino\Models\Category;
use Cartino\Models\Channel;
use Cartino\Models\Country;
use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Cartino\Models\ProductType;
use Cartino\Models\ProductVariant;
use Cartino\Models\Setting;
use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Cartino\Models\Site;
use Cartino\Models\TaxRate;
use Cartino\Models\User;
use Cartino\Models\VariantPrice;
use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CartinoSeeder extends Seeder
{
    use WithoutModelEvents;

    protected function packageDataPath(string $relativePath): string
    {
        return __DIR__ . '/../data/' . ltrim($relativePath, '/');
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Cartino with multi-site support and rich demo data...');

        DB::transaction(function () {
            $this->seedCoreData();
            $this->seedDemoCatalog();
            $this->seedCustomersAndOrders();
        });

        $this->command->info('✅ Cartino multi-site seeding completed!');
        $this->command->info('📍 Sites created: Main Store (main), Store Italia (it)');
        $this->command->info('🔑 Admin login: admin@admin.com / password');
    }

    /**
     * Seed base configuration (sites, currencies, permissions, settings...)
     */
    protected function seedCoreData(): void
    {
        $this->command->info('⚙️ Core setup...');

        // Load data from files
        $currencies = include $this->packageDataPath('currencies.php');
        $countries = include $this->packageDataPath('countries.php');
        $channels = include $this->packageDataPath('channels.php');
        $customerGroups = include $this->packageDataPath('customer_groups.php');

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
            $payload = [
                'site_id' => $mainSite->id,
                'name' => $channel['name'],
                'slug' => $channel['slug'],
                'description' => $channel['description'] ?? null,
                'type' => $channel['type'] ?? 'web',
                'url' => $channel['url'] ?? null,
                'is_default' => $channel['is_default'] ?? false,
                'status' => ($channel['is_enabled'] ?? true) ? 'active' : 'inactive',
                'locales' => $channel['locales'] ?? ['en', 'it'],
                'currencies' => $channel['currencies'] ?? ['EUR'],
                'settings' => $channel['settings'] ?? null,
            ];

            Channel::updateOrCreate(['slug' => $payload['slug']], $payload);
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
            'site_name' => 'Cartino Multi-Site',
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

        // Shipping zones and rates
        $this->command->info('🚚 Seeding shipping zones & rates...');
        $zone = ShippingZone::factory()->state([
            'site_id' => $mainSite->id,
            'countries' => ['IT', 'FR', 'DE', 'ES'],
        ])->create();

        ShippingRate::factory()->count(2)->state([
            'shipping_zone_id' => $zone->id,
            'channel_id' => Channel::where('slug', 'default')->value('id'),
        ])->create();

        // Taxes
        $this->command->info('🧾 Seeding tax rates...');
        TaxRate::factory()->state([
            'name' => 'VAT 22%',
            'code' => 'VAT_IT',
            'rate' => 0.22,
            'countries' => ['IT'],
        ])->create();

        // Payment methods
        $this->command->info('💳 Seeding payment methods...');
        DB::table('payment_methods')->updateOrInsert(
            ['slug' => 'stripe-card'],
            [
                'name' => 'Stripe Card',
                'provider' => 'stripe',
                'description' => 'Pay with credit card via Stripe',
                'configuration' => json_encode(['mode' => 'test']),
                'status' => 'active',
                'is_test_mode' => true,
                'fixed_fee' => 0.30,
                'percentage_fee' => 0.0290,
                'supported_currencies' => json_encode(['EUR', 'USD', 'GBP']),
                'supported_countries' => json_encode(['IT', 'FR', 'DE', 'ES', 'US']),
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        DB::table('payment_methods')->updateOrInsert(
            ['slug' => 'paypal'],
            [
                'name' => 'PayPal',
                'provider' => 'paypal',
                'description' => 'Checkout with PayPal',
                'configuration' => json_encode(['mode' => 'sandbox']),
                'status' => 'active',
                'is_test_mode' => true,
                'fixed_fee' => 0.35,
                'percentage_fee' => 0.0340,
                'supported_currencies' => json_encode(['EUR', 'USD', 'GBP']),
                'supported_countries' => json_encode(['IT', 'FR', 'DE', 'ES', 'US']),
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );
    }

    /**
     * Seed catalog data: categories, products, variants, prices, wishlists.
     */
    protected function seedDemoCatalog(): void
    {
        $this->command->info('🛍️ Building demo catalog...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();

        // Categories with a simple nested-set structure
        $categoriesData = [
            ['name' => 'Men', 'slug' => 'men', 'left' => 1, 'right' => 6],
            ['name' => 'Clothing', 'slug' => 'clothing', 'parent_slug' => 'men', 'left' => 2, 'right' => 3],
            ['name' => 'Shoes', 'slug' => 'shoes', 'parent_slug' => 'men', 'left' => 4, 'right' => 5],
            ['name' => 'Women', 'slug' => 'women', 'left' => 7, 'right' => 12],
            ['name' => 'Dresses', 'slug' => 'dresses', 'parent_slug' => 'women', 'left' => 8, 'right' => 9],
            ['name' => 'Sneakers', 'slug' => 'sneakers', 'parent_slug' => 'women', 'left' => 10, 'right' => 11],
        ];

        $categories = collect();
        foreach ($categoriesData as $data) {
            $parent = null;
            if (isset($data['parent_slug'])) {
                $parent = $categories->firstWhere('slug', $data['parent_slug']);
            }

            $category = Category::unguarded(function () use ($mainSite, $parent, $data) {
                return \Database\Factories\CategoryFactory::new()->create([
                    'site_id' => $mainSite->id,
                    'parent_id' => $parent?->id,
                    'level' => $parent ? $parent->level + 1 : 0,
                    'path' => $parent ? $parent->path.'/'.$data['slug'] : $data['slug'],
                    'left' => $data['left'],
                    'right' => $data['right'],
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'description' => 'Category '.$data['name'],
                    'short_description' => 'Short '.$data['name'],
                    'sort_order' => 0,
                    'is_featured' => false,
                    'is_active' => true,
                    'is_visible' => true,
                    'include_in_menu' => true,
                    'include_in_search' => true,
                    'products_count' => 0,
                ]);
            });

            $categories->push($category);
        }

        // Products with variants and advanced prices
        $products = collect();

        for ($i = 0; $i < 16; $i++) {
            $product = Product::factory()->forSite($mainSite->id)->create();

            // Two options per product
            ProductOption::factory()->create([
                'product_id' => $product->id,
                'name' => 'Color',
                'position' => 1,
                'values' => ['Red', 'Blue', 'Green'],
            ]);

            ProductOption::factory()->create([
                'product_id' => $product->id,
                'name' => 'Size',
                'position' => 2,
                'values' => ['S', 'M', 'L'],
            ]);

            $variants = ProductVariant::factory()
                ->count(3)
                ->state([
                    'product_id' => $product->id,
                    'site_id' => $product->site_id,
                ])
                ->create();

            // Attach categories (random two)
            $chosenCategories = $categories->random(2);
            foreach ($chosenCategories as $cat) {
                DB::table('category_product')->updateOrInsert([
                    'category_id' => $cat->id,
                    'product_id' => $product->id,
                ], [
                    'sort_order' => 0,
                    'is_primary' => false,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // Variant prices
            foreach ($variants as $variant) {
                VariantPrice::factory()->create([
                    'product_variant_id' => $variant->id,
                    'site_id' => $product->site_id,
                    'price' => $variant->price,
                ]);
            }

            // Update product price range and default variant
            $product->update([
                'default_variant_id' => $variants->first()->id,
                'variants_count' => $variants->count(),
                'price_min' => $variants->min('price'),
                'price_max' => $variants->max('price'),
            ]);

            $products->push($product);
        }

        $this->command->info('🛒 Products with variants and prices seeded: '.$products->count());
    }

    /**
     * Seed customers, carts, wishlists, orders.
     */
    protected function seedCustomersAndOrders(): void
    {
        $this->command->info('👥 Generating customers, carts, and orders...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();
        $currency = Currency::where('code', 'EUR')->firstOrFail();

        // Pull products/variants for associations
        $products = Product::with('variants')->get();

        $customers = Customer::factory()->count(8)->state([
            'site_id' => $mainSite->id,
            'status' => 'active',
        ])->create();

        foreach ($customers as $customer) {
            // Address
            Address::factory()->create([
                'addressable_type' => Customer::class,
                'addressable_id' => $customer->id,
            ]);

            // Wishlist
            $wishlist = Wishlist::factory()->state([
                'customer_id' => $customer->id,
            ])->create();

            WishlistItem::factory()->count(3)->create([
                'wishlist_id' => $wishlist->id,
                'product_id' => $products->random()->id,
            ]);

            // Cart with lines
            $cart = Cart::factory()->withCustomer($customer)->create([
                'status' => 'active',
                'currency' => 'EUR',
            ]);

            $lineVariants = $products->flatMap(fn ($p) => $p->variants)->shuffle()->take(2);
            $totals = ['subtotal' => 0, 'tax' => 0, 'shipping' => 5];

            foreach ($lineVariants as $variant) {
                $line = CartLine::factory()->create([
                    'cart_id' => $cart->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'unit_price' => $variant->price,
                    'quantity' => 1,
                    'line_total' => $variant->price,
                ]);
                $totals['subtotal'] += $line->line_total;
            }

            $cart->update([
                'subtotal' => $totals['subtotal'],
                'tax_amount' => round($totals['subtotal'] * 0.22, 2),
                'shipping_amount' => $totals['shipping'],
                'total_amount' => $totals['subtotal'] + round($totals['subtotal'] * 0.22, 2) + $totals['shipping'],
                'items' => null,
            ]);

            // Order with lines
            $order = Order::factory()->forCustomer($customer)->create([
                'site_id' => $mainSite->id,
                'currency_id' => $currency->id,
                'shipping_address' => $cart->shipping_address ?? [],
                'billing_address' => $cart->billing_address ?? [],
            ]);

            $orderSubtotal = 0;
            foreach ($lineVariants as $variant) {
                $line = OrderLine::factory()->forVariant($variant, $order)->create();
                $orderSubtotal += $line->line_total;
            }

            $orderTax = round($orderSubtotal * 0.22, 2);
            $orderShipping = 5;

            $order->update([
                'subtotal' => $orderSubtotal,
                'tax_total' => $orderTax,
                'shipping_total' => $orderShipping,
                'total' => $orderSubtotal + $orderTax + $orderShipping,
            ]);

            // Fidelity data
            $card = $customer->fidelityCard()->create([
                'total_points' => 500,
                'available_points' => 300,
                'total_earned' => 500,
                'total_redeemed' => 200,
                'total_spent_amount' => $order->total,
                'is_active' => true,
                'issued_at' => now()->subMonth(),
            ]);

            $card->transactions()->create([
                'order_id' => $order->id,
                'type' => 'earned',
                'points' => 200,
                'description' => 'Points from '.$order->order_number,
                'expires_at' => now()->addYear(),
            ]);
        }

        $this->command->info('👥 Customers seeded: '.$customers->count());
        $this->command->info('🧾 Orders seeded: '.$customers->count());
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
