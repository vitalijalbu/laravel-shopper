<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Inventory locations per multi-location stock management
        Schema::create('inventory_locations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable();
            $table->string('name');
            $table->string('code', 50);
            $table->jsonb('address')->nullable(); // Full address object
            $table->string('contact_email')->nullable();
            $table->string('contact_phone', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(0); // For allocation priority
            $table->enum('type', ['warehouse', 'store', 'dropship', 'vendor'])->default('warehouse');
            $table->jsonb('settings')->nullable(); // Custom settings per location
            $table->timestamps();

            $table->unique(['code', 'site_id']);
            $table->index(['site_id', 'is_active', 'priority']);
            $table->index(['type', 'is_active']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Detailed inventory tracking per location
        Schema::create('inventory_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('location_id')->constrained('inventory_locations')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('available')->default(0); // Available for sale
            $table->integer('committed')->default(0); // Reserved for orders
            $table->integer('on_hand')->default(0); // Physical inventory
            $table->integer('reserved')->default(0); // Hold/QC/damaged
            $table->integer('incoming')->default(0); // Expected from suppliers
            $table->decimal('cost_price', 15, 2)->nullable(); // Location-specific cost
            $table->timestamp('last_counted_at')->nullable(); // Last physical count
            $table->jsonb('metadata')->nullable(); // Bin location, notes, etc.
            $table->timestamps();

            $table->unique(['location_id', 'product_id', 'variant_id']);
            $table->index(['product_id', 'available']);
            $table->index(['location_id', 'available']);
        });

        // Inventory movements log for audit trail
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inventory_item_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // adjustment, sale, restock, transfer, damage, etc.
            $table->integer('quantity_delta'); // Positive or negative change
            $table->integer('quantity_before');
            $table->integer('quantity_after');
            $table->string('reference')->nullable()->index(); // Order ID, PO ID, etc.
            $table->string('reason')->nullable();
            $table->unsignedBigInteger('user_id')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['inventory_item_id', 'created_at']);
            $table->index(['type', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
        Schema::dropIfExists('inventory_items');
        Schema::dropIfExists('inventory_locations');
    }
};
