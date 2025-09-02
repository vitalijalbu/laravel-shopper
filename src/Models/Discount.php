<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'description',
        'type',
        'value',
        'minimum_order_amount',
        'maximum_discount_amount',
        'usage_limit',
        'usage_limit_per_customer',
        'usage_count',
        'is_enabled',
        'starts_at',
        'expires_at',
        'eligible_products',
        'eligible_categories',
        'eligible_customers',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'minimum_order_amount' => 'decimal:2',
        'maximum_discount_amount' => 'decimal:2',
        'usage_limit' => 'integer',
        'usage_limit_per_customer' => 'integer',
        'usage_count' => 'integer',
        'is_enabled' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'eligible_products' => 'array',
        'eligible_categories' => 'array',
        'eligible_customers' => 'array',
    ];

    public function isActive(): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        $now = now();

        if ($this->starts_at && $now->isBefore($this->starts_at)) {
            return false;
        }

        if ($this->expires_at && $now->isAfter($this->expires_at)) {
            return false;
        }

        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function calculateDiscount(float $amount): float
    {
        if (! $this->isActive()) {
            return 0;
        }

        if ($this->minimum_order_amount && $amount < $this->minimum_order_amount) {
            return 0;
        }

        $discount = match ($this->type) {
            'percentage' => $amount * ($this->value / 100),
            'fixed_amount' => $this->value,
            'free_shipping' => 0, // Handle shipping discount separately
            default => 0,
        };

        if ($this->maximum_discount_amount && $discount > $this->maximum_discount_amount) {
            $discount = $this->maximum_discount_amount;
        }

        return round($discount, 2);
    }
}
