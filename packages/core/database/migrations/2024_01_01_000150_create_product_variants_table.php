<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained($prefix.'products')->cascadeOnDelete();
            $table->string('sku')->unique()->nullable();
            $table->string('barcode')->unique()->nullable();
            $table->integer('price')->unsigned()->default(0); // Price in cents
            $table->integer('compare_price')->unsigned()->nullable(); // Compare price in cents
            $table->integer('cost_price')->unsigned()->default(0); // Cost price in cents
            $table->boolean('track_quantity')->default(true);
            $table->integer('quantity')->default(0);
            $table->integer('inventory_quantity')->default(0);
            $table->string('inventory_policy')->default('deny'); // deny, continue
            $table->boolean('requires_shipping')->default(true);
            $table->boolean('taxable')->default(true);
            $table->dimensions();
            $table->integer('position')->default(1);
            $table->json('option_values')->nullable(); // Store selected option values
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'product_variants');
    }
};
