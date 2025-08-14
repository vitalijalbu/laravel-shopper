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
            $table->string('name')->index();
            $table->string('slug');
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->index();
            $table->decimal('price', 15, 2)->index();
            $table->decimal('compare_price', 15, 2)->nullable();
            $table->decimal('cost_price', 15, 2)->nullable();
            $table->integer('stock_quantity')->default(0)->index();
            $table->boolean('track_quantity')->default(true)->index();
            $table->boolean('allow_out_of_stock_purchases')->default(false);
            $table->enum('stock_status', ['in_stock', 'out_of_stock', 'on_backorder'])->default('in_stock')->index();
            $table->decimal('weight', 8, 2)->nullable();
            $table->jsonb('dimensions')->nullable(); // length, width, height
            $table->boolean('is_physical')->default(true)->index();
            $table->boolean('is_digital')->default(false)->index();
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('is_enabled')->default(true)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->enum('status', ['active', 'draft', 'archived'])->default('draft')->index();
            $table->foreignId('brand_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_type_id')->nullable()->constrained()->nullOnDelete();
            $table->jsonb('seo')->nullable();
            $table->jsonb('meta')->nullable();
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['slug', 'site_id']);
            $table->unique(['sku', 'site_id']);
            $table->index(['site_id', 'status', 'is_enabled']);
            $table->index(['brand_id', 'product_type_id']);
            $table->index(['stock_status', 'track_quantity']);
            $table->index(['is_featured', 'is_enabled']);
            $table->index(['published_at', 'status']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
