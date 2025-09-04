<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ====== REVIEWS & RATINGS SYSTEM ======
        Schema::create('product_reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('order_line_id')->nullable()->constrained('order_lines')->nullOnDelete();
            $table->integer('rating')->check('rating >= 1 AND rating <= 5'); // 1-5 stars
            $table->string('title');
            $table->text('content');
            $table->boolean('is_verified_purchase')->default(false)->index();
            $table->boolean('is_approved')->default(false)->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->integer('helpful_count')->default(0);
            $table->integer('unhelpful_count')->default(0);
            $table->timestamp('replied_at')->nullable();
            $table->text('reply_content')->nullable();
            $table->unsignedBigInteger('replied_by')->nullable(); // Admin user ID
            $table->timestamps();

            $table->unique(['customer_id', 'product_id', 'order_line_id']); // One review per product per order
            $table->index(['product_id', 'is_approved', 'rating']);
            $table->index(['customer_id', 'created_at']);
            $table->index(['is_verified_purchase', 'is_approved']);
            $table->index(['helpful_count', 'is_approved']);
        });

        // Review media (photos/videos)
        Schema::create('review_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('product_reviews')->cascadeOnDelete();
            $table->enum('media_type', ['image', 'video'])->index();
            $table->string('url', 500);
            $table->string('thumbnail_url', 500)->nullable();
            $table->string('alt_text')->nullable();
            $table->integer('file_size')->nullable(); // In bytes
            $table->string('mime_type', 100)->nullable();
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['review_id', 'media_type']);
        });

        // Review helpfulness votes
        Schema::create('review_votes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('review_id')->constrained('product_reviews')->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->boolean('is_helpful'); // true = helpful, false = not helpful
            $table->timestamps();

            $table->unique(['review_id', 'customer_id']);
            $table->index(['review_id', 'is_helpful']);
        });

        // Product performance analytics
        Schema::create('product_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->integer('views')->default(0);
            $table->integer('unique_views')->default(0);
            $table->integer('add_to_carts')->default(0);
            $table->integer('purchases')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->integer('units_sold')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0); // purchases / views
            $table->decimal('cart_rate', 5, 4)->default(0); // add_to_carts / views
            $table->decimal('avg_time_on_page', 8, 2)->default(0); // seconds
            $table->integer('bounce_count')->default(0);
            $table->timestamps();

            $table->unique(['product_id', 'date']);
            $table->index(['date', 'views']);
            $table->index(['conversion_rate', 'date']);
        });

        // ====== PROMOTIONAL CAMPAIGNS ======
        Schema::create('campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['sale', 'flash_sale', 'clearance', 'seasonal', 'new_product', 'abandoned_cart'])->index();
            $table->enum('status', ['draft', 'scheduled', 'active', 'paused', 'completed', 'cancelled'])->default('draft')->index();
            $table->timestamp('start_date')->nullable()->index();
            $table->timestamp('end_date')->nullable()->index();
            $table->decimal('budget', 15, 2)->nullable();
            $table->decimal('spent', 15, 2)->default(0);
            $table->jsonb('target_audience')->nullable(); // Targeting criteria
            $table->jsonb('performance_metrics')->nullable(); // CTR, conversion, etc.
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('revenue', 15, 2)->default(0);
            $table->timestamps();

            $table->index(['site_id', 'status', 'type']);
            $table->index(['start_date', 'end_date', 'status']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Gift cards system
        Schema::create('gift_cards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('site_id')->nullable()->index();
            $table->string('code', 100)->unique();
            $table->decimal('initial_amount', 15, 2);
            $table->decimal('balance', 15, 2);
            $table->char('currency_code', 3)->index();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete(); // Recipient
            $table->foreignId('purchased_by')->nullable()->constrained('customers')->nullOnDelete(); // Purchaser
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete(); // Purchase order
            $table->string('recipient_email')->nullable();
            $table->string('recipient_name')->nullable();
            $table->text('message')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('activated_at')->nullable();
            $table->timestamps();

            $table->index(['code', 'is_active']);
            $table->index(['customer_id', 'is_active']);
            $table->index(['balance', 'expires_at']);
            $table->foreign('site_id')->references('id')->on('sites')->onDelete('cascade');
        });

        // Gift card transactions
        Schema::create('gift_card_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('gift_card_id')->constrained()->cascadeOnDelete();
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('type', ['purchase', 'redeem', 'refund', 'expire', 'transfer'])->index();
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['gift_card_id', 'type', 'created_at']);
            $table->index(['order_id']);
        });

        // ====== WISHLIST ENHANCEMENTS ======
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                if (!Schema::hasColumn('wishlists', 'is_public')) {
                    $table->boolean('is_public')->default(false)->after('name');
                    $table->string('share_token', 100)->nullable()->unique()->after('is_public');
                    $table->integer('views_count')->default(0)->after('share_token');
                }
            });
        }
    }

    public function down(): void
    {
        // Remove wishlist enhancements
        if (Schema::hasTable('wishlists')) {
            Schema::table('wishlists', function (Blueprint $table) {
                if (Schema::hasColumn('wishlists', 'is_public')) {
                    $table->dropColumn(['is_public', 'share_token', 'views_count']);
                }
            });
        }

        // Drop all new tables
        Schema::dropIfExists('gift_card_transactions');
        Schema::dropIfExists('gift_cards');
        Schema::dropIfExists('campaigns');
        Schema::dropIfExists('product_analytics');
        Schema::dropIfExists('review_votes');
        Schema::dropIfExists('review_media');
        Schema::dropIfExists('product_reviews');
    }
};
