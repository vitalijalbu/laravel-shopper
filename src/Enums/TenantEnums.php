<?php

namespace Shopper\Enums;

enum TenantPlan: string
{
    case STARTER = 'starter';
    case PRO = 'pro';
    case ENTERPRISE = 'enterprise';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::STARTER => 'Starter',
            self::PRO => 'Professional',
            self::ENTERPRISE => 'Enterprise',
            self::CUSTOM => 'Custom',
        };
    }

    public function monthlyPrice(): float
    {
        return match ($this) {
            self::STARTER => 29.99,
            self::PRO => 99.99,
            self::ENTERPRISE => 299.99,
            self::CUSTOM => 0, // Custom pricing
        };
    }
}

enum TenantStatus: string
{
    case ACTIVE = 'active';
    case SUSPENDED = 'suspended';
    case CANCELLED = 'cancelled';
    case TRIAL = 'trial';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::SUSPENDED => 'Suspended',
            self::CANCELLED => 'Cancelled',
            self::TRIAL => 'Trial',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE || $this === self::TRIAL;
    }
}

enum SslStatus: string
{
    case PENDING = 'pending';
    case ACTIVE = 'active';
    case FAILED = 'failed';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::ACTIVE => 'Active',
            self::FAILED => 'Failed',
            self::EXPIRED => 'Expired',
        };
    }
}

enum ShardStatus: string
{
    case ACTIVE = 'active';
    case READONLY = 'readonly';
    case MAINTENANCE = 'maintenance';
    case DEGRADED = 'degraded';
    case OFFLINE = 'offline';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::READONLY => 'Read Only',
            self::MAINTENANCE => 'Maintenance',
            self::DEGRADED => 'Degraded',
            self::OFFLINE => 'Offline',
        };
    }

    public function isOperational(): bool
    {
        return in_array($this, [self::ACTIVE, self::READONLY]);
    }
}

enum BackupStatus: string
{
    case SUCCESS = 'success';
    case FAILED = 'failed';
    case IN_PROGRESS = 'in_progress';

    public function label(): string
    {
        return match ($this) {
            self::SUCCESS => 'Success',
            self::FAILED => 'Failed',
            self::IN_PROGRESS => 'In Progress',
        };
    }
}

enum MigrationStatus: string
{
    case ACTIVE = 'active';
    case MIGRATING = 'migrating';
    case ROLLBACK = 'rollback';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::MIGRATING => 'Migrating',
            self::ROLLBACK => 'Rolling Back',
            self::FAILED => 'Failed',
        };
    }
}

enum MetricType: string
{
    case ORDERS = 'orders';
    case PRODUCTS = 'products';
    case CUSTOMERS = 'customers';
    case STORAGE = 'storage';
    case BANDWIDTH = 'bandwidth';
    case API_CALLS = 'api_calls';
    case EMAILS_SENT = 'emails_sent';
    case SMS_SENT = 'sms_sent';
    case TRANSACTIONS = 'transactions';

    public function label(): string
    {
        return match ($this) {
            self::ORDERS => 'Orders',
            self::PRODUCTS => 'Products',
            self::CUSTOMERS => 'Customers',
            self::STORAGE => 'Storage',
            self::BANDWIDTH => 'Bandwidth',
            self::API_CALLS => 'API Calls',
            self::EMAILS_SENT => 'Emails Sent',
            self::SMS_SENT => 'SMS Sent',
            self::TRANSACTIONS => 'Transactions',
        };
    }
}

enum PeriodType: string
{
    case HOUR = 'hour';
    case DAY = 'day';
    case WEEK = 'week';
    case MONTH = 'month';
    case QUARTER = 'quarter';
    case YEAR = 'year';

    public function label(): string
    {
        return match ($this) {
            self::HOUR => 'Hour',
            self::DAY => 'Day',
            self::WEEK => 'Week',
            self::MONTH => 'Month',
            self::QUARTER => 'Quarter',
            self::YEAR => 'Year',
        };
    }
}

enum HealthStatus: string
{
    case HEALTHY = 'healthy';
    case WARNING = 'warning';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::HEALTHY => 'Healthy',
            self::WARNING => 'Warning',
            self::CRITICAL => 'Critical',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::HEALTHY => 'green',
            self::WARNING => 'yellow',
            self::CRITICAL => 'red',
        };
    }
}

enum RolloutStage: string
{
    case DEV = 'dev';
    case BETA = 'beta';
    case STABLE = 'stable';
    case DEPRECATED = 'deprecated';

    public function label(): string
    {
        return match ($this) {
            self::DEV => 'Development',
            self::BETA => 'Beta',
            self::STABLE => 'Stable',
            self::DEPRECATED => 'Deprecated',
        };
    }
}
