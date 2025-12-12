<?php

namespace Cartino\Enums;

enum BusinessType: string
{
    case INDIVIDUAL = 'individual';
    case SOLE_PROPRIETORSHIP = 'sole_proprietorship';
    case PARTNERSHIP = 'partnership';
    case CORPORATION = 'corporation';
    case LLC = 'llc';
    case COOPERATIVE = 'cooperative';

    public function label(): string
    {
        return match ($this) {
            self::INDIVIDUAL => 'Individual',
            self::SOLE_PROPRIETORSHIP => 'Sole Proprietorship',
            self::PARTNERSHIP => 'Partnership',
            self::CORPORATION => 'Corporation',
            self::LLC => 'Limited Liability Company',
            self::COOPERATIVE => 'Cooperative',
        };
    }
}

enum BusinessCategory: string
{
    case MANUFACTURER = 'manufacturer';
    case WHOLESALER = 'wholesaler';
    case RETAILER = 'retailer';
    case DROPSHIPPER = 'dropshipper';
    case SERVICE_PROVIDER = 'service_provider';
    case DIGITAL_GOODS = 'digital_goods';

    public function label(): string
    {
        return match ($this) {
            self::MANUFACTURER => 'Manufacturer',
            self::WHOLESALER => 'Wholesaler',
            self::RETAILER => 'Retailer',
            self::DROPSHIPPER => 'Dropshipper',
            self::SERVICE_PROVIDER => 'Service Provider',
            self::DIGITAL_GOODS => 'Digital Goods',
        };
    }
}

enum VerificationStatus: string
{
    case UNVERIFIED = 'unverified';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::UNVERIFIED => 'Unverified',
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function canOperate(): bool
    {
        return $this === self::VERIFIED;
    }
}

enum KycStatus: string
{
    case NOT_REQUIRED = 'not_required';
    case PENDING = 'pending';
    case VERIFIED = 'verified';
    case REJECTED = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::NOT_REQUIRED => 'Not Required',
            self::PENDING => 'Pending',
            self::VERIFIED => 'Verified',
            self::REJECTED => 'Rejected',
        };
    }
}

enum CommissionType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case TIERED = 'tiered';
    case CATEGORY_BASED = 'category_based';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage',
            self::FIXED => 'Fixed Amount',
            self::TIERED => 'Tiered',
            self::CATEGORY_BASED => 'Category Based',
        };
    }
}

enum PayoutSchedule: string
{
    case DAILY = 'daily';
    case WEEKLY = 'weekly';
    case MONTHLY = 'monthly';
    case ON_DEMAND = 'on_demand';

    public function label(): string
    {
        return match ($this) {
            self::DAILY => 'Daily',
            self::WEEKLY => 'Weekly',
            self::MONTHLY => 'Monthly',
            self::ON_DEMAND => 'On Demand',
        };
    }

    public function daysBetweenPayouts(): int
    {
        return match ($this) {
            self::DAILY => 1,
            self::WEEKLY => 7,
            self::MONTHLY => 30,
            self::ON_DEMAND => 0,
        };
    }
}

enum VendorStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case BANNED = 'banned';
    case PENDING_APPROVAL = 'pending_approval';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::BANNED => 'Banned',
            self::PENDING_APPROVAL => 'Pending Approval',
        };
    }

    public function canSell(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum ProductApprovalStatus: string
{
    case DRAFT = 'draft';
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case NEEDS_REVISION = 'needs_revision';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::NEEDS_REVISION => 'Needs Revision',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::APPROVED;
    }
}

enum CommissionStatus: string
{
    case PENDING = 'pending';
    case APPROVED = 'approved';
    case PAID = 'paid';
    case DISPUTED = 'disputed';
    case REFUNDED = 'refunded';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::APPROVED => 'Approved',
            self::PAID => 'Paid',
            self::DISPUTED => 'Disputed',
            self::REFUNDED => 'Refunded',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::PAID, self::REFUNDED, self::CANCELLED]);
    }
}

