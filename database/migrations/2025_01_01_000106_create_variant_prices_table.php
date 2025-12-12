<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create database tables for variant pricing, price lists, and price list items with their columns, constraints, and indexes.
     *
     * Creates:
     * - variant_prices: stores contextual and scheduled prices for product variants (includes currency, price/cost fields, tiering, schedule, priority, jsonb data), with foreign keys that cascade on delete and multiple indexes for context, schedule and resolution (including idx_price_resolution).
     * - price_lists: defines bulk price list metadata and adjustment rules (adjustment_type/value, schedule, is_active, priority), includes soft deletes and indexes for active/priority and site/channel/customer_group.
     * - price_list_items: maps price_list entries to product variants with pricing and min_quantity; enforces a unique composite constraint named `price_list_items_uq` on (price_list_id, product_variant_id, min_quantity) and an index on product_variant_id.
     *
     * @return void
     */
    public function up(): void
    {
        // Advanced Pricing Table - Hierarchical with fallbacks
        Schema::create('variant_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            // Context: What price applies to
            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->cascadeOnDelete();
            $table->foreignId('catalog_id')->nullable()->constrained('catalogs')->cascadeOnDelete();
            $table->string('currency', 3);

            // Pricing
            $table->decimal('price', 15, 4);
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->decimal('cost', 15, 4)->nullable();

            // Tier Pricing (B2B wholesale)
            $table->integer('min_quantity')->default(1);
            $table->integer('max_quantity')->nullable();

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            // Priority (higher = takes precedence)
            $table->integer('priority')->default(0)->comment('Higher priority prices override lower ones');

            $table->timestamps();
            $table->jsonb('data')->nullable();

            // Indexes for fast lookups
            $table->index(['product_variant_id', 'site_id', 'customer_group_id', 'min_quantity'], 'idx_variant_context');
            $table->index(['product_variant_id', 'currency', 'starts_at', 'ends_at'], 'idx_variant_schedule');
            $table->index(['priority', 'starts_at', 'ends_at']);
            $table->index(['catalog_id', 'currency']);

            // Composite for most common query
            $table->index([
                'product_variant_id',
                'site_id',
                'channel_id',
                'customer_group_id',
                'currency',
                'priority',
            ], 'idx_price_resolution');
        });

        // Price Lists (for bulk import/export)
        Schema::create('price_lists', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();

            $table->foreignId('site_id')->nullable()->constrained('sites')->cascadeOnDelete();
            $table->foreignId('channel_id')->nullable()->constrained('channels')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained('customer_groups')->cascadeOnDelete();
            $table->string('currency', 3);

            // Adjustment Rules
            $table->enum('adjustment_type', ['percentage', 'fixed'])->nullable();
            $table->decimal('adjustment_value', 10, 4)->nullable();

            // Scheduling
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('ends_at')->nullable();

            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0);

            $table->timestamps();
            $table->softDeletes();
            $table->jsonb('data')->nullable();

            $table->index(['is_active', 'priority']);
            $table->index(['site_id', 'channel_id', 'customer_group_id']);
        });

        // Price List Items
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->foreignId('product_variant_id')->constrained('product_variants')->cascadeOnDelete();

            $table->decimal('price', 15, 4);
            $table->decimal('compare_at_price', 15, 4)->nullable();
            $table->integer('min_quantity')->default(1);

            $table->timestamps();

            $table->unique(['price_list_id', 'product_variant_id', 'min_quantity'], 'price_list_items_uq');
            $table->index(['product_variant_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('price_list_items');
        Schema::dropIfExists('price_lists');
        Schema::dropIfExists('variant_prices');
    }
};