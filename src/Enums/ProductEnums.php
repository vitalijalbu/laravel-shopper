<?php

namespace Shopper\Enums;

enum InventoryLocationType: string
{
    case WAREHOUSE = 'warehouse';
    case STORE = 'store';
    case DROPSHIP = 'dropship';
    case VENDOR = 'vendor';

    public function label(): string
    {
        return match ($this) {
            self::WAREHOUSE => 'Warehouse',
            self::STORE => 'Store',
            self::DROPSHIP => 'Dropship',
            self::VENDOR => 'Vendor',
        };
    }
}

enum MetafieldValueType: string
{
    case STRING = 'string';
    case INTEGER = 'integer';
    case DECIMAL = 'decimal';
    case BOOLEAN = 'boolean';
    case JSON = 'json';
    case DATE = 'date';
    case URL = 'url';
    case EMAIL = 'email';

    public function label(): string
    {
        return match ($this) {
            self::STRING => 'String',
            self::INTEGER => 'Integer',
            self::DECIMAL => 'Decimal',
            self::BOOLEAN => 'Boolean',
            self::JSON => 'JSON',
            self::DATE => 'Date',
            self::URL => 'URL',
            self::EMAIL => 'Email',
        };
    }

    public function cast($value): mixed
    {
        return match ($this) {
            self::STRING => (string) $value,
            self::INTEGER => (int) $value,
            self::DECIMAL => (float) $value,
            self::BOOLEAN => (bool) $value,
            self::JSON => json_decode($value, true),
            self::DATE => $value instanceof \DateTime ? $value : new \DateTime($value),
            self::URL, self::EMAIL => (string) $value,
        };
    }
}

enum ProductRelationType: string
{
    case UPSELL = 'upsell';
    case CROSS_SELL = 'cross_sell';
    case RELATED = 'related';
    case ALTERNATIVE = 'alternative';
    case ACCESSORY = 'accessory';

    public function label(): string
    {
        return match ($this) {
            self::UPSELL => 'Upsell',
            self::CROSS_SELL => 'Cross-sell',
            self::RELATED => 'Related',
            self::ALTERNATIVE => 'Alternative',
            self::ACCESSORY => 'Accessory',
        };
    }
}

enum AttributeType: string
{
    case TEXT = 'text';
    case NUMBER = 'number';
    case BOOLEAN = 'boolean';
    case SELECT = 'select';
    case MULTISELECT = 'multiselect';
    case COLOR = 'color';
    case IMAGE = 'image';

    public function label(): string
    {
        return match ($this) {
            self::TEXT => 'Text',
            self::NUMBER => 'Number',
            self::BOOLEAN => 'Boolean',
            self::SELECT => 'Select',
            self::MULTISELECT => 'Multi-select',
            self::COLOR => 'Color',
            self::IMAGE => 'Image',
        };
    }
}

enum PricingRuleType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case BULK = 'bulk';
    case TIERED = 'tiered';
    case BOGO = 'bogo';
    case CONDITIONAL = 'conditional';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage Discount',
            self::FIXED => 'Fixed Amount',
            self::BULK => 'Bulk Discount',
            self::TIERED => 'Tiered Pricing',
            self::BOGO => 'Buy One Get One',
            self::CONDITIONAL => 'Conditional',
        };
    }
}

enum FulfillmentStatus: string
{
    case PENDING = 'pending';
    case IN_PROGRESS = 'in_progress';
    case SHIPPED = 'shipped';
    case DELIVERED = 'delivered';
    case FAILED = 'failed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::IN_PROGRESS => 'In Progress',
            self::SHIPPED => 'Shipped',
            self::DELIVERED => 'Delivered',
            self::FAILED => 'Failed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isComplete(): bool
    {
        return in_array($this, [self::DELIVERED, self::FAILED, self::CANCELLED]);
    }
}

enum ReturnStatus: string
{
    case REQUESTED = 'requested';
    case APPROVED = 'approved';
    case REJECTED = 'rejected';
    case IN_TRANSIT = 'in_transit';
    case RECEIVED = 'received';
    case PROCESSED = 'processed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::REQUESTED => 'Requested',
            self::APPROVED => 'Approved',
            self::REJECTED => 'Rejected',
            self::IN_TRANSIT => 'In Transit',
            self::RECEIVED => 'Received',
            self::PROCESSED => 'Processed',
            self::REFUNDED => 'Refunded',
        };
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::REJECTED, self::REFUNDED]);
    }
}

enum ReturnReason: string
{
    case DEFECTIVE = 'defective';
    case WRONG_ITEM = 'wrong_item';
    case NOT_AS_DESCRIBED = 'not_as_described';
    case CHANGED_MIND = 'changed_mind';
    case DAMAGED = 'damaged';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::DEFECTIVE => 'Defective',
            self::WRONG_ITEM => 'Wrong Item',
            self::NOT_AS_DESCRIBED => 'Not as Described',
            self::CHANGED_MIND => 'Changed Mind',
            self::DAMAGED => 'Damaged',
            self::OTHER => 'Other',
        };
    }

    public function isVendorFault(): bool
    {
        return in_array($this, [self::DEFECTIVE, self::WRONG_ITEM, self::NOT_AS_DESCRIBED, self::DAMAGED]);
    }
}

