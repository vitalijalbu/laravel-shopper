<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // ====== PRODUCTS TABLE OPTIMIZATIONS ======
        Schema::table('products', function (Blueprint $table) {
            // Search optimization
            $table->index(['site_id', 'is_enabled', 'status', 'name'], 'idx_products_search');
            
            // Featured products optimization
            $table->index(['site_id', 'is_featured', 'is_enabled', 'published_at'], 'idx_products_featured');
            
            // Price range filtering
            $table->index(['site_id', 'price', 'is_enabled', 'status'], 'idx_products_price_range');
            
            // Stock status filtering
            $table->index(['site_id', 'stock_status', 'track_quantity'], 'idx_products_stock');
            
            // Category filtering optimization (if not exists)
            if (!Schema::hasColumn('products', 'primary_category_id')) {
                $table->foreignId('primary_category_id')->nullable()->after('product_type_id')->constrained('categories')->nullOnDelete();
                $table->index(['primary_category_id', 'is_enabled', 'status'], 'idx_products_category');
            }
        });

        // Add fulltext search index for products
        DB::statement('ALTER TABLE products ADD FULLTEXT ft_products_search (name, description, short_description)');

        // ====== ORDERS TABLE OPTIMIZATIONS ======
        Schema::table('orders', function (Blueprint $table) {
            // Customer order history optimization
            $table->index(['customer_id', 'created_at', 'status'], 'idx_orders_customer_history');
            
            // Revenue analysis optimization
            $table->index(['site_id', 'created_at', 'total', 'status'], 'idx_orders_revenue_analysis');
            
            // Fulfillment optimization
            $table->index(['fulfillment_status', 'created_at'], 'idx_orders_fulfillment');
            
            // Payment tracking
            $table->index(['payment_status', 'payment_method', 'created_at'], 'idx_orders_payment');
        });

        // ====== CUSTOMERS TABLE OPTIMIZATIONS ======
        Schema::table('customers', function (Blueprint $table) {
            // Customer lifetime value (if column doesn't exist)
            if (!Schema::hasColumn('customers', 'lifetime_value')) {
                $table->decimal('lifetime_value', 15, 2)->default(0)->after('last_login_ip');
                $table->integer('orders_count')->default(0)->after('lifetime_value');
                $table->timestamp('first_order_at')->nullable()->after('orders_count');
                $table->timestamp('last_order_at')->nullable()->after('first_order_at');
            }
            
            // Customer value analysis
            $table->index(['site_id', 'lifetime_value', 'orders_count'], 'idx_customers_value');
            
            // Activity tracking
            $table->index(['last_login_at', 'is_enabled'], 'idx_customers_activity');
            
            // Registration tracking
            $table->index(['created_at', 'email_verified_at'], 'idx_customers_registration');
        });

        // ====== PRODUCT VARIANTS OPTIMIZATIONS ======
        Schema::table('product_variants', function (Blueprint $table) {
            // Variant search optimization
            $table->index(['product_id', 'is_enabled', 'stock_status', 'price'], 'idx_variants_search');
            
            // SKU uniqueness (if not exists)
            if (!$table->hasIndex(['sku', 'product_id'])) {
                $table->unique(['sku'], 'idx_variants_sku_unique');
            }
        });

        // ====== CART OPTIMIZATIONS ======
        Schema::table('carts', function (Blueprint $table) {
            // Active carts optimization
            $table->index(['customer_id', 'updated_at', 'session_id'], 'idx_carts_active');
            
            // Cart abandonment tracking
            $table->index(['updated_at', 'customer_id'], 'idx_carts_abandonment');
        });

        // ====== TRANSACTIONS OPTIMIZATIONS ======
        if (Schema::hasTable('transactions')) {
            Schema::table('transactions', function (Blueprint $table) {
                // Financial reporting
                $table->index(['processed_at', 'status', 'type', 'amount'], 'idx_transactions_reporting');
                
                // Gateway performance
                $table->index(['gateway', 'status', 'processed_at'], 'idx_transactions_gateway');
                
                // Customer transaction history
                $table->index(['customer_id', 'processed_at', 'status'], 'idx_transactions_customer');
            });
        }

        // ====== CATEGORIES OPTIMIZATIONS ======
        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                // Category hierarchy (if not exists)
                if (!Schema::hasColumn('categories', 'level')) {
                    $table->integer('level')->default(0)->after('parent_id');
                    $table->string('path', 500)->nullable()->after('level'); // /parent/child/grandchild
                }
                
                // Category navigation optimization
                $table->index(['parent_id', 'is_enabled', 'sort_order'], 'idx_categories_navigation');
                
                // Nested set optimization
                $table->index(['level', 'path'], 'idx_categories_hierarchy');
            });
        }

        // ====== ANALYTICS TABLE ======
        // Create analytics summary table for faster reporting
        Schema::create('analytics_daily_summary', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->date('date')->index();
            $table->integer('visitors')->default(0);
            $table->integer('pageviews')->default(0);
            $table->integer('sessions')->default(0);
            $table->decimal('bounce_rate', 5, 4)->default(0);
            $table->integer('orders_count')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->decimal('avg_order_value', 15, 2)->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->integer('new_customers')->default(0);
            $table->integer('returning_customers')->default(0);
            $table->jsonb('traffic_sources')->nullable();
            $table->jsonb('top_products')->nullable();
            $table->jsonb('top_categories')->nullable();
            $table->timestamps();

            $table->unique(['site_id', 'date']);
            $table->index(['date', 'revenue']);
            $table->index(['site_id', 'date', 'conversion_rate']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_summary');

        // Remove added indexes (MySQL doesn't support IF EXISTS for indexes in migrations)
        // These would need to be handled manually or with raw SQL
        
        // Remove added columns
        Schema::table('customers', function (Blueprint $table) {
            if (Schema::hasColumn('customers', 'lifetime_value')) {
                $table->dropColumn(['lifetime_value', 'orders_count', 'first_order_at', 'last_order_at']);
            }
        });

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'primary_category_id')) {
                $table->dropForeign(['primary_category_id']);
                $table->dropColumn('primary_category_id');
            }
        });

        if (Schema::hasTable('categories')) {
            Schema::table('categories', function (Blueprint $table) {
                if (Schema::hasColumn('categories', 'level')) {
                    $table->dropColumn(['level', 'path']);
                }
            });
        }

        // Remove fulltext index
        DB::statement('ALTER TABLE products DROP INDEX ft_products_search');
    }
};
