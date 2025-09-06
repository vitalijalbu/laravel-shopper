<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Adds a `data` JSONB field to all custom models for dynamic field management.
     * This implements a Statamic-like system where custom fields are defined via JSON schemas
     * and stored in the `data` column, allowing flexible content management without 
     * database schema changes.
     */
    public function up(): void
    {
        // Core business entities
        $tables = [
            'products',
            'product_variants', 
            'customers',
            'orders',
            'order_lines',
            'brands',
            'product_types',
            'collections',
            'collection_entries',
            'customer_addresses',
            'addresses',
            'pages',
            'menus',
            'menu_items',
            'carts',
            'cart_lines',
            'wishlists',
            'wishlist_items',
            'favorites',
            'payment_gateways',
            'payment_methods',
            'transactions',
            'shipping_zones',
            'shipping_methods',
            'tax_rates',
            'fidelity_cards',
            'fidelity_transactions',
            'suppliers',
            'purchase_orders',
            'purchase_order_items',
            'apps',
            'app_installations',
            'app_reviews',
            'stock_notifications',
            'customer_groups'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    // Add JSONB field for custom data
                    // This will store schema-defined custom fields as JSON
                    $table->jsonb('data')->nullable()->comment('Custom fields data based on JSON schema');
                    
                    // Add index for better query performance on data field
                    if (config('database.default') === 'pgsql') {
                        // PostgreSQL specific JSONB index
                        $table->index('data', null, 'gin');
                    } else {
                        // MySQL JSON index (MySQL 5.7+)
                        $table->index('data');
                    }
                });
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $tables = [
            'products',
            'product_variants', 
            'customers',
            'orders',
            'order_lines',
            'brands',
            'product_types',
            'collections',
            'collection_entries',
            'customer_addresses',
            'addresses',
            'pages',
            'menus',
            'menu_items',
            'carts',
            'cart_lines',
            'wishlists',
            'wishlist_items',
            'favorites',
            'payment_gateways',
            'payment_methods',
            'transactions',
            'shipping_zones',
            'shipping_methods',
            'tax_rates',
            'fidelity_cards',
            'fidelity_transactions',
            'suppliers',
            'purchase_orders',
            'purchase_order_items',
            'apps',
            'app_installations',
            'app_reviews',
            'stock_notifications',
            'customer_groups'
        ];

        foreach ($tables as $table) {
            if (Schema::hasTable($table)) {
                Schema::table($table, function (Blueprint $table) {
                    $table->dropColumn('data');
                });
            }
        }
    }
};
