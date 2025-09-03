<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Fulfillment tracking (Shopify-style)
        Schema::create('fulfillments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->string('fulfillment_number')->unique(); // FUL-XXXXXX
            $table->enum('status', ['pending', 'in_progress', 'shipped', 'delivered', 'failed', 'cancelled'])->default('pending')->index();
            $table->string('tracking_number')->nullable()->index();
            $table->string('tracking_company')->nullable();
            $table->string('tracking_url')->nullable();
            $table->jsonb('shipping_address');
            $table->decimal('shipping_cost', 15, 2)->default(0);
            $table->string('shipping_method')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable()->index();
            $table->timestamp('estimated_delivery')->nullable();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->jsonb('metadata')->nullable(); // Carrier-specific data
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['location_id', 'status']);
            $table->index(['shipped_at', 'status']);
            $table->index(['tracking_number']);
        });

        // Line items per fulfillment
        Schema::create('fulfillment_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fulfillment_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained('order_lines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->timestamps();

            $table->unique(['fulfillment_id', 'order_line_id']);
            $table->index(['order_line_id']);
        });

        // Returns & refunds tracking
        Schema::create('returns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('return_number')->unique(); // RET-XXXXXX
            $table->enum('status', ['requested', 'approved', 'rejected', 'in_transit', 'received', 'processed', 'refunded'])->default('requested')->index();
            $table->enum('reason', ['defective', 'wrong_item', 'not_as_described', 'changed_mind', 'damaged', 'other'])->index();
            $table->text('customer_notes')->nullable();
            $table->text('admin_notes')->nullable();
            $table->decimal('refund_amount', 15, 2)->default(0);
            $table->boolean('restockable')->default(true);
            $table->string('tracking_number')->nullable(); // Return shipping
            $table->timestamp('requested_at')->index();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index(['order_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['reason', 'status']);
            $table->index(['requested_at', 'status']);
        });

        // Return line items
        Schema::create('return_line_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('return_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_line_id')->constrained('order_lines')->cascadeOnDelete();
            $table->integer('quantity');
            $table->decimal('refund_amount', 15, 2);
            $table->enum('condition', ['new', 'opened', 'damaged', 'defective'])->default('new');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['return_id', 'order_line_id']);
            $table->index(['order_line_id']);
        });

        // Shipping zones
        Schema::create('shipping_zones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->jsonb('countries'); // Array of country codes
            $table->jsonb('states')->nullable(); // Array of state codes
            $table->jsonb('postal_codes')->nullable(); // Specific postal codes
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['site_id', 'is_active']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Enhanced shipping methods
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('calculation_type', ['flat_rate', 'weight_based', 'price_based', 'item_count', 'calculated'])->default('flat_rate');
            $table->decimal('base_cost', 15, 2)->default(0);
            $table->jsonb('rates'); // Rate structure based on calculation type
            $table->decimal('min_order_amount', 15, 2)->nullable();
            $table->decimal('max_order_amount', 15, 2)->nullable();
            $table->decimal('max_weight', 8, 2)->nullable();
            $table->string('carrier')->nullable(); // UPS, FedEx, DHL, etc.
            $table->string('service_code')->nullable(); // Carrier service code
            $table->integer('min_delivery_days')->nullable();
            $table->integer('max_delivery_days')->nullable();
            $table->boolean('requires_signature')->default(false);
            $table->boolean('is_active')->default(true)->index();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['shipping_zone_id', 'is_active']);
            $table->index(['carrier', 'service_code']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
        Schema::dropIfExists('shipping_zones');
        Schema::dropIfExists('return_line_items');
        Schema::dropIfExists('returns');
        Schema::dropIfExists('fulfillment_line_items');
        Schema::dropIfExists('fulfillments');
    }
};
