<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'carts', function (Blueprint $table) {
            $table->id();
            $table->userForeignKey(nullable: true);
            $table->foreignId('customer_id')->nullable()->constrained($prefix.'customers')->cascadeOnDelete();
            $table->foreignId('currency_id')->constrained($prefix.'currencies');
            $table->foreignId('channel_id')->constrained($prefix.'channels');
            $table->string('session_id')->nullable()->index();
            $table->string('coupon_code')->nullable()->index();
            $table->json('applied_discounts')->nullable();
            $table->integer('items_count')->default(0);
            $table->integer('items_quantity')->default(0);
            $table->integer('subtotal')->unsigned()->default(0); // In cents
            $table->integer('tax_total')->unsigned()->default(0); // In cents
            $table->integer('discount_total')->unsigned()->default(0); // In cents
            $table->integer('shipping_total')->unsigned()->default(0); // In cents
            $table->integer('total')->unsigned()->default(0); // In cents
            $table->timestamp('completed_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'carts');
    }
};
