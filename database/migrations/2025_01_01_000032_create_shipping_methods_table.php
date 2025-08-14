<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_zone_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['flat_rate', 'free', 'calculated', 'pickup'])->default('flat_rate');
            $table->decimal('cost', 10, 2)->default(0);
            $table->decimal('free_shipping_threshold', 10, 2)->nullable();
            $table->integer('min_delivery_days')->nullable();
            $table->integer('max_delivery_days')->nullable();
            $table->decimal('weight_limit', 8, 2)->nullable(); // kg
            $table->decimal('size_limit', 8, 2)->nullable(); // cm
            $table->boolean('requires_address')->default(true);
            $table->boolean('is_enabled')->default(true);
            $table->integer('sort_order')->default(0);
            $table->json('configuration')->nullable(); // Carrier-specific settings
            $table->timestamps();

            $table->index(['shipping_zone_id', 'is_enabled']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_methods');
    }
};
