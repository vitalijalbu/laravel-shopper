<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->nullable()->constrained($prefix.'brands')->nullOnDelete();
            $table->foreignId('product_type_id')->constrained($prefix.'product_types');
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->text('short_description')->nullable();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->enum('status', ['active', 'draft', 'archived'])->default('draft')->index();
            $table->boolean('is_visible')->default(true)->index();
            $table->boolean('backorder')->default(false)->index();
            $table->boolean('requires_shipping')->default(true)->index();
            $table->boolean('track_quantity')->default(true)->index();
            $table->string('seo_title')->nullable();
            $table->text('seo_description')->nullable();
            $table->dimensions();
            $table->json('meta')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'products');
    }
};
