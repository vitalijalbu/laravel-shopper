<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Metafields (Shopify-style custom fields)
        Schema::create('metafields', function (Blueprint $table) {
            $table->id();

            // Owner (polymorphic relationship)
            $table->string('owner_resource'); // product, product_variant, customer, order, etc.
            $table->unsignedBigInteger('owner_id');

            // Metafield Definition
            $table->string('namespace')->index(); // e.g., 'custom', 'seo', 'technical'
            $table->string('key')->index(); // e.g., 'material', 'care_instructions'
            $table->text('value'); // The actual value
            $table->string('type')->default('single_line_text_field'); // text, number, date, etc.

            // Display and Behavior
            $table->string('description')->nullable();
            $table->boolean('show_in_storefront')->default(false);
            $table->integer('sort_order')->default(0);

            $table->timestamps();

            // Indexes
            $table->index(['owner_resource', 'owner_id']);
            $table->index(['namespace', 'key']);
            $table->unique(['owner_resource', 'owner_id', 'namespace', 'key'], 'metafields_unique');
        });

        // Product Tags (Shopify-style)
        Schema::create('product_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('color')->nullable();
            $table->integer('usage_count')->default(0);
            $table->timestamps();

            $table->index('usage_count');
        });

        // Product Tag Pivot
        Schema::create('product_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignId('product_tag_id')->constrained('product_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'product_tag_id']);
        });

        // Gift Cards (Shopify feature)
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();

            // Gift Card Details
            $table->string('code')->unique(); // XXXX-XXXX-XXXX-XXXX
            $table->decimal('initial_value', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->string('currency', 3);

            // Customer Information
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->string('recipient_email')->nullable();

            // Status and Dates
            $table->string('status')->default('active'); // active, disabled, expired, used
            $table->date('expires_at')->nullable();
            $table->timestamp('last_used_at')->nullable();

            // Creation Information
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete(); // If purchased
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();

            // Additional Information
            $table->text('note')->nullable();
            $table->json('metadata')->nullable();

            $table->timestamps();

            // Indexes
            $table->index(['site_id', 'status']);
            $table->index(['customer_id', 'status']);
            $table->index(['expires_at', 'status']);
            $table->index('balance');
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Gift Card Transactions
        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained('gift_cards')->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained('orders')->nullOnDelete();

            $table->string('type'); // debit, credit
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('note')->nullable();

            $table->timestamps();

            $table->index(['gift_card_id', 'created_at']);
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gift_card_transactions');
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('product_tag');
        Schema::dropIfExists('product_tags');
        Schema::dropIfExists('metafields');
    }
};
