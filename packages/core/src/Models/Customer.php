<?php

namespace VitaliJalbu\LaravelShopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'email_verified_at',
        'phone',
        'date_of_birth',
        'gender',
        'meta',
    ];

    protected $casts = [
        'email_verified_at' => 'timestamp',
        'date_of_birth' => 'date',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('shopper.database.table_prefix', 'shopper_') . 'customers';
    }

    public function customerGroups(): BelongsToMany
    {
        return $this->belongsToMany(
            CustomerGroup::class,
            config('shopper.database.table_prefix', 'shopper_') . 'customer_customer_group'
        );
    }

    public function addresses(): MorphMany
    {
        return $this->morphMany(Address::class, 'addressable');
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    public function getDefaultShippingAddressAttribute(): ?Address
    {
        return $this->addresses()
            ->where('type', 'shipping')
            ->where('default', true)
            ->first();
    }

    public function getDefaultBillingAddressAttribute(): ?Address
    {
        return $this->addresses()
            ->where('type', 'billing')
            ->where('default', true)
            ->first();
    }
}