enum PayoutMethod: string
{
    case BANK_TRANSFER = 'bank_transfer';
    case PAYPAL = 'paypal';
    case STRIPE = 'stripe';
    case WISE = 'wise';
    case CRYPTO = 'crypto';
    case CHECK = 'check';

    public function label(): string
    {
        return match ($this) {
            self::BANK_TRANSFER => 'Bank Transfer',
            self::PAYPAL => 'PayPal',
            self::STRIPE => 'Stripe',
            self::WISE => 'Wise',
            self::CRYPTO => 'Cryptocurrency',
            self::CHECK => 'Check',
        };
    }

    public function isInstant(): bool
    {
        return in_array($this, [self::PAYPAL, self::STRIPE, self::CRYPTO]);
    }
}

enum PayoutStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::COMPLETED, self::FAILED, self::CANCELLED]);
    }
}

enum ApiApplicationType: string
{
    case INTERNAL = 'internal';
    case PARTNER = 'partner';
    case PUBLIC = 'public';
    case WEBHOOK_ONLY = 'webhook_only';

    public function label(): string
    {
        return match ($this) {
            self::INTERNAL => 'Internal',
            self::PARTNER => 'Partner',
            self::PUBLIC => 'Public',
            self::WEBHOOK_ONLY => 'Webhook Only',
        };
    }
}

enum ApiEnvironment: string
{
    case DEVELOPMENT = 'development';
    case STAGING = 'staging';
    case PRODUCTION = 'production';

    public function label(): string
    {
        return match ($this) {
            self::DEVELOPMENT => 'Development',
            self::STAGING => 'Staging',
            self::PRODUCTION => 'Production',
        };
    }

    public function rateLimitMultiplier(): float
    {
        return match ($this) {
            self::DEVELOPMENT => 10.0,
            self::STAGING => 2.0,
            self::PRODUCTION => 1.0,
        };
    }
}

enum ApiStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case SUSPENDED = 'suspended';
    case REVOKED = 'revoked';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::SUSPENDED => 'Suspended',
            self::REVOKED => 'Revoked',
        };
    }

    public function canMakeRequests(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum WebhookFormat: string
{
    case JSON = 'json';
    case FORM = 'form';
    case XML = 'xml';

    public function label(): string
    {
        return match ($this) {
            self::JSON => 'JSON',
            self::FORM => 'Form Data',
            self::XML => 'XML',
        };
    }

    public function contentType(): string
    {
        return match ($this) {
            self::JSON => 'application/json',
            self::FORM => 'application/x-www-form-urlencoded',
            self::XML => 'application/xml',
        };
    }
}

enum WebhookStatus: string
{
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case DISABLED = 'disabled';
    case FAILED = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::DISABLED => 'Disabled',
            self::FAILED => 'Failed',
        };
    }

    public function canSend(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum WebhookFailureReason: string
{
    case TIMEOUT = 'timeout';
    case CONNECTION_ERROR = 'connection_error';
    case SSL_ERROR = 'ssl_error';
    case INVALID_RESPONSE = 'invalid_response';
    case RATE_LIMITED = 'rate_limited';
    case AUTHENTICATION_FAILED = 'authentication_failed';
    case SERVER_ERROR = 'server_error';

    public function label(): string
    {
        return match ($this) {
            self::TIMEOUT => 'Timeout',
            self::CONNECTION_ERROR => 'Connection Error',
            self::SSL_ERROR => 'SSL Error',
            self::INVALID_RESPONSE => 'Invalid Response',
            self::RATE_LIMITED => 'Rate Limited',
            self::AUTHENTICATION_FAILED => 'Authentication Failed',
            self::SERVER_ERROR => 'Server Error',
        };
    }

    public function isRetryable(): bool
    {
        return in_array($this, [
            self::TIMEOUT,
            self::CONNECTION_ERROR,
            self::RATE_LIMITED,
            self::SERVER_ERROR,
        ]);
    }
}
