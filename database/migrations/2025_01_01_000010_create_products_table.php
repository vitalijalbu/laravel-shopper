<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('title')->index();
            $table->string('slug');
            $table->string('handle')->nullable()->index();
            $table->text('excerpt')->nullable();
            $table->text('description')->nullable();

            // Product Classification (applies to all variants)
            $table->string('product_type')->default('physical')->index();
            $table->foreignId('brand_id')->nullable()->constrained('brands')->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained('product_types')->nullOnDelete();

            // Product Options (Color, Size, Material, etc.)
            $table->jsonb('options')->nullable(); // [{"name": "Color", "values": ["Red", "Blue"]}, ...]
            $table->jsonb('tags')->nullable(); // Product tags

            // SEO and Meta (Product-level)
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->jsonb('seo')->nullable();

            // Shopify-specific Fields (Product-level)
            $table->string('template_suffix')->nullable();
            $table->boolean('requires_selling_plan')->default(false);

            // Status and Publishing (Product-level)
            $table->string('status')->default('draft')->index();
            $table->timestamp('published_at')->nullable()->index();
            $table->string('published_scope')->default('web')->index(); // web, global

            // Timestamps
            $table->timestamps();
            $table->softDeletes();

            // Indexes
            $table->unique(['slug', 'site_id']);
            $table->unique(['handle', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['brand_id', 'product_type_id']);
            $table->index(['published_at', 'status']);
            $table->index(['product_type', 'status']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
