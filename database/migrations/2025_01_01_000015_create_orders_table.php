<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('order_number');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email')->index();
            $table->jsonb('customer_details');
            $table->foreignId('currency_id')->constrained();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_total', 15, 2)->default(0);
            $table->decimal('shipping_total', 15, 2)->default(0);
            $table->decimal('discount_total', 15, 2)->default(0);
            $table->decimal('total', 15, 2)->index();
            $table->enum('status', ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled', 'refunded'])->default('pending')->index();
            $table->enum('payment_status', ['pending', 'paid', 'partially_paid', 'failed', 'refunded', 'cancelled'])->default('pending')->index();
            $table->enum('fulfillment_status', ['unfulfilled', 'partially_fulfilled', 'fulfilled', 'shipped', 'delivered'])->default('unfulfilled')->index();
            $table->jsonb('shipping_address');
            $table->jsonb('billing_address');
            $table->jsonb('applied_discounts')->nullable();
            $table->string('shipping_method')->nullable()->index();
            $table->string('payment_method')->nullable()->index();
            $table->jsonb('payment_details')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('shipped_at')->nullable()->index();
            $table->timestamp('delivered_at')->nullable()->index();
            $table->timestamps();
            $table->softDeletes();

            $table->jsonb('data')->nullable()->comment('Custom fields data based on JSON schema');

            $table->unique(['order_number', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['status', 'payment_status', 'fulfillment_status']);
            $table->index(['customer_email', 'site_id']);
            $table->index(['created_at', 'status']);
            $table->index(['total', 'status']);

            $table->index('subtotal');
            $table->index('tax_total');
            $table->index('shipping_total');
            $table->index('discount_total');
            $table->index('updated_at');

            $table->index(['status', 'created_at']);
            $table->index(['payment_status', 'status']);
            $table->index(['fulfillment_status', 'status']);
            $table->index(['shipped_at', 'status']);
            $table->index(['delivered_at', 'status']);
            $table->index(['currency_id', 'status']);

            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
