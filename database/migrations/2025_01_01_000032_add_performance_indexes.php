<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Add GIN indexes for PostgreSQL JSONB performance  
        if (DB::getDriverName() === 'pgsql') {
            $this->addGinIndexes();
        }
        
        // Add additional performance indexes for all databases
        $this->addPerformanceIndexes();
    }

    public function down(): void
    {
        // Remove indexes
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'updated_at']);
            $table->dropIndex(['price', 'is_enabled']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'updated_at']);
            $table->dropIndex(['total', 'currency_id']);
        });

        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['created_at', 'updated_at']);
        });
    }

    private function addGinIndexes(): void
    {
        // Products JSONB indexes
        DB::statement('CREATE INDEX IF NOT EXISTS products_dimensions_gin_idx ON products USING gin (dimensions)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_seo_gin_idx ON products USING gin (seo)');
        DB::statement('CREATE INDEX IF NOT EXISTS products_meta_gin_idx ON products USING gin (meta)');

        // Orders JSONB indexes
        DB::statement('CREATE INDEX IF NOT EXISTS orders_customer_details_gin_idx ON orders USING gin (customer_details)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_shipping_address_gin_idx ON orders USING gin (shipping_address)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_billing_address_gin_idx ON orders USING gin (billing_address)');
        DB::statement('CREATE INDEX IF NOT EXISTS orders_payment_details_gin_idx ON orders USING gin (payment_details)');

        // Transactions JSONB indexes  
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_gateway_data_gin_idx ON transactions USING gin (gateway_data)');
        DB::statement('CREATE INDEX IF NOT EXISTS transactions_metadata_gin_idx ON transactions USING gin (metadata)');

        // Channels JSONB indexes
        DB::statement('CREATE INDEX IF NOT EXISTS channels_locales_gin_idx ON channels USING gin (locales)');
        DB::statement('CREATE INDEX IF NOT EXISTS channels_currencies_gin_idx ON channels USING gin (currencies)');

        // Sites JSONB indexes
        DB::statement('CREATE INDEX IF NOT EXISTS sites_attributes_gin_idx ON sites USING gin (attributes)');
    }

    private function addPerformanceIndexes(): void
    {
        // Products performance indexes
        Schema::table('products', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
            $table->index(['price', 'is_enabled']);
            $table->index(['stock_status', 'stock_quantity']);
        });

        // Orders performance indexes
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
            $table->index(['total', 'currency_id']);
            $table->index(['shipped_at', 'delivered_at']);
        });

        // Customers performance indexes
        Schema::table('customers', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
            $table->index(['last_login_at', 'is_enabled']);
        });

        // Transactions performance indexes
        Schema::table('transactions', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
            $table->index(['processed_at', 'status']);
        });

        // Sites performance indexes
        Schema::table('sites', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
        });

        // Channels performance indexes  
        Schema::table('channels', function (Blueprint $table) {
            $table->index(['created_at', 'updated_at']);
        });
    }
};
