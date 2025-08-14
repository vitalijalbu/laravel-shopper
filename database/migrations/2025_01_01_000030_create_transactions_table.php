<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('reference'); // TXN-XXXXXX
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->index(); // payment, refund, capture, void
            $table->string('status')->index(); // pending, completed, failed, cancelled
            $table->string('gateway')->index(); // stripe, paypal, manual, etc
            $table->string('gateway_reference')->nullable()->index(); // external transaction ID
            $table->decimal('amount', 15, 2)->index();
            $table->char('currency_code', 3)->index();
            $table->jsonb('gateway_data')->nullable(); // raw gateway response
            $table->jsonb('metadata')->nullable(); // additional data
            $table->string('failure_reason')->nullable();
            $table->timestamp('processed_at')->nullable()->index();
            $table->timestamps();

            $table->unique(['reference', 'site_id']);
            $table->index(['site_id', 'status']);
            $table->index(['order_id', 'type']);
            $table->index(['status', 'type']);
            $table->index(['gateway', 'status']);
            $table->index(['processed_at', 'status']);
            $table->index(['amount', 'currency_code']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
