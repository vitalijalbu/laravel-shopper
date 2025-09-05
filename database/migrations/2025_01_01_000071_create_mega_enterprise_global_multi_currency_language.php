<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MEGA ENTERPRISE PHASE 2: Global Multi-Currency & Multi-Language System
     * 
     * Sistema avanzato per supportare tutte le valute globali con AI-powered
     * exchange rates e sistema di traduzione automatica enterprise-grade
     */
    public function up(): void
    {
        // Advanced global currency system
        Schema::create('global_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code', 3)->unique(); // ISO 4217
            $table->string('name', 100)->index();
            $table->string('symbol', 10);
            $table->string('symbol_position', 10)->default('before'); // before, after
            $table->tinyInteger('decimal_places')->default(2);
            $table->decimal('rounding_precision', 10, 8)->default(0.01);
            
            // Currency classification
            $table->string('type', 20)->default('fiat')->index();
            $table->boolean('is_crypto')->default(false)->index();
            $table->boolean('is_stable_coin')->default(false)->index();
            $table->boolean('is_legal_tender')->default(true)->index();
            
            // Market data
            $table->decimal('market_cap_usd', 20, 4)->nullable(); // For crypto
            $table->decimal('volatility_score', 5, 2)->default(0); // 0-100 volatility index
            $table->decimal('liquidity_score', 5, 2)->default(100); // Market liquidity
            $table->string('issuing_authority', 100)->nullable(); // Central bank, company
            
            // Regional & regulatory
            $table->string('primary_region', 50)->nullable()->index();
            $table->jsonb('supported_countries')->nullable(); // ISO country codes
            $table->jsonb('restricted_countries')->nullable(); // Where it's banned
            $table->string('regulatory_status', 20)->default('approved');
            
            // Status & lifecycle
            $table->string('status', 20)->default('active')->index();
            $table->timestamp('listed_at')->nullable();
            $table->timestamp('deprecated_at')->nullable();
            $table->text('deprecation_reason')->nullable();
            
            $table->timestamps();
            
            $table->index(['type', 'status']);
            $table->index(['volatility_score', 'liquidity_score']);
            $table->index(['primary_region', 'status']);
        });

        // AI-powered real-time exchange rates with prediction
        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('from_currency_code', 3)->index();
            $table->string('to_currency_code', 3)->index();
            $table->decimal('rate', 20, 10); // High precision for crypto
            
            // Rate metadata
            $table->string('source', 30)->index();
            $table->string('source_id', 100)->nullable(); // API response ID
            $table->decimal('bid_rate', 20, 10)->nullable(); // For forex
            $table->decimal('ask_rate', 20, 10)->nullable();
            $table->decimal('spread_percentage', 8, 6)->nullable();
            
            // AI predictions & confidence
            $table->decimal('ai_confidence_score', 3, 2)->default(0); // 0-1 confidence
            $table->string('predicted_trend', 20)->nullable();
            $table->decimal('trend_confidence', 3, 2)->nullable();
            $table->jsonb('prediction_factors')->nullable(); // Economic indicators used
            
            // Time validity
            $table->timestamp('valid_from')->useCurrent()->index();
            $table->timestamp('valid_until')->nullable()->index();
            $table->timestamp('fetched_at')->useCurrent();
            $table->integer('cache_duration_seconds')->default(3600);
            
            // Historical tracking
            $table->decimal('previous_rate', 20, 10)->nullable();
            $table->decimal('daily_change_percent', 8, 4)->nullable();
            $table->decimal('weekly_change_percent', 8, 4)->nullable();
            $table->decimal('monthly_change_percent', 8, 4)->nullable();
            
            $table->timestamps();
            
            $table->unique(['from_currency_code', 'to_currency_code', 'valid_from'], 'global_exchange_rates_unique');
            $table->index(['from_currency_code', 'to_currency_code', 'valid_until']);
            $table->index(['source', 'fetched_at']);
            $table->index(['ai_confidence_score', 'predicted_trend']);
        });

        // Tenant-specific currency configuration
        Schema::create('tenant_currencies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('currency_code', 3)->index();
            
            // Tenant currency settings
            $table->boolean('is_default')->default(false)->index();
            $table->string('status')->default('active')->index(); // active, inactive, restricted
            $table->integer('display_order')->default(0)->index();
            $table->string('display_format', 50)->nullable(); // Custom format override
            
            // Pricing strategy
            $table->string('pricing_strategy', 30)->default('auto_convert');
            $table->decimal('markup_percentage', 8, 4)->default(0); // Additional markup for this currency
            $table->decimal('rounding_rule', 8, 4)->default(0.01); // Round to nearest X
            $table->boolean('round_up_always')->default(false);
            
            // Market-specific adjustments
            $table->decimal('market_adjustment', 8, 4)->default(0); // Local market price adjustment
            $table->jsonb('pricing_rules')->nullable(); // Complex pricing rules
            $table->decimal('minimum_order_value', 15, 4)->nullable();
            $table->decimal('free_shipping_threshold', 15, 4)->nullable();
            
            // Payment integration
            $table->jsonb('supported_payment_methods')->nullable();
            $table->decimal('payment_processing_fee', 8, 4)->default(0);
            $table->boolean('supports_installments')->default(false);
            $table->integer('max_installments')->nullable();
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'currency_code']);
            $table->index(['tenant_id', 'is_default', 'status']);
            $table->index(['currency_code', 'status']);
        });

        // Global language system with AI translation support
        Schema::create('global_languages', function (Blueprint $table) {
            $table->id();
            $table->string('code', 5)->unique(); // en-US, it-IT, zh-CN
            $table->string('iso_639_1', 2)->index(); // en, it, zh
            $table->string('iso_3166_1', 2)->index(); // US, IT, CN
            $table->string('name', 100)->index(); // English (United States)
            $table->string('native_name', 100); // Native language name
            $table->string('english_name', 100)->index();
            
            // Language characteristics
            $table->string('direction', 10)->default('ltr')->index();
            $table->string('script', 50)->nullable(); // Latin, Cyrillic, Arabic, etc.
            $table->string('font_family', 100)->nullable(); // Recommended font
            $table->string('complexity', 20)->default('simple'); // For translation
            
            // Geographic & usage data
            $table->string('primary_country', 2)->nullable()->index();
            $table->jsonb('supported_countries')->nullable();
            $table->bigInteger('native_speakers')->nullable();
            $table->bigInteger('total_speakers')->nullable();
            $table->decimal('internet_penetration', 5, 2)->nullable(); // % of speakers online
            
            // Translation & AI support
            $table->boolean('supports_ai_translation')->default(true)->index();
            $table->decimal('translation_quality_score', 3, 2)->default(0); // AI translation quality
            $table->boolean('requires_human_review')->default(false);
            $table->decimal('completion_percentage', 5, 2)->default(0); // % of translations complete
            
            // Business metrics
            $table->decimal('ecommerce_penetration', 5, 2)->nullable(); // % doing online shopping
            $table->decimal('avg_order_value_usd', 15, 4)->nullable();
            $table->decimal('conversion_rate', 5, 4)->nullable();
            $table->string('business_priority', 20)->default('medium')->index();
            
            // Status & lifecycle
            $table->string('status', 20)->default('active')->index();
            $table->boolean('is_default')->default(false);
            $table->timestamp('supported_since')->nullable();
            
            $table->timestamps();
            
            $table->index(['status', 'business_priority']);
            $table->index(['supports_ai_translation', 'translation_quality_score'], 'gl_ai_trans_quality_idx');
            $table->index(['primary_country', 'status']);
        });

        // Advanced translation system with AI & human review
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Translation key structure
            $table->string('namespace', 100)->index(); // ui, products, emails, marketing, legal
            $table->string('group', 100)->index(); // validation, checkout, product_details
            $table->string('key', 255)->index(); // specific translation key
            $table->string('language_code', 5)->index();
            
            // Translation content
            $table->text('value'); // Translated content
            $table->text('original_value')->nullable(); // Original text for reference
            $table->text('context')->nullable(); // Context for translators
            $table->text('notes')->nullable(); // Translator notes
            
            // Translation metadata
            $table->string('type', 20)->default('string');
            $table->jsonb('variables')->nullable(); // Placeholders used in translation
            $table->integer('character_count')->default(0)->index();
            $table->integer('word_count')->default(0);
            
            // AI translation data
            $table->boolean('is_ai_generated')->default(false)->index();
            $table->decimal('ai_confidence_score', 3, 2)->nullable();
            $table->string('ai_model', 50)->nullable(); // GPT-4, Google Translate, etc.
            $table->jsonb('ai_alternatives')->nullable(); // Alternative AI translations
            
            // Human review process
            $table->boolean('requires_review')->default(false)->index();
            $table->boolean('is_reviewed')->default(false)->index();
            $table->boolean('is_approved')->default(false)->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('review_notes')->nullable();
            
            // Quality & performance
            $table->string('quality_grade', 5)->nullable()->index();
            $table->decimal('usage_frequency', 10, 4)->default(0); // How often used
            $table->decimal('conversion_impact', 8, 4)->nullable(); // Impact on conversions
            $table->jsonb('ab_test_results')->nullable();
            
            // Version control
            $table->integer('version')->default(1);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamp('published_at')->nullable();
            $table->jsonb('change_log')->nullable();
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'namespace', 'group', 'key', 'language_code', 'version'], 'tenant_translations_unique');
            $table->index(['tenant_id', 'language_code', 'is_active']);
            $table->index(['namespace', 'is_ai_generated', 'requires_review'], 'trans_ns_ai_review_idx');
            $table->index(['quality_grade', 'conversion_impact']);
            $table->index(['usage_frequency', 'language_code']);
        });

        // Translation performance analytics
        Schema::create('translation_analytics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 5)->index();
            $table->string('namespace', 100)->index();
            
            // Usage metrics
            $table->bigInteger('pageviews')->default(0);
            $table->bigInteger('unique_visitors')->default(0);
            $table->decimal('bounce_rate', 5, 4)->default(0);
            $table->decimal('session_duration_avg', 10, 2)->default(0);
            
            // Conversion metrics
            $table->bigInteger('conversions')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->decimal('revenue', 15, 4)->default(0);
            $table->decimal('avg_order_value', 15, 4)->default(0);
            
            // Translation quality impact
            $table->integer('translation_errors_reported')->default(0);
            $table->decimal('user_satisfaction_score', 3, 2)->nullable(); // User feedback
            $table->jsonb('common_issues')->nullable(); // Reported translation problems
            
            // Time period
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            $table->string('period_type', 20)->index();
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'language_code', 'namespace', 'period_start', 'period_type'], 'translation_analytics_unique');
            $table->index(['period_start', 'conversion_rate']);
            $table->index(['language_code', 'revenue']);
        });

        // Currency-specific product pricing with dynamic rules
        Schema::create('product_currency_pricing', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('product_id')->index(); // Polymorphic for products/variants
            $table->string('product_type', 50)->default('product'); // product, variant, bundle
            $table->string('currency_code', 3)->index();
            
            // Pricing data
            $table->decimal('base_price', 15, 4); // Price before adjustments
            $table->decimal('sale_price', 15, 4)->nullable();
            $table->decimal('cost_price', 15, 4)->nullable();
            $table->decimal('msrp', 15, 4)->nullable(); // Manufacturer suggested retail price
            
            // Pricing strategy
            $table->string('pricing_method', 30)->default('auto_convert');
            $table->boolean('auto_update')->default(true)->index();
            $table->decimal('margin_target', 5, 2)->nullable(); // Target profit margin %
            $table->decimal('markup_percentage', 8, 4)->default(0);
            
            // Market-specific adjustments
            $table->decimal('market_adjustment', 8, 4)->default(0); // Local market price modifier
            $table->decimal('tax_rate', 8, 4)->default(0); // Local tax rate
            $table->boolean('tax_inclusive')->default(false);
            $table->jsonb('pricing_rules')->nullable(); // Complex conditional pricing
            
            // Competitive intelligence
            $table->decimal('competitor_min_price', 15, 4)->nullable();
            $table->decimal('competitor_avg_price', 15, 4)->nullable();
            $table->decimal('competitor_max_price', 15, 4)->nullable();
            $table->string('price_position', 20)->nullable();
            
            // Performance tracking
            $table->bigInteger('views')->default(0);
            $table->bigInteger('purchases')->default(0);
            $table->decimal('conversion_rate', 5, 4)->default(0);
            $table->timestamp('last_sold_at')->nullable();
            $table->timestamp('price_updated_at')->nullable();
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'product_id', 'product_type', 'currency_code'], 'multi_currency_pricing_unique');
            $table->index(['currency_code', 'pricing_method']);
            $table->index(['auto_update', 'price_updated_at']);
            $table->index(['conversion_rate', 'currency_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_currency_pricing');
        Schema::dropIfExists('translation_analytics');
        Schema::dropIfExists('translations');
        Schema::dropIfExists('global_languages');
        Schema::dropIfExists('tenant_currencies');
        Schema::dropIfExists('exchange_rates');
        Schema::dropIfExists('global_currencies');
    }
};
