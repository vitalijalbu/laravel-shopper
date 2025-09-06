<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->unsignedBigInteger('site_id')->nullable()->index();

            // Variant Identity
            $table->string('title')->index(); // Default Title, Red / Large, etc.
            $table->string('sku')->index();
            $table->string('barcode')->nullable()->index(); // UPC, EAN, etc.

            // Option Values (Shopify style) - null per varianti di default
            $table->string('option1')->nullable()->index(); // e.g., "Red"
            $table->string('option2')->nullable()->index(); // e.g., "Large"
            $table->string('option3')->nullable()->index(); // e.g., "Cotton"

            // PRICING - I dati principali stanno qui nelle varianti
            $table->decimal('price', 15, 2)->index();
            $table->decimal('compare_at_price', 15, 2)->nullable(); // Compare price
            $table->decimal('cost', 15, 2)->nullable(); // Cost price

            // INVENTORY - I dati principali stanno qui nelle varianti
            $table->integer('inventory_quantity')->default(0)->index();
            $table->boolean('track_quantity')->default(true)->index();
            $table->string('inventory_management')->default('shopify')->index(); // shopify, not_managed, fulfillment_service
            $table->string('inventory_policy')->default('deny')->index(); // deny, continue
            $table->string('fulfillment_service')->default('manual');
            $table->integer('inventory_quantity_adjustment')->default(0);
            $table->boolean('allow_out_of_stock_purchases')->default(false);

            // PHYSICAL PROPERTIES - I dati principali stanno qui nelle varianti
            $table->decimal('weight', 8, 2)->nullable();
            $table->string('weight_unit', 10)->default('kg'); // kg, g, lb, oz
            $table->jsonb('dimensions')->nullable(); // length, width, height

            // SHIPPING & TAX - I dati principali stanno qui nelle varianti
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('taxable')->default(true);
            $table->string('tax_code')->nullable();

            // Display and Ordering
            $table->integer('position')->default(1)->index(); // Display order

            // Status della variante specifica
            $table->string('status')->default('active')->index();
            $table->boolean('available')->default(true)->index(); // Available for sale

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Custom fields data (JSON schema-based)
            $table->jsonb('data')->nullable()->comment('Custom fields data based on JSON schema');

            // Indexes
            $table->unique(['sku', 'site_id']);
            $table->index(['product_id', 'position']);
            $table->index(['site_id', 'status']);
            $table->index(['option1', 'option2', 'option3']);
            $table->index(['inventory_quantity', 'inventory_policy']);
            $table->index(['price', 'compare_at_price']);
            $table->index(['track_quantity', 'available']);
            $table->index(['weight_unit', 'requires_shipping']);
            
            // Additional filter indexes
            $table->index('cost');
            $table->index('inventory_management');
            $table->index('fulfillment_service');
            $table->index('allow_out_of_stock_purchases');
            $table->index('weight');
            $table->index('taxable');
            $table->index('tax_code');
            $table->index('created_at');
            $table->index('updated_at');
            
            // Composite indexes for common filter combinations
            $table->index(['product_id', 'status']);
            $table->index(['product_id', 'available']);
            $table->index(['inventory_quantity', 'status']);
            $table->index(['price', 'status']);
            $table->index(['available', 'status']);
            $table->index(['track_quantity', 'inventory_quantity']);
            
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