enum ProductCondition: string
{
    case NEW = 'new';
    case OPENED = 'opened';
    case DAMAGED = 'damaged';
    case DEFECTIVE = 'defective';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'New',
            self::OPENED => 'Opened',
            self::DAMAGED => 'Damaged',
            self::DEFECTIVE => 'Defective',
        };
    }

    public function refundPercentage(): int
    {
        return match ($this) {
            self::NEW => 100,
            self::OPENED => 90,
            self::DAMAGED => 50,
            self::DEFECTIVE => 0,
        };
    }
}

enum ShippingCalculationType: string
{
    case FLAT_RATE = 'flat_rate';
    case WEIGHT_BASED = 'weight_based';
    case PRICE_BASED = 'price_based';
    case ITEM_COUNT = 'item_count';
    case CALCULATED = 'calculated';

    public function label(): string
    {
        return match ($this) {
            self::FLAT_RATE => 'Flat Rate',
            self::WEIGHT_BASED => 'Weight Based',
            self::PRICE_BASED => 'Price Based',
            self::ITEM_COUNT => 'Item Count',
            self::CALCULATED => 'Calculated',
        };
    }
}

enum CustomerSegmentType: string
{
    case STATIC = 'static';
    case DYNAMIC = 'dynamic';
    case SMART = 'smart';

    public function label(): string
    {
        return match ($this) {
            self::STATIC => 'Static',
            self::DYNAMIC => 'Dynamic',
            self::SMART => 'Smart (AI)',
        };
    }
}

enum ReviewMediaType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';

    public function label(): string
    {
        return match ($this) {
            self::IMAGE => 'Image',
            self::VIDEO => 'Video',
        };
    }
}

enum CampaignType: string
{
    case SALE = 'sale';
    case FLASH_SALE = 'flash_sale';
    case CLEARANCE = 'clearance';
    case SEASONAL = 'seasonal';
    case NEW_PRODUCT = 'new_product';
    case ABANDONED_CART = 'abandoned_cart';

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Sale',
            self::FLASH_SALE => 'Flash Sale',
            self::CLEARANCE => 'Clearance',
            self::SEASONAL => 'Seasonal',
            self::NEW_PRODUCT => 'New Product',
            self::ABANDONED_CART => 'Abandoned Cart',
        };
    }
}

enum CampaignStatus: string
{
    case DRAFT = 'draft';
    case SCHEDULED = 'scheduled';
    case ACTIVE = 'active';
    case PAUSED = 'paused';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::SCHEDULED => 'Scheduled',
            self::ACTIVE => 'Active',
            self::PAUSED => 'Paused',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum GiftCardTransactionType: string
{
    case PURCHASE = 'purchase';
    case REDEEM = 'redeem';
    case REFUND = 'refund';
    case EXPIRE = 'expire';
    case TRANSFER = 'transfer';

    public function label(): string
    {
        return match ($this) {
            self::PURCHASE => 'Purchase',
            self::REDEEM => 'Redeem',
            self::REFUND => 'Refund',
            self::EXPIRE => 'Expire',
            self::TRANSFER => 'Transfer',
        };
    }

    public function affectsBalance(): bool
    {
        return in_array($this, [self::PURCHASE, self::REDEEM, self::REFUND]);
    }
}

// Enums per existing tables
enum CartStatus: string
{
    case ACTIVE = 'active';
    case ABANDONED = 'abandoned';
    case CONVERTED = 'converted';
    case EXPIRED = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::ABANDONED => 'Abandoned',
            self::CONVERTED => 'Converted',
            self::EXPIRED => 'Expired',
        };
    }
}

enum TaxType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage',
            self::FIXED => 'Fixed Amount',
        };
    }
}

enum EntryStatus: string
{
    case DRAFT = 'draft';
    case PUBLISHED = 'published';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::PUBLISHED => 'Published',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::PUBLISHED;
    }
}

enum Gender: string
{
    case MALE = 'male';
    case FEMALE = 'female';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::MALE => 'Male',
            self::FEMALE => 'Female',
            self::OTHER => 'Other',
        };
    }
}

enum StockStatus: string
{
    case IN_STOCK = 'in_stock';
    case OUT_OF_STOCK = 'out_of_stock';
    case ON_BACKORDER = 'on_backorder';

    public function label(): string
    {
        return match ($this) {
            self::IN_STOCK => 'In Stock',
            self::OUT_OF_STOCK => 'Out of Stock',
            self::ON_BACKORDER => 'On Backorder',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this, [self::IN_STOCK, self::ON_BACKORDER]);
    }
}

enum ProductStatus: string
{
    case ACTIVE = 'active';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::DRAFT => 'Draft',
            self::ARCHIVED => 'Archived',
        };
    }

    public function isPublic(): bool
    {
        return $this === self::ACTIVE;
    }
}

enum CustomerStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
    case SUSPENDED = 'suspended';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Active',
            self::INACTIVE => 'Inactive',
            self::PENDING => 'Pending',
            self::SUSPENDED => 'Suspended',
        };
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
