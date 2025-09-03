<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Flexible metafields system (Shopify-inspired)
        Schema::create('product_metafields', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('namespace', 100)->index(); // e.g., 'custom', 'seo', 'shipping'
            $table->string('key', 100)->index(); // e.g., 'material', 'care_instructions'
            $table->longText('value'); // Store any type of data
            $table->enum('value_type', ['string', 'integer', 'decimal', 'boolean', 'json', 'date', 'url', 'email'])->default('string');
            $table->text('description')->nullable();
            $table->boolean('is_public')->default(false)->index(); // Show in storefront
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'namespace', 'key']);
            $table->index(['namespace', 'key']);
            $table->index(['is_public', 'namespace']);
        });

        // Bundle products for cross-selling
        Schema::create('product_bundles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('child_product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('child_variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->integer('quantity')->default(1);
            $table->decimal('discount_percentage', 5, 2)->default(0); // Bundle discount
            $table->decimal('fixed_price', 15, 2)->nullable(); // Or fixed price instead
            $table->boolean('is_required')->default(false); // Must include in bundle
            $table->boolean('is_optional')->default(true); // Customer can choose
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['parent_product_id', 'child_product_id', 'child_variant_id']);
            $table->index(['parent_product_id', 'sort_order']);
            $table->index(['child_product_id']);
        });

        // Related products for recommendations
        Schema::create('product_relations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('related_product_id')->constrained('products')->cascadeOnDelete();
            $table->enum('relation_type', ['upsell', 'cross_sell', 'related', 'alternative', 'accessory'])->index();
            $table->decimal('relevance_score', 5, 4)->default(1.0); // AI scoring
            $table->integer('sort_order')->default(0);
            $table->boolean('is_automatic')->default(false); // AI-generated vs manual
            $table->timestamps();

            $table->unique(['product_id', 'related_product_id', 'relation_type']);
            $table->index(['product_id', 'relation_type', 'relevance_score']);
            $table->index(['related_product_id']);
        });

        // Product attributes (size, color, material, etc.)
        Schema::create('product_attributes', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index(); // Color, Size, Material
            $table->string('slug')->unique();
            $table->enum('type', ['text', 'number', 'boolean', 'select', 'multiselect', 'color', 'image'])->default('text');
            $table->jsonb('options')->nullable(); // For select/multiselect types
            $table->boolean('is_required')->default(false);
            $table->boolean('is_variant_option')->default(false); // Creates variants
            $table->boolean('is_filterable')->default(true); // Show in filters
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_filterable']);
            $table->index(['is_variant_option', 'sort_order']);
        });

        // Product attribute values
        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('product_attributes')->cascadeOnDelete();
            $table->text('value'); // The actual value
            $table->jsonb('metadata')->nullable(); // Color codes, image URLs, etc.
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
            $table->index(['attribute_id', 'value(100)']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('product_attributes');
        Schema::dropIfExists('product_relations');
        Schema::dropIfExists('product_bundles');
        Schema::dropIfExists('product_metafields');
    }
};
