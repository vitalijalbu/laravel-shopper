<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Metafields (Shopify-style custom fields)
        Schema::create('metafields', function (Blueprint $table) {
            $table->id();

            // Owner (polymorphic relationship)
            $table->string('owner_resource'); // product, product_variant, customer, order, etc.
            $table->unsignedBigInteger('owner_id');

            // Metafield Definition
            $table->string('namespace')->index(); // e.g., 'custom', 'seo', 'technical'
            $table->string('key')->index(); // e.g., 'material', 'care_instructions'
            $table->text('value'); // The actual value
            $table->string('type')->default('single_line_text_field'); // text, number, date, etc.

            // Display and Behavior
            $table->string('description')->nullable();
            $table->boolean('show_in_storefront')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['owner_resource', 'owner_id']);
            $table->index(['namespace', 'key']);
            $table->unique(['owner_resource', 'owner_id', 'namespace', 'key'], 'metafields_unique');
        });

        // Product Tags (Shopify-style)
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('usage_count');
        });

        // Product Tag Pivot
        Schema::create('product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_tag_id')->constrained('product_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'product_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('metafields');
    }
};
