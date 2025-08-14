<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique();
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed_amount', 'free_shipping'])->default('percentage');
            $table->decimal('value', 15, 2); // percentage or fixed amount
            $table->decimal('minimum_order_amount', 15, 2)->nullable();
            $table->decimal('maximum_discount_amount', 15, 2)->nullable();
            $table->integer('usage_limit')->nullable(); // null = unlimited
            $table->integer('usage_limit_per_customer')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_enabled')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('eligible_products')->nullable(); // product IDs
            $table->json('eligible_categories')->nullable(); // category IDs
            $table->json('eligible_customers')->nullable(); // customer IDs or customer group IDs
            $table->timestamps();

            $table->index('code');
            $table->index(['is_enabled', 'starts_at', 'expires_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
