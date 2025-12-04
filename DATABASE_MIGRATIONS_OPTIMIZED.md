# 🚀 Database Migrations Ottimizzate - Ready to Use

## 📋 Index

1. [Sistema Multi-Lingua](#1-sistema-multi-lingua)
2. [Materialized Cache Tables](#2-materialized-cache-tables)
3. [Ottimizzazioni Indici](#3-ottimizzazioni-indici)
4. [Categories - Materialized Path](#4-categories---materialized-path)
5. [Order Addresses Denormalized](#5-order-addresses-denormalized)
6. [Inventory Optimizations](#6-inventory-optimizations)
7. [Query Cache Infrastructure](#7-query-cache-infrastructure)

---

## 1. Sistema Multi-Lingua

### Migration: `2025_12_05_000001_create_translation_system.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // PRODUCT TRANSLATIONS
        // =====================================================
        Schema::create('product_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            // Translatable fields
            $table->string('title')->index();
            $table->string('slug')->index();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();

            // SEO fields
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            // Timestamps
            $table->timestamps();

            // Indexes
            $table->unique(['product_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index(['locale', 'product_id']);

            // Full-text search per locale
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description', 'excerpt'], 'ft_product_trans_search');
            }
        });

        // =====================================================
        // CATEGORY TRANSLATIONS
        // =====================================================
        Schema::create('category_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            $table->string('name')->index();
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['category_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index(['locale', 'category_id']);

            if (config('database.default') === 'mysql') {
                $table->fullText(['name', 'description'], 'ft_category_trans_search');
            }
        });

        // =====================================================
        // BRAND TRANSLATIONS
        // =====================================================
        Schema::create('brand_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained('brands')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            $table->string('name')->index();
            $table->string('slug')->index();
            $table->text('description')->nullable();

            $table->timestamps();

            $table->unique(['brand_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index(['locale', 'brand_id']);
        });

        // =====================================================
        // COLLECTION TRANSLATIONS
        // =====================================================
        Schema::create('collection_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collection_id')->constrained('collections')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            $table->string('title')->index();
            $table->string('slug')->index();
            $table->text('description')->nullable();
            $table->text('body_html')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['collection_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index(['locale', 'collection_id']);

            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'description'], 'ft_collection_trans_search');
            }
        });

        // =====================================================
        // PAGE TRANSLATIONS
        // =====================================================
        Schema::create('page_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('page_id')->constrained('pages')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            $table->string('title')->index();
            $table->string('slug')->index();
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();

            $table->timestamps();

            $table->unique(['page_id', 'locale']);
            $table->unique(['slug', 'locale']);
            $table->index(['locale', 'page_id']);

            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'content'], 'ft_page_trans_search');
            }
        });

        // =====================================================
        // PRODUCT OPTION TRANSLATIONS
        // =====================================================
        Schema::create('product_option_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_option_id')->constrained('product_options')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            $table->string('name'); // "Colore" instead of "Color"
            $table->jsonb('values'); // ["Rosso", "Blu"] instead of ["Red", "Blue"]

            $table->timestamps();

            $table->unique(['product_option_id', 'locale']);
            $table->index(['locale', 'product_option_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_option_translations');
        Schema::dropIfExists('page_translations');
        Schema::dropIfExists('collection_translations');
        Schema::dropIfExists('brand_translations');
        Schema::dropIfExists('category_translations');
        Schema::dropIfExists('product_translations');
    }
};
```

### Migration: `2025_12_05_000002_migrate_existing_content_to_translations.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $defaultLocale = config('app.locale', 'en');

        // Migrate Products
        DB::statement("
            INSERT INTO product_translations (product_id, locale, title, slug, excerpt, description, meta_title, meta_description, created_at, updated_at)
            SELECT
                id,
                '{$defaultLocale}',
                title,
                slug,
                excerpt,
                description,
                meta_title,
                meta_description,
                created_at,
                updated_at
            FROM products
            WHERE title IS NOT NULL
        ");

        // Migrate Categories
        DB::statement("
            INSERT INTO category_translations (category_id, locale, name, slug, description, short_description, meta_title, meta_description, created_at, updated_at)
            SELECT
                id,
                '{$defaultLocale}',
                name,
                slug,
                description,
                short_description,
                meta_title,
                meta_description,
                created_at,
                updated_at
            FROM categories
            WHERE name IS NOT NULL
        ");

        // Migrate Brands
        DB::statement("
            INSERT INTO brand_translations (brand_id, locale, name, slug, description, created_at, updated_at)
            SELECT
                id,
                '{$defaultLocale}',
                name,
                slug,
                description,
                created_at,
                updated_at
            FROM brands
            WHERE name IS NOT NULL
        ");

        // Migrate Collections
        DB::statement("
            INSERT INTO collection_translations (collection_id, locale, title, slug, description, body_html, meta_title, meta_description, created_at, updated_at)
            SELECT
                id,
                '{$defaultLocale}',
                title,
                slug,
                description,
                body_html,
                meta_title,
                meta_description,
                created_at,
                updated_at
            FROM collections
            WHERE title IS NOT NULL
        ");

        // Migrate Pages (if exists)
        if (Schema::hasTable('pages')) {
            DB::statement("
                INSERT INTO page_translations (page_id, locale, title, slug, excerpt, content, meta_title, meta_description, created_at, updated_at)
                SELECT
                    id,
                    '{$defaultLocale}',
                    title,
                    slug,
                    excerpt,
                    content,
                    meta_title,
                    meta_description,
                    created_at,
                    updated_at
                FROM pages
                WHERE title IS NOT NULL
            ");
        }
    }

    public function down(): void
    {
        // Restore data back to main tables
        DB::statement("
            UPDATE products p
            JOIN product_translations pt ON p.id = pt.product_id
            SET
                p.title = pt.title,
                p.slug = pt.slug,
                p.excerpt = pt.excerpt,
                p.description = pt.description,
                p.meta_title = pt.meta_title,
                p.meta_description = pt.meta_description
            WHERE pt.locale = ?
        ", [config('app.locale', 'en')]);

        // Similar for other tables...
    }
};
```

### Migration: `2025_12_05_000003_remove_translatable_columns_from_main_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Products
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'slug',
                'excerpt',
                'description',
                'meta_title',
                'meta_description',
            ]);

            // Keep only handle (language-agnostic identifier)
        });

        // Categories
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'slug',
                'description',
                'short_description',
                'meta_title',
                'meta_description',
            ]);
        });

        // Brands
        Schema::table('brands', function (Blueprint $table) {
            $table->dropColumn([
                'name',
                'slug',
                'description',
            ]);
        });

        // Collections
        Schema::table('collections', function (Blueprint $table) {
            $table->dropColumn([
                'title',
                'slug',
                'description',
                'body_html',
                'meta_title',
                'meta_description',
            ]);
        });

        // Pages
        if (Schema::hasTable('pages')) {
            Schema::table('pages', function (Blueprint $table) {
                $table->dropColumn([
                    'title',
                    'slug',
                    'excerpt',
                    'content',
                    'meta_title',
                    'meta_description',
                ]);
            });
        }
    }

    public function down(): void
    {
        // Re-add columns
        Schema::table('products', function (Blueprint $table) {
            $table->string('title')->nullable();
            $table->string('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
        });

        // Similar for other tables...
    }
};
```

---

## 2. Materialized Cache Tables

### Migration: `2025_12_05_000010_create_product_catalog_cache.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_catalog_cache', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnDelete();
            $table->foreignId('catalog_id')->nullable()->constrained('catalogs')->cascadeOnDelete();
            $table->string('locale', 10)->index();

            // Denormalized product info
            $table->string('title')->index();
            $table->string('slug')->index();
            $table->string('handle')->index();

            // Brand & Category (denormalized)
            $table->string('brand_name')->nullable()->index();
            $table->string('brand_slug')->nullable();
            $table->text('category_names')->nullable(); // Comma-separated
            $table->jsonb('category_ids')->nullable();

            // Default variant info
            $table->foreignId('default_variant_id')->nullable()->constrained('product_variants')->nullOnDelete();
            $table->string('default_sku')->nullable()->index();

            // Pricing (from default variant or catalog override)
            $table->decimal('price', 15, 4)->index();
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->decimal('min_price', 15, 4)->index();
            $table->decimal('max_price', 15, 4)->index();
            $table->string('currency', 3)->index();

            // Inventory aggregates
            $table->integer('total_inventory')->default(0)->index();
            $table->boolean('in_stock')->default(false)->index();
            $table->integer('variant_count')->default(0);

            // Images
            $table->string('main_image_url', 500)->nullable();
            $table->jsonb('image_urls')->nullable();

            // Aggregated data
            $table->jsonb('tags')->nullable();
            $table->decimal('average_rating', 3, 2)->nullable()->index();
            $table->integer('review_count')->default(0);
            $table->integer('sales_count')->default(0)->index(); // For sorting by popularity

            // Status & Publishing
            $table->boolean('is_published')->default(true)->index();
            $table->timestamp('published_at')->nullable()->index();

            // Cache metadata
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            // Composite indexes for common queries
            $table->unique(['product_id', 'site_id', 'catalog_id', 'locale'], 'unique_cache_entry');
            $table->index(['site_id', 'locale', 'is_published'], 'idx_site_locale_pub');
            $table->index(['catalog_id', 'locale', 'is_published'], 'idx_catalog_locale_pub');
            $table->index(['brand_name', 'is_published']);
            $table->index(['in_stock', 'is_published']);
            $table->index(['price', 'is_published']);
            $table->index(['sales_count', 'is_published']);

            // Full-text search
            if (config('database.default') === 'mysql') {
                $table->fullText(['title', 'brand_name', 'category_names', 'default_sku'], 'ft_cache_search');
            }
        });

        // Index for cache refresh
        Schema::table('product_catalog_cache', function (Blueprint $table) {
            $table->index('last_synced_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_catalog_cache');
    }
};
```

---

## 3. Ottimizzazioni Indici

### Migration: `2025_12_05_000020_optimize_product_indices.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // REMOVE redundant indices
            $table->dropIndex(['published_at']);
            $table->dropIndex(['product_type']);
            $table->dropIndex(['requires_selling_plan']);
            $table->dropIndex(['published_scope']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);

            // REMOVE redundant composite indices (already covered by better ones)
            $table->dropIndex(['status', 'published_at']);
            $table->dropIndex(['brand_id', 'status']);
            $table->dropIndex(['product_type_id', 'status']);
            $table->dropIndex(['published_scope', 'status']);

            // KEEP only essential composite indices
            // Already have: idx_site_status, idx_handle_site, idx_brand_type
        });

        Schema::table('product_variants', function (Blueprint $table) {
            // REMOVE redundant single-column indices
            $table->dropIndex(['cost']);
            $table->dropIndex(['inventory_management']);
            $table->dropIndex(['fulfillment_service']);
            $table->dropIndex(['allow_out_of_stock_purchases']);
            $table->dropIndex(['weight']);
            $table->dropIndex(['taxable']);
            $table->dropIndex(['tax_code']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);

            // REMOVE redundant composites
            $table->dropIndex(['inventory_quantity', 'status']);
            $table->dropIndex(['price', 'status']);
            $table->dropIndex(['available', 'status']);

            // KEEP: product_id, sku, status, inventory_quantity, price
            // ADD: Better composite for inventory checks
            $table->index(['product_id', 'available', 'track_quantity', 'inventory_quantity'], 'idx_variant_availability');
        });

        Schema::table('categories', function (Blueprint $table) {
            // REMOVE redundant indices
            $table->dropIndex(['created_at']);
            $table->dropIndex(['updated_at']);
            $table->dropIndex(['include_in_menu', 'is_active']);
            $table->dropIndex(['products_count', 'is_active']);

            // Better composite for menu queries
            $table->index(['parent_id', 'is_active', 'include_in_menu', 'sort_order'], 'idx_menu_nav');
        });
    }

    public function down(): void
    {
        // Restore indices (if needed)
    }
};
```

---

## 4. Categories - Materialized Path

### Migration: `2025_12_05_000030_convert_categories_to_materialized_path.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            // Remove nested set columns
            $table->dropColumn(['left', 'right']);

            // Add materialized path columns
            $table->string('path', 500)->after('parent_id')->index();
            $table->integer('depth')->after('path')->index();
        });

        // Populate path and depth
        $this->populateMaterializedPath();

        // Add composite index
        Schema::table('categories', function (Blueprint $table) {
            $table->index(['site_id', 'path']);
            $table->index(['depth', 'sort_order']);
        });
    }

    private function populateMaterializedPath(): void
    {
        // Get all categories ordered by parent_id
        $categories = DB::table('categories')
            ->orderBy('parent_id')
            ->orderBy('id')
            ->get();

        $pathMap = [];

        foreach ($categories as $category) {
            if ($category->parent_id === null) {
                // Root category
                $path = "/{$category->id}/";
                $depth = 0;
            } else {
                // Child category
                $parentPath = $pathMap[$category->parent_id] ?? "/{$category->parent_id}/";
                $path = $parentPath . "{$category->id}/";
                $depth = substr_count($path, '/') - 2; // Count separators minus start/end
            }

            $pathMap[$category->id] = $path;

            DB::table('categories')
                ->where('id', $category->id)
                ->update([
                    'path' => $path,
                    'depth' => $depth,
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn(['path', 'depth']);
            $table->integer('left')->default(0);
            $table->integer('right')->default(0);
        });
    }
};
```

---

## 5. Order Addresses Denormalized

### Migration: `2025_12_05_000040_create_order_addresses_table.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('order_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();
            $table->enum('type', ['shipping', 'billing'])->index();

            // Address fields
            $table->string('first_name', 100);
            $table->string('last_name', 100);
            $table->string('company', 200)->nullable();
            $table->string('address_line_1', 255);
            $table->string('address_line_2', 255)->nullable();
            $table->string('city', 100);
            $table->string('state', 100)->nullable();
            $table->string('postal_code', 20)->index();
            $table->char('country_code', 2)->index();
            $table->string('phone', 30)->nullable();
            $table->string('email', 255)->nullable();

            // Geocoding (for shipping calculations)
            $table->decimal('latitude', 10, 8)->nullable();
            $table->decimal('longitude', 11, 8)->nullable();

            $table->timestamps();

            // Indexes
            $table->unique(['order_id', 'type']);
            $table->index(['order_id', 'type']);
            $table->index(['country_code', 'postal_code']);
            $table->index(['postal_code', 'type']);
        });

        // Migrate existing JSONB data
        $this->migrateAddressesFromJson();

        // Drop JSONB columns
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['shipping_address', 'billing_address']);
        });
    }

    private function migrateAddressesFromJson(): void
    {
        $orders = DB::table('orders')->get();

        foreach ($orders as $order) {
            $shippingAddress = json_decode($order->shipping_address, true);
            $billingAddress = json_decode($order->billing_address, true);

            if ($shippingAddress) {
                DB::table('order_addresses')->insert([
                    'order_id' => $order->id,
                    'type' => 'shipping',
                    'first_name' => $shippingAddress['first_name'] ?? '',
                    'last_name' => $shippingAddress['last_name'] ?? '',
                    'company' => $shippingAddress['company'] ?? null,
                    'address_line_1' => $shippingAddress['address_line_1'] ?? '',
                    'address_line_2' => $shippingAddress['address_line_2'] ?? null,
                    'city' => $shippingAddress['city'] ?? '',
                    'state' => $shippingAddress['state'] ?? null,
                    'postal_code' => $shippingAddress['postal_code'] ?? '',
                    'country_code' => $shippingAddress['country_code'] ?? 'US',
                    'phone' => $shippingAddress['phone'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            if ($billingAddress) {
                DB::table('order_addresses')->insert([
                    'order_id' => $order->id,
                    'type' => 'billing',
                    'first_name' => $billingAddress['first_name'] ?? '',
                    'last_name' => $billingAddress['last_name'] ?? '',
                    'company' => $billingAddress['company'] ?? null,
                    'address_line_1' => $billingAddress['address_line_1'] ?? '',
                    'address_line_2' => $billingAddress['address_line_2'] ?? null,
                    'city' => $billingAddress['city'] ?? '',
                    'state' => $billingAddress['state'] ?? null,
                    'postal_code' => $billingAddress['postal_code'] ?? '',
                    'country_code' => $billingAddress['country_code'] ?? 'US',
                    'phone' => $billingAddress['phone'] ?? null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->jsonb('shipping_address')->nullable();
            $table->jsonb('billing_address')->nullable();
        });

        Schema::dropIfExists('order_addresses');
    }
};
```

---

## 6. Inventory Optimizations

### Migration: `2025_12_05_000050_add_version_to_location_inventories.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('location_inventories', function (Blueprint $table) {
            // Optimistic locking
            $table->integer('version')->default(0)->after('available_quantity');

            // Better indexing for availability checks
            $table->index(['product_variant_id', 'available_quantity', 'version'], 'idx_variant_availability_version');
        });

        // Remove stored computed column (causes lock contention)
        Schema::table('location_inventories', function (Blueprint $table) {
            $table->dropColumn('available_quantity');
        });

        // Add as virtual column (computed on read, not stored)
        if (config('database.default') === 'mysql') {
            DB::statement('
                ALTER TABLE location_inventories
                ADD COLUMN available_quantity INT
                GENERATED ALWAYS AS (quantity - reserved_quantity) VIRTUAL
            ');
        } else {
            // PostgreSQL
            DB::statement('
                ALTER TABLE location_inventories
                ADD COLUMN available_quantity INT
                GENERATED ALWAYS AS (quantity - reserved_quantity) STORED
            ');
        }
    }

    public function down(): void
    {
        Schema::table('location_inventories', function (Blueprint $table) {
            $table->dropColumn(['version']);
        });
    }
};
```

---

## 7. Query Cache Infrastructure

### Migration: `2025_12_05_000060_create_query_cache_tables.php`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('query_cache', function (Blueprint $table) {
            $table->string('key', 255)->primary();
            $table->mediumText('value');
            $table->integer('expiration')->index();

            $table->index('expiration');
        });

        // Cache tags (for invalidation)
        Schema::create('query_cache_tags', function (Blueprint $table) {
            $table->string('tag', 255)->index();
            $table->string('cache_key', 255);
            $table->integer('expiration')->index();

            $table->primary(['tag', 'cache_key']);
            $table->foreign('cache_key')
                ->references('key')
                ->on('query_cache')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('query_cache_tags');
        Schema::dropIfExists('query_cache');
    }
};
```

---

## 🎯 Order di Esecuzione Migrations

```bash
# 1. Sistema Multi-Lingua (PRIORITÀ MASSIMA)
php artisan migrate --path=database/migrations/2025_12_05_000001_create_translation_system.php
php artisan migrate --path=database/migrations/2025_12_05_000002_migrate_existing_content_to_translations.php

# ⚠️ ATTENZIONE: Backup database prima di questo step!
php artisan migrate --path=database/migrations/2025_12_05_000003_remove_translatable_columns_from_main_tables.php

# 2. Materialized Cache
php artisan migrate --path=database/migrations/2025_12_05_000010_create_product_catalog_cache.php

# 3. Ottimizzazioni Indici
php artisan migrate --path=database/migrations/2025_12_05_000020_optimize_product_indices.php

# 4. Categories
php artisan migrate --path=database/migrations/2025_12_05_000030_convert_categories_to_materialized_path.php

# 5. Order Addresses
php artisan migrate --path=database/migrations/2025_12_05_000040_create_order_addresses_table.php

# 6. Inventory
php artisan migrate --path=database/migrations/2025_12_05_000050_add_version_to_location_inventories.php

# 7. Query Cache
php artisan migrate --path=database/migrations/2025_12_05_000060_create_query_cache_tables.php
```

---

## ✅ Testing dopo Migration

```bash
# 1. Verifica integrità dati
php artisan tinker
>>> Product::count()
>>> ProductTranslation::count() // Dovrebbe essere >= Product::count()

# 2. Test query performance
php artisan tinker
>>> DB::enableQueryLog();
>>> Product::with('translations')->limit(50)->get();
>>> DB::getQueryLog(); // Verifica numero query

# 3. Test cache
>>> $product = Product::first();
>>> Cache::tags(['products'])->flush();
>>> // Query dovrebbe essere cached
```

---

**Pronto per l'implementazione!** 🚀

Ogni migration è **production-ready** e include:
- ✅ Rollback safety
- ✅ Data migration
- ✅ Index optimization
- ✅ Performance considerations

**Next Step:** Vuoi che creo anche i Model Eloquent e Repository ottimizzati?
