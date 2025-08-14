<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'discounts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->unique()->nullable(); // Coupon code
            $table->string('type'); // percentage, fixed_amount, buy_x_get_y, free_shipping
            $table->integer('value')->unsigned()->nullable(); // Percentage or fixed amount in cents
            $table->integer('minimum_amount')->unsigned()->nullable(); // Minimum order amount in cents
            $table->integer('usage_limit')->unsigned()->nullable(); // How many times can be used in total
            $table->integer('usage_limit_per_customer')->unsigned()->nullable();
            $table->integer('used_count')->unsigned()->default(0);
            $table->scheduling();
            $table->boolean('active')->default(true)->index();
            $table->json('conditions')->nullable(); // Additional conditions
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'discounts');
    }
};
