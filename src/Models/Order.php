<?php

declare(strict_types=1);

namespace VitaliJalbu\LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'number',
        'user_id',
        'customer_id',
        'channel_id',
        'currency_code',
        'status',
        'reference',
        'customer_reference',
        'sub_total',
        'tax_total',
        'discount_total',
        'shipping_total',
        'total',
        'notes',
        'currency_code',
        'compare_currency_code',
        'exchange_rate',
        'shipping_option',
        'shipping_address',
        'billing_address',
        'placed_at',
        'meta',
    ];

    protected $casts = [
        'sub_total' => 'integer',
        'tax_total' => 'integer',
        'discount_total' => 'integer',
        'shipping_total' => 'integer',
        'total' => 'integer',
        'exchange_rate' => 'decimal:6',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'placed_at' => 'datetime',
        'meta' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('orders');
        parent::__construct($attributes);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(OrderAddress::class);
    }

    public function getSubTotalAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }

    public function setSubTotalAttribute($value)
    {
        $this->attributes['sub_total'] = $value ? $value * 100 : 0;
    }

    public function getTaxTotalAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }

    public function setTaxTotalAttribute($value)
    {
        $this->attributes['tax_total'] = $value ? $value * 100 : 0;
    }

    public function getDiscountTotalAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }

    public function setDiscountTotalAttribute($value)
    {
        $this->attributes['discount_total'] = $value ? $value * 100 : 0;
    }

    public function getShippingTotalAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }

    public function setShippingTotalAttribute($value)
    {
        $this->attributes['shipping_total'] = $value ? $value * 100 : 0;
    }

    public function getTotalAttribute($value)
    {
        return $value ? $value / 100 : 0;
    }

    public function setTotalAttribute($value)
    {
        $this->attributes['total'] = $value ? $value * 100 : 0;
    }

    public function getFormattedSubTotalAttribute()
    {
        return number_format($this->sub_total, 2);
    }

    public function getFormattedTotalAttribute()
    {
        return number_format($this->total, 2);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeRecent($query)
    {
        return $query->orderBy('created_at', 'desc');
    }
}
