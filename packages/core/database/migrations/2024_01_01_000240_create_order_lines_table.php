<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'order_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained($prefix.'orders')->cascadeOnDelete();
            $table->morphs('purchasable'); // Can be product_variant or other purchasable
            $table->string('type')->default('product'); // product, shipping, discount, etc.
            $table->string('title'); // Product name at time of order
            $table->string('variant_title')->nullable(); // Variant details
            $table->string('sku')->nullable();
            $table->integer('quantity')->unsigned()->default(1);
            $table->integer('unit_price')->unsigned()->default(0); // In cents
            $table->integer('total_price')->unsigned()->default(0); // In cents
            $table->integer('total_discount')->unsigned()->default(0); // In cents
            $table->json('product_snapshot')->nullable(); // Store product/variant data
            $table->json('tax_lines')->nullable(); // Tax breakdown for this line
            $table->boolean('requires_shipping')->default(true);
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'order_lines');
    }
};
