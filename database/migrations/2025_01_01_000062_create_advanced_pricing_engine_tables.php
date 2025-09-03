<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Advanced pricing tiers
        Schema::create('price_tiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('variant_id')->nullable()->constrained('product_variants')->cascadeOnDelete();
            $table->foreignId('customer_group_id')->nullable()->constrained()->nullOnDelete();
            $table->char('currency_code', 3)->index();
            $table->integer('min_quantity')->default(1)->index();
            $table->integer('max_quantity')->nullable();
            $table->decimal('price', 15, 2);
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->timestamp('valid_from')->nullable()->index();
            $table->timestamp('valid_until')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['product_id', 'customer_group_id', 'min_quantity']);
            $table->index(['currency_code', 'valid_from', 'valid_until']);
            $table->index(['is_active', 'valid_from', 'valid_until']);
        });

        // Dynamic pricing rules engine
        Schema::create('pricing_rules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['percentage', 'fixed', 'bulk', 'tiered', 'bogo', 'conditional'])->index();
            $table->jsonb('conditions'); // Complex rule conditions
            $table->jsonb('actions'); // Rule actions to apply
            $table->integer('priority')->default(0)->index(); // Higher number = higher priority
            $table->integer('usage_limit')->nullable();
            $table->integer('usage_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->boolean('applies_to_all_products')->default(false);
            $table->timestamp('valid_from')->nullable()->index();
            $table->timestamp('valid_until')->nullable()->index();
            $table->timestamps();

            $table->index(['site_id', 'is_active', 'priority']);
            $table->index(['type', 'is_active']);
            $table->index(['valid_from', 'valid_until', 'is_active']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Products affected by pricing rules
        Schema::create('pricing_rule_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pricing_rule_id', 'product_id']);
            $table->index(['product_id']);
        });

        // Customer groups affected by pricing rules
        Schema::create('pricing_rule_customer_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_group_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['pricing_rule_id', 'customer_group_id']);
            $table->index(['customer_group_id']);
        });

        // Track pricing rule applications for analytics
        Schema::create('pricing_rule_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pricing_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('cart_id')->nullable()->constrained('carts')->nullOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('original_amount', 15, 2);
            $table->decimal('discounted_amount', 15, 2);
            $table->decimal('discount_amount', 15, 2);
            $table->jsonb('applied_products'); // Products affected
            $table->timestamp('applied_at')->index();

            $table->index(['pricing_rule_id', 'applied_at']);
            $table->index(['customer_id', 'applied_at']);
            $table->index(['order_id']);
        });

        // Exchange rates for multi-currency
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->char('from_currency', 3)->index();
            $table->char('to_currency', 3)->index();
            $table->decimal('rate', 20, 10);
            $table->string('provider', 50)->default('manual'); // ecb, fixer, manual
            $table->timestamp('fetched_at')->nullable();
            $table->timestamps();

            $table->unique(['from_currency', 'to_currency']);
            $table->index(['provider', 'updated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('pricing_rule_applications');
        Schema::dropIfExists('pricing_rule_customer_groups');
        Schema::dropIfExists('pricing_rule_products');
        Schema::dropIfExists('pricing_rules');
        Schema::dropIfExists('price_tiers');
    }
};
