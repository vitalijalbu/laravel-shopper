<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');

        Schema::create($prefix.'orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->userForeignKey(nullable: true);
            $table->foreignId('customer_id')->nullable()->constrained($prefix.'customers');
            $table->foreignId('cart_id')->nullable()->constrained($prefix.'carts');
            $table->foreignId('currency_id')->constrained($prefix.'currencies');
            $table->foreignId('channel_id')->constrained($prefix.'channels');
            $table->string('email')->nullable();
            $table->enum('status', ['pending', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])
                   ->default('pending')->index();
            $table->enum('financial_status', ['pending', 'paid', 'partially_paid', 'refunded', 'partially_refunded'])
                   ->default('pending')->index();
            $table->enum('fulfillment_status', ['unfulfilled', 'partial', 'fulfilled'])->default('unfulfilled')->index();
            $table->integer('subtotal')->unsigned()->default(0); // In cents
            $table->integer('tax_total')->unsigned()->default(0); // In cents
            $table->integer('discount_total')->unsigned()->default(0); // In cents
            $table->integer('shipping_total')->unsigned()->default(0); // In cents
            $table->integer('total')->unsigned()->default(0); // In cents
            $table->json('tax_breakdown')->nullable();
            $table->json('discount_breakdown')->nullable();
            $table->json('shipping_breakdown')->nullable();
            $table->string('currency_code', 3);
            $table->decimal('exchange_rate', 10, 4)->default(1.0000);
            $table->timestamp('placed_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        $prefix = config('shopper.database.table_prefix', 'shopper_');
        Schema::dropIfExists($prefix.'orders');
    }
};
