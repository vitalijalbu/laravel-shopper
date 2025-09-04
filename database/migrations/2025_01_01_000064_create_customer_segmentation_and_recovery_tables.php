<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Customer segments (Shopify Plus-style)
        Schema::create('customer_segments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['static', 'dynamic', 'smart'])->default('dynamic')->index();
            $table->jsonb('conditions'); // Segment criteria
            $table->boolean('auto_update')->default(false)->index();
            $table->integer('customer_count')->default(0)->index();
            $table->timestamp('last_updated_at')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();

            $table->index(['site_id', 'type', 'is_active']);
            $table->index(['auto_update', 'last_updated_at']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Customer segment membership
        Schema::create('customer_segment_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('segment_id')->constrained('customer_segments')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->timestamp('added_at')->index();
            $table->boolean('is_automatic')->default(true); // Auto vs manual addition

            $table->unique(['segment_id', 'customer_id']);
            $table->index(['customer_id', 'added_at']);
        });

        // Cart abandonment tracking
        Schema::create('abandoned_carts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cart_id')->constrained('carts')->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->string('customer_email')->nullable()->index();
            $table->string('customer_phone', 20)->nullable();
            $table->decimal('cart_value', 15, 2);
            $table->integer('items_count');
            $table->timestamp('abandoned_at')->index();
            $table->integer('recovery_attempts')->default(0);
            $table->timestamp('last_recovery_sent_at')->nullable();
            $table->timestamp('recovered_at')->nullable()->index();
            $table->foreignId('recovery_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->jsonb('metadata')->nullable(); // Browser, device, etc.
            $table->timestamps();

            $table->index(['abandoned_at', 'recovered_at']);
            $table->index(['customer_email', 'recovered_at']);
            $table->index(['recovery_attempts', 'last_recovery_sent_at']);
        });

        // Cart recovery campaigns
        Schema::create('cart_recovery_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('delay_hours'); // Hours after abandonment
            $table->string('email_template'); // Template identifier
            $table->string('discount_code')->nullable();
            $table->decimal('discount_percentage', 5, 2)->nullable();
            $table->decimal('discount_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->integer('emails_sent')->default(0);
            $table->integer('recoveries')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->decimal('revenue_recovered', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['site_id', 'is_active']);
            $table->index(['delay_hours', 'is_active']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Recovery email sending log
        Schema::create('cart_recovery_emails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('abandoned_cart_id')->constrained()->cascadeOnDelete();
            $table->foreignId('campaign_id')->constrained('cart_recovery_campaigns')->cascadeOnDelete();
            $table->string('email_address');
            $table->timestamp('sent_at')->index();
            $table->boolean('opened')->default(false);
            $table->timestamp('opened_at')->nullable();
            $table->boolean('clicked')->default(false);
            $table->timestamp('clicked_at')->nullable();
            $table->boolean('recovered')->default(false);
            $table->timestamp('recovered_at')->nullable();
            $table->jsonb('metadata')->nullable();

            $table->index(['campaign_id', 'sent_at']);
            $table->index(['email_address', 'sent_at']);
            $table->index(['opened', 'clicked', 'recovered']);
        });

        // Search tracking and optimization
        Schema::create('search_terms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('term', 500)->index();
            $table->string('normalized_term', 500)->index(); // Cleaned/stemmed version
            $table->integer('results_count')->default(0);
            $table->integer('search_count')->default(0)->index();
            $table->integer('click_count')->default(0);
            $table->integer('conversion_count')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->timestamp('first_searched_at');
            $table->timestamp('last_searched_at')->index();
            $table->timestamps();

            $table->unique(['site_id', 'term']);
            $table->index(['site_id', 'search_count']);
            $table->index(['normalized_term', 'search_count']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Product search performance
        Schema::create('product_search_performance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('search_term', 500);
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('click_through_rate', 5, 4)->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->date('date');
            $table->timestamps();

            $table->unique(['product_id', 'search_term', 'date']);
            $table->index(['search_term', 'date']);
            $table->index(['click_through_rate', 'conversion_rate'], 'psp_ctr_cr_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_search_performance');
        Schema::dropIfExists('search_terms');
        Schema::dropIfExists('cart_recovery_emails');
        Schema::dropIfExists('cart_recovery_campaigns');
        Schema::dropIfExists('abandoned_carts');
        Schema::dropIfExists('customer_segment_members');
        Schema::dropIfExists('customer_segments');
    }
};
