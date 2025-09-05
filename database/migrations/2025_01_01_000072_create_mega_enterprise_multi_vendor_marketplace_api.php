<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MEGA ENTERPRISE PHASE 3: Multi-Vendor Marketplace & Advanced API Management
     *
     * Sistema marketplace completo con vendor management, commission tracking,
     * API management enterprise e system di webhook avanzato
     */
    public function up(): void
    {
        // Advanced vendor management system
        Schema::create('marketplace_vendors', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Primary vendor contact

            // Business information
            $table->string('business_name')->index();
            $table->string('business_slug')->unique();
            $table->string('business_registration_number', 100)->nullable()->index();
            $table->string('tax_id', 50)->nullable()->index();
            $table->string('business_type', 50)->index();
            $table->string('business_category', 50)->index();

            // Contact & location
            $table->string('contact_email')->index();
            $table->string('contact_phone', 20)->nullable();
            $table->string('website_url')->nullable();
            $table->jsonb('business_address');
            $table->jsonb('shipping_addresses'); // Multiple shipping locations
            $table->jsonb('return_addresses'); // Return/refund addresses

            // Verification & compliance
            $table->string('verification_status', 30)->default('unverified')->index();
            $table->string('kyc_status', 30)->default('not_required')->index();
            $table->jsonb('verification_documents'); // Document IDs and statuses
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users');
            $table->text('verification_notes')->nullable();

            // Business metrics & performance
            $table->decimal('total_sales', 15, 4)->default(0)->index();
            $table->integer('total_orders')->default(0)->index();
            $table->integer('total_products')->default(0)->index();
            $table->decimal('average_rating', 3, 2)->default(0)->index();
            $table->integer('total_reviews')->default(0);
            $table->decimal('fulfillment_rate', 5, 4)->default(0); // % of orders fulfilled on time
            $table->decimal('return_rate', 5, 4)->default(0);
            $table->decimal('customer_satisfaction', 3, 2)->default(0);

            // Commission & financial
            $table->decimal('commission_rate', 5, 4)->default(0.05)->index(); // Default 5%
            $table->string('commission_type', 30)->default('percentage');
            $table->jsonb('commission_structure'); // Complex commission rules
            $table->string('payout_schedule', 30)->default('monthly')->index();
            $table->integer('payout_delay_days')->default(7); // Days before payout
            $table->decimal('pending_balance', 15, 4)->default(0);
            $table->decimal('available_balance', 15, 4)->default(0);
            $table->decimal('lifetime_earnings', 15, 4)->default(0)->index();

            // Platform settings
            $table->string('status', 30)->default('pending_approval')->index();
            $table->jsonb('permissions'); // What vendor can/cannot do
            $table->jsonb('settings'); // Vendor-specific settings
            $table->jsonb('branding'); // Store branding/customization
            $table->boolean('auto_approve_products')->default(false);
            $table->boolean('can_edit_pricing')->default(true);
            $table->boolean('can_manage_inventory')->default(true);
            $table->boolean('can_process_refunds')->default(false);

            // Analytics & insights
            $table->jsonb('analytics_access'); // What analytics vendor can see
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('last_sale_at')->nullable();
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamp('contract_expires_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['verification_status', 'kyc_status']);
            $table->index(['total_sales', 'average_rating']);
            $table->index(['commission_rate', 'payout_schedule']);
        });

        // Vendor product management & approval workflow
        Schema::create('vendor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->index();
            $table->string('product_type', 50)->default('product'); // product, variant, bundle

            // Approval workflow
            $table->string('approval_status', 30)->default('draft')->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->jsonb('revision_notes')->nullable();

            // Vendor permissions for this product
            $table->boolean('can_edit_price')->default(true);
            $table->boolean('can_edit_inventory')->default(true);
            $table->boolean('can_edit_description')->default(false);
            $table->boolean('can_edit_images')->default(true);
            $table->boolean('can_delete')->default(false);

            // Commission override for specific products
            $table->decimal('commission_override', 5, 4)->nullable();
            $table->string('commission_type_override', 20)->nullable();
            $table->text('commission_notes')->nullable();

            // Product performance for vendor
            $table->bigInteger('vendor_views')->default(0);
            $table->bigInteger('vendor_sales')->default(0);
            $table->decimal('vendor_revenue', 15, 4)->default(0);
            $table->decimal('vendor_commission_earned', 15, 4)->default(0);
            $table->timestamp('last_sold_at')->nullable();

            // Quality & compliance
            $table->integer('quality_score')->default(100); // 0-100 quality score
            $table->integer('policy_violations')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->integer('featured_priority')->default(0);

            $table->timestamps();

            $table->unique(['vendor_id', 'product_id', 'product_type']);
            $table->index(['approval_status', 'submitted_at']);
            $table->index(['vendor_id', 'vendor_sales']);
            $table->index(['is_featured', 'featured_priority']);
        });

        // Commission tracking & payout management
        Schema::create('vendor_commissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('order_line_item_id')->index();
            $table->unsignedBigInteger('product_id')->index();

            // Financial details
            $table->decimal('item_subtotal', 15, 4); // Before tax/shipping
            $table->decimal('item_tax', 15, 4)->default(0);
            $table->decimal('item_shipping', 15, 4)->default(0);
            $table->decimal('item_total', 15, 4); // Final item total

            // Commission calculation
            $table->decimal('commission_rate', 5, 4); // Rate applied
            $table->string('commission_type', 20);
            $table->decimal('commission_amount', 15, 4); // Gross commission
            $table->decimal('platform_fee', 15, 4)->default(0); // Platform fees
            $table->decimal('payment_processing_fee', 15, 4)->default(0);
            $table->decimal('refund_fee', 15, 4)->default(0);
            $table->decimal('net_commission', 15, 4); // Final amount to vendor

            // Status & timing
            $table->string('status', 30)->default('pending')->index();
            $table->timestamp('earned_at')->useCurrent(); // When commission was earned
            $table->timestamp('approved_at')->nullable(); // When approved for payout
            $table->timestamp('paid_at')->nullable(); // When actually paid
            $table->foreignId('payout_id')->nullable()->constrained('vendor_payouts');

            // Dispute & refund handling
            $table->boolean('is_disputed')->default(false)->index();
            $table->text('dispute_reason')->nullable();
            $table->decimal('refund_amount', 15, 4)->default(0);
            $table->timestamp('refunded_at')->nullable();

            // Reporting periods
            $table->date('commission_period_start')->index();
            $table->date('commission_period_end')->index();
            $table->string('currency_code', 3)->index();

            $table->timestamps();

            $table->index(['vendor_id', 'status', 'earned_at']);
            $table->index(['commission_period_start', 'status']);
            $table->index(['order_id', 'product_id']);
        });

        // Vendor payout management
        Schema::create('vendor_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_id')->constrained('marketplace_vendors')->cascadeOnDelete();

            // Payout details
            $table->string('payout_reference', 100)->unique();
            $table->decimal('total_amount', 15, 4);
            $table->decimal('fees_deducted', 15, 4)->default(0);
            $table->decimal('net_amount', 15, 4);
            $table->string('currency_code', 3);
            $table->integer('commission_count'); // Number of commissions included

            // Payout method & processing
            $table->string('payout_method', 30)->index();
            $table->jsonb('payout_details'); // Bank account, PayPal email, etc.
            $table->string('status', 30)->default('pending')->index();
            $table->string('external_transaction_id', 100)->nullable()->index();
            $table->text('failure_reason')->nullable();

            // Timing
            $table->date('period_start')->index(); // Commission period covered
            $table->date('period_end')->index();
            $table->timestamp('initiated_at')->useCurrent();
            $table->timestamp('processed_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->integer('processing_time_hours')->nullable(); // For SLA tracking

            // Notifications & communications
            $table->boolean('vendor_notified')->default(false);
            $table->timestamp('vendor_notified_at')->nullable();
            $table->jsonb('notifications_sent'); // Track all notifications

            $table->timestamps();

            $table->index(['vendor_id', 'status', 'period_end']);
            $table->index(['payout_method', 'status']);
            $table->index(['initiated_at', 'completed_at']);
        });

        // Enterprise API management system
        Schema::create('api_applications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users');

            // Application details
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->string('type', 30)->default('internal')->index();
            $table->string('environment', 30)->default('development')->index();

            // API credentials
            $table->string('app_id', 32)->unique();
            $table->string('app_secret', 64);
            $table->jsonb('allowed_origins')->nullable(); // CORS origins
            $table->jsonb('allowed_ips')->nullable(); // IP whitelist
            $table->string('webhook_url')->nullable();
            $table->string('webhook_secret', 64)->nullable();

            // Access control
            $table->jsonb('scopes'); // API permissions/scopes
            $table->jsonb('rate_limits'); // Custom rate limits
            $table->boolean('can_read')->default(true);
            $table->boolean('can_write')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->boolean('can_admin')->default(false);

            // Status & lifecycle
            $table->string('status', 30)->default('active')->index();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users');

            // Usage tracking
            $table->bigInteger('total_requests')->default(0);
            $table->bigInteger('successful_requests')->default(0);
            $table->bigInteger('failed_requests')->default(0);
            $table->decimal('avg_response_time', 8, 3)->default(0);
            $table->timestamp('first_request_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['type', 'environment', 'status']);
            $table->index(['expires_at', 'status']);
        });

        // Detailed API usage tracking & analytics
        Schema::create('api_request_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_application_id')->nullable()->constrained()->nullOnDelete();

            // Request details
            $table->string('request_id', 36)->unique(); // UUID for request tracking
            $table->string('method', 10)->index(); // GET, POST, PUT, DELETE
            $table->string('endpoint', 500)->index();
            $table->string('version', 10)->default('v1')->index();
            $table->jsonb('query_parameters')->nullable();
            $table->text('user_agent')->nullable();
            $table->string('ip_address', 45)->index();
            $table->string('country_code', 2)->nullable()->index();

            // Authentication
            $table->string('auth_method', 50)->nullable(); // API key, OAuth, JWT
            $table->unsignedBigInteger('authenticated_user_id')->nullable();
            $table->string('authenticated_user_type', 50)->nullable(); // user, vendor, admin

            // Response details
            $table->integer('status_code')->index();
            $table->boolean('is_success')->default(false)->index();
            $table->integer('response_time_ms'); // Response time in milliseconds
            $table->integer('request_size_bytes')->default(0);
            $table->integer('response_size_bytes')->default(0);
            $table->text('error_message')->nullable();
            $table->string('error_code', 50)->nullable()->index();

            // Rate limiting
            $table->boolean('was_rate_limited')->default(false)->index();
            $table->integer('rate_limit_remaining')->nullable();
            $table->timestamp('rate_limit_reset_at')->nullable();

            // Billing & quotas
            $table->boolean('is_billable')->default(true)->index();
            $table->decimal('cost', 10, 6)->default(0); // Cost per request
            $table->string('quota_type', 50)->nullable(); // Type of quota consumed
            $table->integer('quota_consumed')->default(1);

            $table->timestamp('requested_at')->useCurrent()->index();

            // Optimize for large datasets with partitioning
            $table->index(['tenant_id', 'requested_at']);
            $table->index(['api_application_id', 'requested_at']);
            $table->index(['endpoint', 'method', 'requested_at']);
            $table->index(['status_code', 'requested_at']);
            $table->index(['is_success', 'response_time_ms']);
        });

        // Advanced webhook management
        Schema::create('webhooks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('api_application_id')->nullable()->constrained()->nullOnDelete();

            // Webhook configuration
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->string('url'); // Webhook endpoint URL
            $table->string('secret', 64); // For signature verification
            $table->string('format', 20)->default('json');
            $table->string('content_type', 100)->default('application/json');

            // Event subscription
            $table->jsonb('subscribed_events'); // Array of event types
            $table->jsonb('event_filters')->nullable(); // Conditional filters
            $table->boolean('subscribe_to_all')->default(false);

            // Delivery configuration
            $table->integer('timeout_seconds')->default(30);
            $table->integer('max_retries')->default(3);
            $table->jsonb('retry_intervals')->nullable(); // Custom retry schedule
            $table->boolean('verify_ssl')->default(true);
            $table->jsonb('custom_headers')->nullable();

            // Status & health
            $table->string('status', 30)->default('active')->index();
            $table->integer('consecutive_failures')->default(0);
            $table->timestamp('last_triggered_at')->nullable();
            $table->timestamp('last_success_at')->nullable();
            $table->timestamp('last_failure_at')->nullable();
            $table->text('last_error_message')->nullable();

            // Performance metrics
            $table->bigInteger('total_deliveries')->default(0);
            $table->bigInteger('successful_deliveries')->default(0);
            $table->bigInteger('failed_deliveries')->default(0);
            $table->decimal('success_rate', 5, 4)->default(0);
            $table->decimal('avg_response_time', 8, 3)->default(0);

            // Auto-disable configuration
            $table->integer('failure_threshold')->default(10); // Auto-disable after X failures
            $table->boolean('auto_disable_on_failure')->default(true);
            $table->timestamp('auto_disabled_at')->nullable();

            $table->timestamps();

            $table->index(['tenant_id', 'status']);
            $table->index(['status', 'last_triggered_at']);
            $table->index(['consecutive_failures', 'auto_disable_on_failure']);
        });

        // Webhook delivery tracking & retry management
        Schema::create('webhook_deliveries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('webhook_id')->constrained()->cascadeOnDelete();
            $table->string('delivery_id', 36)->unique(); // UUID

            // Event data
            $table->string('event_type', 100)->index();
            $table->string('event_id', 36)->index(); // Original event UUID
            $table->jsonb('payload'); // Event payload
            $table->integer('payload_size_bytes');

            // Delivery attempt
            $table->integer('attempt_number')->default(1);
            $table->timestamp('attempted_at')->useCurrent();
            $table->integer('response_time_ms')->nullable();
            $table->integer('response_status_code')->nullable();
            $table->text('response_body')->nullable();
            $table->jsonb('response_headers')->nullable();

            // Success/failure tracking
            $table->boolean('is_successful')->default(false)->index();
            $table->text('error_message')->nullable();
            $table->string('failure_reason', 50)->nullable()->index();

            // Retry management
            $table->boolean('will_retry')->default(false)->index();
            $table->timestamp('next_retry_at')->nullable()->index();
            $table->integer('max_retries')->default(3);
            $table->boolean('is_final_attempt')->default(false);

            // Security
            $table->string('signature_header', 255)->nullable(); // For verification
            $table->boolean('signature_verified')->default(false);

            $table->timestamps();

            $table->index(['webhook_id', 'attempted_at']);
            $table->index(['event_type', 'is_successful']);
            $table->index(['will_retry', 'next_retry_at']);
            $table->index(['attempt_number', 'is_successful']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('webhook_deliveries');
        Schema::dropIfExists('webhooks');
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('api_applications');
        Schema::dropIfExists('vendor_payouts');
        Schema::dropIfExists('vendor_commissions');
        Schema::dropIfExists('vendor_products');
        Schema::dropIfExists('marketplace_vendors');
    }
};
