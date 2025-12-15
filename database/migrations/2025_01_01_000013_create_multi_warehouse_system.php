<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Multi-Warehouse Inventory System
 *
 * Enterprise-grade inventory management with multiple warehouses.
 * Inspired by PrestaShop Advanced Stock Management and Shopware's warehouse system.
 *
 * Features:
 * - Multiple warehouses/locations per site
 * - Stock levels tracked per variant per warehouse
 * - Stock movements for complete audit trail
 * - Stock reservations (hold inventory for carts/orders)
 * - Automated reorder points
 * - Cost price tracking per warehouse
 *
 * For 5M products, this provides:
 * - Distributed inventory
 * - Fulfillment optimization (ship from nearest warehouse)
 * - Accurate availability checks
 */
return new class extends Migration
{
    public function up(): void
    {
        // Warehouses / Locations
        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();

            $table->string('name');
            $table->string('code', 20)
                ->unique()
                ->comment('Short code for internal use (e.g., WH-NYC, WH-LA)');

            $table->enum('type', ['warehouse', 'store', 'dropship', 'supplier'])
                ->default('warehouse')
                ->comment('Type of location');

            $table->foreignId('address_id')
                ->nullable()
                ->constrained('addresses')
                ->nullOnDelete();

            $table->boolean('is_active')->default(true);
            $table->boolean('is_primary')
                ->default(false)
                ->comment('Default warehouse for new products');

            $table->integer('priority')
                ->default(0)
                ->comment('Fulfillment priority (higher = preferred)');

            $table->json('settings')
                ->nullable()
                ->comment('Custom settings (operating hours, shipping zones, etc.)');

            $table->timestamps();
            $table->softDeletes();

            $table->index(['is_active', 'priority']);
            $table->index('type');
        });

        // Stock Levels per Variant per Warehouse
        Schema::create('stock_levels', function (Blueprint $table) {
            $table->id();

            $table->foreignId('product_variant_id')
                ->constrained('product_variants')
                ->cascadeOnDelete();

            $table->foreignId('warehouse_id')
                ->constrained('warehouses')
                ->cascadeOnDelete();

            // Stock quantities
            $table->integer('quantity_on_hand')
                ->default(0)
                ->comment('Physical stock in warehouse');

            $table->integer('quantity_reserved')
                ->default(0)
                ->comment('Stock reserved for orders/carts');

            $table->integer('quantity_incoming')
                ->default(0)
                ->comment('Stock on purchase orders');

            $table->integer('quantity_damaged')
                ->default(0)
                ->comment('Damaged/unsellable stock');

            // Computed: quantity_available = on_hand - reserved
            // Can be used with virtual columns or accessors

            // Reorder management
            $table->integer('reorder_point')
                ->nullable()
                ->comment('Trigger reorder when stock falls below this');

            $table->integer('reorder_quantity')
                ->nullable()
                ->comment('How many to order when restocking');

            // Cost tracking
            $table->decimal('cost_price', 10, 2)
                ->nullable()
                ->comment('Unit cost at this warehouse');

            $table->timestamps();

            // Constraints
            $table->unique(['product_variant_id', 'warehouse_id']);

            // Indexes for queries
            $table->index(['warehouse_id', 'quantity_on_hand']);
            $table->index(['product_variant_id', 'quantity_on_hand']);
            $table->index(['quantity_on_hand', 'reorder_point']);
        });

        // Stock Movements (audit trail)
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_level_id')
                ->constrained('stock_levels')
                ->cascadeOnDelete();

            $table->integer('quantity_delta')
                ->comment('Change in stock (positive = increase, negative = decrease)');

            $table->enum('type', [
                'purchase',        // Stock received from supplier
                'sale',            // Stock sold to customer
                'return',          // Customer return
                'adjustment',      // Manual inventory adjustment
                'transfer_in',     // Received from another warehouse
                'transfer_out',    // Sent to another warehouse
                'damaged',         // Marked as damaged
                'found',           // Stock count found extra
                'lost',            // Stock count found missing
                'production',      // Manufactured/assembled
                'disassembly',     // Bundle broken down
            ]);

            // References (nullable, depends on movement type)
            $table->foreignId('order_id')
                ->nullable()
                ->constrained('orders')
                ->cascadeOnDelete();

            $table->foreignId('purchase_order_id')
                ->nullable()
                ->constrained('purchase_orders')
                ->cascadeOnDelete();

            $table->foreignId('from_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->cascadeOnDelete()
                ->comment('Source warehouse for transfers');

            $table->foreignId('to_warehouse_id')
                ->nullable()
                ->constrained('warehouses')
                ->cascadeOnDelete()
                ->comment('Destination warehouse for transfers');

            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->comment('Who made the change');

            $table->text('notes')->nullable();

            $table->timestamps();

            // Indexes for audit queries
            $table->index(['stock_level_id', 'created_at']);
            $table->index('type');
            $table->index(['type', 'created_at']);
        });

        // Stock Reservations (hold inventory for carts/orders)
        Schema::create('stock_reservations', function (Blueprint $table) {
            $table->id();

            $table->foreignId('stock_level_id')
                ->constrained('stock_levels')
                ->cascadeOnDelete();

            $table->integer('quantity');

            // Polymorphic: can be Cart or Order
            $table->morphs('reservable');

            $table->timestamp('expires_at')
                ->nullable()
                ->comment('Auto-release after this time (e.g., abandoned carts)');

            $table->timestamps();

            // Indexes
            $table->index(['stock_level_id', 'expires_at']);
            $table->index(['reservable_type', 'reservable_id']);
        });

        // Add foreign keys to fulfillments/returns (now that warehouses exist)
        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->nullOnDelete();
        });

        Schema::table('order_returns', function (Blueprint $table) {
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_returns', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });

        Schema::table('order_fulfillments', function (Blueprint $table) {
            $table->dropForeign(['warehouse_id']);
        });

        Schema::dropIfExists('stock_reservations');
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('stock_levels');
        Schema::dropIfExists('warehouses');
    }
};
