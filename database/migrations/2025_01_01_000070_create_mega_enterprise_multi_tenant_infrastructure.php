<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * MEGA ENTERPRISE PHASE 1: Multi-Tenant Infrastructure
     * 
     * Implementa l'architettura per supportare migliaia di tenant
     * con database sharding e isolamento completo
     */
    public function up(): void
    {
        // Master tenant management table
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('domain')->unique();
            $table->string('subdomain', 100)->unique();
            $table->string('database_name', 100)->index();
            
            // Tenant configuration
            $table->string('plan', 50)->default('starter')->index();
            $table->string('status', 50)->default('trial')->index();
            $table->jsonb('settings')->nullable(); // Custom tenant settings
            $table->jsonb('limits')->nullable(); // API limits, storage, users, orders
            $table->jsonb('features')->nullable(); // Enabled features per plan
            
            // Billing & usage
            $table->decimal('monthly_revenue', 15, 4)->default(0)->index();
            $table->integer('total_orders')->default(0)->index();
            $table->integer('total_products')->default(0)->index();
            $table->integer('total_customers')->default(0)->index();
            $table->bigInteger('storage_used_bytes')->default(0)->index();
            
            // Lifecycle management
            $table->timestamp('trial_ends_at')->nullable()->index();
            $table->timestamp('subscription_ends_at')->nullable()->index();
            $table->timestamp('last_activity_at')->nullable()->index();
            $table->timestamp('suspended_at')->nullable();
            $table->text('suspension_reason')->nullable();
            
            $table->timestamps();
            
            // Performance indexes
            $table->index(['status', 'plan']);
            $table->index(['last_activity_at', 'status']);
            $table->index(['monthly_revenue', 'plan']);
        });

        // Custom domains per tenant (multi-domain support)
        Schema::create('tenant_domains', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->string('domain')->unique();
            $table->boolean('is_primary')->default(false)->index();
            $table->boolean('ssl_enabled')->default(false)->index();
            $table->string('ssl_status', 50)->default('pending');
            $table->timestamp('ssl_expires_at')->nullable();
            $table->timestamp('verified_at')->nullable()->index();
            $table->jsonb('dns_records')->nullable(); // Required DNS settings
            $table->jsonb('ssl_info')->nullable(); // Certificate details
            $table->timestamps();
            
            $table->index(['tenant_id', 'is_primary']);
            $table->index(['ssl_enabled', 'ssl_expires_at']);
        });

        // Database sharding configuration
        Schema::create('database_shards', function (Blueprint $table) {
            $table->id();
            $table->string('shard_name', 100)->unique();
            $table->string('shard_key', 50)->unique(); // For consistent hashing
            $table->text('connection_string'); // Encrypted connection details
            $table->string('region', 50)->index();
            $table->string('availability_zone', 50)->nullable();
            
            // Capacity management
            $table->integer('max_tenants')->default(1000);
            $table->integer('current_tenants')->default(0)->index();
            $table->decimal('storage_limit_gb', 10, 2)->default(1000);
            $table->decimal('storage_used_gb', 10, 2)->default(0)->index();
            $table->integer('cpu_cores')->default(4);
            $table->integer('memory_gb')->default(16);
            
            // Performance & health
            $table->string('status', 50)->default('active')->index();
            $table->decimal('avg_response_time_ms', 8, 3)->default(0);
            $table->decimal('cpu_usage_percent', 5, 2)->default(0);
            $table->decimal('memory_usage_percent', 5, 2)->default(0);
            $table->decimal('disk_usage_percent', 5, 2)->default(0);
            $table->jsonb('performance_metrics')->nullable();
            
            // Replication & backup
            $table->boolean('has_read_replicas')->default(false);
            $table->integer('read_replica_count')->default(0);
            $table->timestamp('last_backup_at')->nullable();
            $table->string('backup_status', 50)->nullable();
            
            $table->timestamps();
            
            $table->index(['region', 'status']);
            $table->index(['current_tenants', 'max_tenants']);
            $table->index(['status', 'avg_response_time_ms']);
        });

        // Tenant-to-shard mapping with migration history
        Schema::create('tenant_shard_mapping', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $table->foreignId('shard_id')->constrained('database_shards');
            $table->foreignId('previous_shard_id')->nullable()->constrained('database_shards');
            
            // Migration tracking
            $table->timestamp('mapped_at')->useCurrent();
            $table->timestamp('migrated_at')->nullable();
            $table->string('migration_status', 50)->default('active');
            $table->text('migration_notes')->nullable();
            $table->jsonb('migration_data')->nullable(); // Progress, errors, etc.
            
            $table->timestamps();
            
            $table->unique(['tenant_id']); // One active mapping per tenant
            $table->index(['shard_id', 'migration_status']);
            $table->index(['migrated_at', 'migration_status']);
        });

        // Tenant usage tracking for billing & analytics
        Schema::create('tenant_usage_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Metric details
            $table->string('metric_type', 50)->index();
            $table->string('metric_subtype', 100)->nullable(); // API endpoint, email type, etc.
            
            // Time period
            $table->string('period_type', 20)->index();
            $table->date('period_start')->index();
            $table->date('period_end')->index();
            
            // Usage data
            $table->bigInteger('usage_count')->default(0)->index();
            $table->decimal('usage_value', 15, 4)->default(0); // Cost, size, etc.
            $table->decimal('billable_amount', 15, 4)->default(0)->index();
            $table->jsonb('usage_details')->nullable(); // Breakdown by feature
            
            // Billing
            $table->boolean('is_billable')->default(true)->index();
            $table->boolean('is_overage')->default(false)->index();
            $table->decimal('overage_rate', 10, 6)->nullable();
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'metric_type', 'period_type', 'period_start']);
            $table->index(['period_start', 'metric_type', 'is_billable']);
            $table->index(['tenant_id', 'period_start', 'billable_amount']);
        });

        // Tenant activity & health monitoring
        Schema::create('tenant_health_metrics', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Performance metrics
            $table->string('metric_name', 100)->index(); // response_time, error_rate, etc.
            $table->decimal('metric_value', 15, 6);
            $table->decimal('threshold_warning', 15, 6)->nullable();
            $table->decimal('threshold_critical', 15, 6)->nullable();
            $table->string('status', 20)->default('healthy')->index();
            
            // Context
            $table->string('service_component', 100)->nullable(); // API, frontend, database, etc.
            $table->string('environment', 50)->default('production')->index();
            $table->jsonb('metadata')->nullable(); // Additional context
            
            $table->timestamp('measured_at')->useCurrent()->index();
            $table->timestamps();
            
            $table->index(['tenant_id', 'metric_name', 'measured_at']);
            $table->index(['status', 'measured_at']);
            $table->index(['metric_name', 'status', 'measured_at']);
        });

        // Global tenant settings & feature flags
        Schema::create('tenant_feature_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Feature management
            $table->string('feature_key', 100)->index(); // multi_currency, ai_recommendations, etc.
            $table->boolean('is_enabled')->default(false)->index();
            $table->jsonb('feature_config')->nullable(); // Feature-specific configuration
            $table->string('rollout_stage', 20)->default('stable');
            $table->decimal('rollout_percentage', 5, 2)->default(100); // Gradual rollout
            
            // Access control
            $table->boolean('is_plan_feature')->default(false); // Requires specific plan
            $table->jsonb('required_plans')->nullable(); // Which plans include this feature
            $table->decimal('addon_price', 10, 4)->nullable(); // Additional cost
            
            // Analytics
            $table->timestamp('first_enabled_at')->nullable();
            $table->timestamp('last_used_at')->nullable()->index();
            $table->bigInteger('usage_count')->default(0);
            
            $table->timestamps();
            
            $table->unique(['tenant_id', 'feature_key']);
            $table->index(['feature_key', 'is_enabled']);
            $table->index(['is_plan_feature', 'addon_price']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tenant_feature_flags');
        Schema::dropIfExists('tenant_health_metrics');
        Schema::dropIfExists('tenant_usage_metrics');
        Schema::dropIfExists('tenant_shard_mapping');
        Schema::dropIfExists('database_shards');
        Schema::dropIfExists('tenant_domains');
        Schema::dropIfExists('tenants');
    }
};
