<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'date_of_birth',
        'gender',
        'email_verified_at',
        'password',
        'is_enabled',
        'last_login_at',
        'last_login_ip',
        'avatar',
        'meta',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'email_verified_at' => 'datetime',
        'is_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'meta' => 'array',
        'password' => 'hashed',
    ];

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(CustomerGroup::class, 'customer_customer_group');
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

    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function getDefaultWishlistAttribute(): ?Wishlist
    {
        return $this->wishlists()->where('is_default', true)->first();
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }
}
