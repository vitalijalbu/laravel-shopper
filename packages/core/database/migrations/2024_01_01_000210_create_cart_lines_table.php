<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'cart_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained($prefix.'carts')->cascadeOnDelete();
            $table->morphs('purchasable'); // Can be product_variant or other purchasable
            $table->integer('quantity')->unsigned()->default(1);
            $table->integer('unit_price')->unsigned()->default(0); // In cents
            $table->integer('total_price')->unsigned()->default(0); // In cents
            $table->json('product_snapshot')->nullable(); // Store product data at time of adding
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'cart_lines');
    }
};
