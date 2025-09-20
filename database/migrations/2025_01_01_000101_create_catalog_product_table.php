<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('catalog_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('catalog_id')->constrained('catalogs')->cascadeOnDelete();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            
            // Product inclusion/exclusion
            $table->boolean('is_included')->default(true)->index();
            
            // Fixed pricing (overrides catalog-level adjustments)
            $table->decimal('fixed_price', 10, 4)->nullable();
            $table->decimal('compare_at_price', 10, 4)->nullable();
            
            // Quantity rules for B2B
            $table->integer('quantity_increment')->nullable()->comment('Items must be purchased in multiples of this quantity');
            $table->integer('minimum_order_quantity')->nullable()->comment('Minimum quantity that can be purchased');
            $table->integer('maximum_order_quantity')->nullable()->comment('Maximum quantity that can be purchased');
            
            // Volume pricing (quantity breaks) - stored as JSON
            $table->jsonb('quantity_breaks')->nullable()->comment('Array of quantity break rules: [{"quantity": 10, "price": 9.99}, ...]');
            
            // Publishing control at product level
            $table->boolean('is_published')->default(true)->index();
            
            // Timestamps
            $table->timestamps();
            
            // Custom fields data (JSON schema-based)
            $table->jsonb('data')->nullable()->comment('Custom fields data based on JSON schema');
            
            // Indexes
            $table->unique(['catalog_id', 'product_id']);
            $table->index(['catalog_id', 'is_included', 'is_published']);
            $table->index(['product_id', 'is_included']);
            $table->index(['fixed_price']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product');
    }
};