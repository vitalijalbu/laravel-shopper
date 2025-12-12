<?php

namespace Database\Seeders;

use Cartino\Models\Address;
use Cartino\Models\AnalyticsEvent;
use Cartino\Models\Brand;
use Cartino\Models\Cart;
use Cartino\Models\CartLine;
use Cartino\Models\Category;
use Cartino\Models\Channel;
use Cartino\Models\Country;
use Cartino\Models\Currency;
use Cartino\Models\Customer;
use Cartino\Models\CustomerGroup;
use Cartino\Models\Discount;
use Cartino\Models\Favorite;
use Cartino\Models\Menu;
use Cartino\Models\MenuItem;
use Cartino\Models\Order;
use Cartino\Models\OrderLine;
use Cartino\Models\Page;
use Cartino\Models\Product;
use Cartino\Models\ProductOption;
use Cartino\Models\ProductReview;
use Cartino\Models\ProductType;
use Cartino\Models\ProductVariant;
use Cartino\Models\PurchaseOrder;
use Cartino\Models\PurchaseOrderItem;
use Cartino\Models\ReviewMedia;
use Cartino\Models\ReviewVote;
use Cartino\Models\Setting;
use Cartino\Models\ShippingRate;
use Cartino\Models\ShippingZone;
use Cartino\Models\Site;
use Cartino\Models\StockNotification;
use Cartino\Models\Supplier;
use Cartino\Models\TaxRate;
use Cartino\Models\Transaction;
use Cartino\Models\User;
use Cartino\Models\UserGroup;
use Cartino\Models\VariantPrice;
use Cartino\Models\Wishlist;
use Cartino\Models\WishlistItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CartinoSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🌱 Seeding Cartino with MASSIVE multi-site demo data...');
        $this->command->warn('⚠️  This will create LARGE amounts of data - please be patient!');

        DB::transaction(function () {
            $this->seedCoreData();
            $this->seedDemoCatalog();
            $this->seedCustomersAndOrders();
        });

        $this->command->info('');
        $this->command->info('✅ ========================================');
        $this->command->info('✅ Cartino MASSIVE seeding completed!');
        $this->command->info('✅ ========================================');
        $this->command->info('');
        $this->command->info('📊 Data Summary:');
        $this->command->info('   • Sites: 2');
        $this->command->info('   • Users: ~26');
        $this->command->info('   • Currencies: ~11');
        $this->command->info('   • Countries: ~50');
        $this->command->info('   • Brands: ~50');
        $this->command->info('   • Categories: ~80+');
        $this->command->info('   • Products: ~500 (with 3-5 variants each)');
        $this->command->info('   • Product Reviews: ~2000+');
        $this->command->info('   • Customers: ~200');
        $this->command->info('   • Orders: ~600+');
        $this->command->info('   • Discounts: ~150');
        $this->command->info('   • Pages: ~80');
        $this->command->info('   • Suppliers: ~30');
        $this->command->info('   • Purchase Orders: ~100+');
        $this->command->info('   • Analytics Events: ~10,000');
        $this->command->info('   • Stock Notifications: ~500');
        $this->command->info('');
        $this->command->info('🔑 Admin login: admin@admin.com / password');
        $this->command->info('');
    }

    /**
     * Seed base configuration (sites, currencies, permissions, settings...)
     */
    protected function seedCoreData(): void
    {
        $this->command->info('⚙️ Core setup...');

        // Seed Sites
        $this->command->info('🌐 Seeding sites...');
        $mainSite = Site::factory()->state([
            'handle' => 'main',
            'name' => 'Main Store',
            'locale' => 'en_US',
            'lang' => 'en',
            'is_default' => true,
            'status' => 'active',
            'order' => 1,
        ])->create();

        Site::factory()->state([
            'handle' => 'it',
            'name' => 'Store Italia',
            'locale' => 'it_IT',
            'lang' => 'it',
            'status' => 'active',
            'order' => 2,
        ])->create();

        // Seed currencies
        $this->command->info('💰 Seeding currencies...');
        Currency::factory()->state([
            'code' => 'EUR',
            'name' => 'Euro',
            'symbol' => '€',
            'is_default' => true,
            'rate' => 1.0000,
        ])->create();

        Currency::factory()->count(10)->create();

        // Seed countries
        $this->command->info('🌍 Seeding countries...');
        Country::factory()->count(50)->create();

        // Create permissions and roles
        $this->command->info('🔑 Creating permissions and roles...');
        $this->createPermissionsAndRoles();

        // Create default admin user
        $this->command->info('👤 Creating admin user...');
        $user = User::factory()->admin()->create();

        // Assign super-admin role to user
        if (! $user->hasRole('super-admin')) {
            $user->assignRole('super-admin');
            $this->command->info('✅ Assigned super-admin role to admin user');
        }

        // Create additional users
        $this->command->info('👥 Creating additional users...');
        User::factory()->count(25)->create();

        // User groups
        $this->command->info('👥 Seeding user groups...');
        UserGroup::factory()->count(8)->create();

        // Seed channels
        $this->command->info('📺 Seeding channels...');
        Channel::factory()->state([
            'site_id' => $mainSite->id,
            'slug' => 'default',
            'name' => 'Default Channel',
            'is_default' => true,
            'status' => 'active',
        ])->create();

        Channel::factory()->count(5)->state([
            'site_id' => $mainSite->id,
        ])->create();

        // Seed customer groups
        $this->command->info('👥 Seeding customer groups...');
        CustomerGroup::factory()->default()->create();
        CustomerGroup::factory()->count(10)->create();

        // Seed brands
        $this->command->info('🏷️ Seeding brands...');
        Brand::factory()->count(500)->enabled()->create();

        // Seed product types
        $this->command->info('📦 Seeding product types...');
        ProductType::factory()->state(['name' => 'Physical', 'slug' => 'physical', 'status' => 'active'])->create();
        ProductType::factory()->state(['name' => 'Digital', 'slug' => 'digital', 'status' => 'active'])->create();
        ProductType::factory()->count(10)->active()->create();

        // Settings
        $this->command->info('⚙️ Seeding settings...');
        Setting::factory()->count(50)->create();

        // Shipping zones and rates
        $this->command->info('🚚 Seeding shipping zones & rates...');
        $zones = ShippingZone::factory()->count(10)->state([
            'site_id' => $mainSite->id,
        ])->create();

        foreach ($zones as $zone) {
            ShippingRate::factory()->count(5)->state([
                'shipping_zone_id' => $zone->id,
                'channel_id' => Channel::where('slug', 'default')->value('id'),
            ])->create();
        }

        // Taxes
        $this->command->info('🧾 Seeding tax rates...');
        TaxRate::factory()->count(20)->create();

        // Payment methods
        $this->command->info('💳 Seeding payment methods...');
        DB::table('payment_methods')->insert(
            \Database\Factories\PaymentMethodFactory::new()->state([
                'slug' => 'stripe-card',
                'provider' => 'stripe',
                'status' => 'active',
            ])->raw()
        );

        DB::table('payment_methods')->insert(
            \Database\Factories\PaymentMethodFactory::new()->state([
                'slug' => 'paypal',
                'provider' => 'paypal',
                'status' => 'active',
            ])->raw()
        );

        // Discounts
        $this->command->info('🎟️ Seeding discounts...');
        Discount::factory()->count(100)->create();
        Discount::factory()->count(50)->active()->create();

        // Pages
        $this->command->info('📄 Seeding pages...');
        Page::factory()->count(50)->create();
        Page::factory()->count(30)->published()->create();

        // Menus
        $this->command->info('🗂️ Seeding menus...');
        $menus = Menu::factory()->count(5)->create();

        foreach ($menus as $menu) {
            MenuItem::factory()->count(10)->state(['menu_id' => $menu->id])->create();
        }

        // Suppliers
        $this->command->info('🏭 Seeding suppliers...');
        Supplier::factory()->count(30)->active()->create();
    }

    /**
     * Seed catalog data: categories, products, variants, prices, wishlists.
     */
    protected function seedDemoCatalog(): void
    {
        $this->command->info('🛍️ Building demo catalog...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();

        // Categories with factory
        $this->command->info('📂 Seeding categories...');
        $categories = collect();

        // Create root categories
        $rootCategories = [
            ['name' => 'Men', 'slug' => 'men', 'left' => 1, 'right' => 20],
            ['name' => 'Women', 'slug' => 'women', 'left' => 21, 'right' => 40],
            ['name' => 'Kids', 'slug' => 'kids', 'left' => 41, 'right' => 60],
            ['name' => 'Electronics', 'slug' => 'electronics', 'left' => 61, 'right' => 80],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'left' => 81, 'right' => 100],
        ];

        foreach ($rootCategories as $rootData) {
            $rootCat = Category::factory()->state([
                'site_id' => $mainSite->id,
                'name' => $rootData['name'],
                'slug' => $rootData['slug'],
                'parent_id' => null,
                'level' => 0,
                'left' => $rootData['left'],
                'right' => $rootData['right'],
            ])->create();
            $categories->push($rootCat);

            // Create subcategories for each root category
            for ($i = 0; $i < 5; $i++) {
                $subCat = Category::factory()->state([
                    'site_id' => $mainSite->id,
                    'parent_id' => $rootCat->id,
                    'level' => 1,
                    'path' => $rootData['slug'].'/'.fake()->slug(2),
                ])->create();
                $categories->push($subCat);
            }
        }

        // Create even more categories
        Category::factory()->count(50)->state([
            'site_id' => $mainSite->id,
        ])->create()->each(fn ($cat) => $categories->push($cat));

        $this->command->info('✅ Categories created: '.$categories->count());

        // Products with variants using factory - MASSIVE QUANTITY
        $this->command->info('🛍️ Seeding MASSIVE product catalog...');
        $this->command->info('⏳ This will take a while... creating 500 products with variants...');

        $products = collect();
        $batchSize = 100;
        $totalProducts = 5000;

        for ($batch = 0; $batch < ($totalProducts / $batchSize); $batch++) {
            $this->command->info('📦 Processing batch '.($batch + 1).' of '.($totalProducts / $batchSize));

            for ($i = 0; $i < $batchSize; $i++) {
                $product = Product::factory()->state([
                    'site_id' => $mainSite->id,
                ])->create();

                // Product options using factory
                ProductOption::factory()->count(2)->state([
                    'product_id' => $product->id,
                ])->create();

                // Variants using factory (3-5 variants per product)
                $variantCount = rand(3, 5);
                $variants = ProductVariant::factory()
                    ->count($variantCount)
                    ->state([
                        'product_id' => $product->id,
                        'site_id' => $product->site_id,
                    ])
                    ->create();

                // Attach random categories (1-3 per product)
                $numCategories = rand(1, min(3, $categories->count()));
                $chosenCategories = $categories->random($numCategories);

                foreach ($chosenCategories as $cat) {
                    DB::table('category_product')->insert([
                        'category_id' => $cat->id,
                        'product_id' => $product->id,
                        'sort_order' => 0,
                        'is_primary' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Variant prices using factory
                foreach ($variants as $variant) {
                    VariantPrice::factory()->state([
                        'product_variant_id' => $variant->id,
                        'site_id' => $product->site_id,
                        'price' => $variant->price,
                    ])->create();
                }

                // Update product price range
                $product->update([
                    'default_variant_id' => $variants->first()->id,
                    'variants_count' => $variants->count(),
                    'price_min' => $variants->min('price'),
                    'price_max' => $variants->max('price'),
                ]);

                $products->push($product);
            }
        }

        $this->command->info('✅ Products with variants seeded: '.$products->count());

        // Product Reviews - MASSIVE
        $this->command->info('⭐ Seeding product reviews...');
        $productIds = $products->pluck('id')->toArray();

        foreach (array_slice($productIds, 0, 200) as $productId) {
            $reviewCount = rand(2, 15);
            $reviews = ProductReview::factory()->count($reviewCount)->state([
                'product_id' => $productId,
            ])->approved()->create();

            // Add media to some reviews
            foreach ($reviews->take(rand(1, 3)) as $review) {
                ReviewMedia::factory()->count(rand(1, 3))->state([
                    'product_review_id' => $review->id,
                ])->create();
            }

            // Add votes to reviews
            foreach ($reviews as $review) {
                ReviewVote::factory()->count(rand(0, 20))->state([
                    'product_review_id' => $review->id,
                ])->create();
            }
        }

        // Purchase Orders & Suppliers
        $this->command->info('📋 Seeding purchase orders...');
        $suppliers = Supplier::all();

        foreach ($suppliers->take(20) as $supplier) {
            $pos = PurchaseOrder::factory()->count(rand(2, 8))->state([
                'supplier_id' => $supplier->id,
            ])->create();

            foreach ($pos as $po) {
                PurchaseOrderItem::factory()->count(rand(5, 15))->state([
                    'purchase_order_id' => $po->id,
                ])->create();
            }
        }

        // Stock Notifications
        $this->command->info('🔔 Seeding stock notifications...');
        StockNotification::factory()->count(500)->create();

        // Analytics Events
        $this->command->info('📊 Seeding analytics events...');
        AnalyticsEvent::factory()->count(10000)->create();
    }

    /**
     * Seed customers, carts, wishlists, orders.
     */
    protected function seedCustomersAndOrders(): void
    {
        $this->command->info('👥 Generating MASSIVE customer and order data...');
        $mainSite = Site::where('handle', 'main')->firstOrFail();
        $currency = Currency::where('code', 'EUR')->first() ?? Currency::factory()->state(['code' => 'EUR'])->create();

        // Get products with variants
        $products = Product::with('variants')->limit(100)->get();

        // Create MASSIVE amount of customers
        $this->command->info('👥 Creating 200 customers...');
        $customers = Customer::factory()->count(200)->state([
            'site_id' => $mainSite->id,
            'status' => 'active',
        ])->create();

        $this->command->info('🛒 Processing customer data (carts, wishlists, orders)...');

        foreach ($customers as $index => $customer) {
            if ($index % 20 === 0) {
                $this->command->info('  Processing customer '.($index + 1).' of '.$customers->count());
            }

            // Address using factory (1-3 addresses per customer)
            Address::factory()->count(rand(1, 3))->state([
                'addressable_type' => Customer::class,
                'addressable_id' => $customer->id,
            ])->create();

            // Wishlist using factory
            $wishlist = Wishlist::factory()->state([
                'customer_id' => $customer->id,
            ])->create();

            // Wishlist items using factory (3-15 items)
            $wishlistItemCount = rand(3, 15);
            WishlistItem::factory()->count($wishlistItemCount)->state([
                'wishlist_id' => $wishlist->id,
                'product_id' => $products->random()->id,
            ])->create();

            // Favorites
            Favorite::factory()->count(rand(5, 20))->state([
                'customer_id' => $customer->id,
            ])->create();

            // Cart with lines using factory
            $cart = Cart::factory()->state([
                'customer_id' => $customer->id,
                'status' => 'active',
                'currency' => 'EUR',
            ])->create();

            $lineCount = rand(1, 8);
            $lineVariants = $products->flatMap(fn ($p) => $p->variants)->shuffle()->take($lineCount);
            $totals = ['subtotal' => 0, 'tax' => 0, 'shipping' => 5];

            foreach ($lineVariants as $variant) {
                $quantity = rand(1, 5);
                $line = CartLine::factory()->state([
                    'cart_id' => $cart->id,
                    'product_id' => $variant->product_id,
                    'product_variant_id' => $variant->id,
                    'unit_price' => $variant->price,
                    'quantity' => $quantity,
                    'line_total' => $variant->price * $quantity,
                ])->create();

                $totals['subtotal'] += $line->line_total;
            }

            $cart->update([
                'subtotal' => $totals['subtotal'],
                'tax_amount' => round($totals['subtotal'] * 0.22, 2),
                'shipping_amount' => $totals['shipping'],
                'total_amount' => $totals['subtotal'] + round($totals['subtotal'] * 0.22, 2) + $totals['shipping'],
                'items' => null,
            ]);

            // Create multiple orders per customer (1-5 orders)
            $orderCount = rand(1, 5);

            for ($o = 0; $o < $orderCount; $o++) {
                $order = Order::factory()->state([
                    'customer_id' => $customer->id,
                    'site_id' => $mainSite->id,
                    'currency_id' => $currency->id,
                    'shipping_address' => $cart->shipping_address ?? [],
                    'billing_address' => $cart->billing_address ?? [],
                ])->create();

                $orderLineCount = rand(1, 10);
                $orderLineVariants = $products->flatMap(fn ($p) => $p->variants)->shuffle()->take($orderLineCount);
                $orderSubtotal = 0;

                foreach ($orderLineVariants as $variant) {
                    $line = OrderLine::factory()->state([
                        'order_id' => $order->id,
                        'product_id' => $variant->product_id,
                        'product_variant_id' => $variant->id,
                    ])->create();

                    $orderSubtotal += $line->line_total;
                }

                $orderTax = round($orderSubtotal * 0.22, 2);
                $orderShipping = rand(0, 20);

                $order->update([
                    'subtotal' => $orderSubtotal,
                    'tax_total' => $orderTax,
                    'shipping_total' => $orderShipping,
                    'total' => $orderSubtotal + $orderTax + $orderShipping,
                ]);

                // Transactions for orders
                Transaction::factory()->count(rand(1, 2))->state([
                    'order_id' => $order->id,
                ])->successful()->create();
            }

            // Fidelity card using factory
            $card = $customer->fidelityCard()->create(
                \Database\Factories\FidelityCardFactory::new()->state([
                    'total_points' => rand(100, 5000),
                    'available_points' => rand(50, 3000),
                    'total_earned' => rand(500, 10000),
                    'total_redeemed' => rand(0, 2000),
                    'is_active' => true,
                    'issued_at' => now()->subMonths(rand(1, 24)),
                ])->raw()
            );

            // Multiple fidelity transactions per customer
            for ($t = 0; $t < rand(5, 20); $t++) {
                $card->transactions()->create(
                    \Database\Factories\FidelityTransactionFactory::new()->state([
                        'type' => fake()->randomElement(['earned', 'redeemed', 'expired']),
                        'points' => rand(10, 500),
                        'expires_at' => fake()->boolean(70) ? now()->addYear() : null,
                    ])->raw()
                );
            }
        }

        $this->command->info('✅ Customers seeded: '.$customers->count());
        $this->command->info('✅ Total orders created: ~'.($customers->count() * 3));
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
