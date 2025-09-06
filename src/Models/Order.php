<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Shopper\Traits\HasOptimizedFilters;
use Shopper\Traits\HasCustomFields;

class Order extends Model
{
    use HasFactory;
    use SoftDeletes;
    use HasOptimizedFilters;
    use HasCustomFields;

    protected $fillable = [
        'order_number',
        'customer_id',
        'customer_email',
        'customer_details',
        'currency_id',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'total',
        'status',
        'payment_status',
        'fulfillment_status',
        'shipping_address',
        'billing_address',
        'applied_discounts',
        'shipping_method',
        'payment_method',
        'payment_details',
        'notes',
        'shipped_at',
        'delivered_at',
        'data',
    ];

    protected $casts = [
        'customer_details' => 'array',
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'applied_discounts' => 'array',
        'payment_details' => 'array',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [
        'customer:id,first_name,last_name,email',
        'currency:id,code,symbol',
    ];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'order_number',
        'customer_id',
        'customer_email',
        'currency_id',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'total',
        'status',
        'payment_status',
        'fulfillment_status',
        'created_at',
        'updated_at',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'order_number',
        'total',
        'status',
        'payment_status',
        'fulfillment_status',
        'created_at',
        'updated_at',
        'shipped_at',
        'delivered_at',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'order_number',
        'customer_email',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function getTotalItemsAttribute(): int
    {
        return $this->lines->sum('quantity');
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function isFulfilled(): bool
    {
        return $this->fulfillment_status === 'fulfilled';
    }

    public function isShipped(): bool
    {
        return $this->fulfillment_status === 'shipped';
    }

    public function isDelivered(): bool
    {
        return $this->fulfillment_status === 'delivered';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function canBeCancelled(): bool
    {
        return in_array($this->status, ['pending', 'confirmed']) &&
               $this->payment_status !== 'paid';
    }

    public function canBeShipped(): bool
    {
        return $this->status === 'confirmed' &&
               $this->payment_status === 'paid' &&
               $this->fulfillment_status === 'unfulfilled';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = 'ORD-'.strtoupper(uniqid());
            }
        });
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByPaymentStatus($query, string $status)
    {
        return $query->where('payment_status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
