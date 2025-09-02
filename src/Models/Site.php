<?php

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Model;
use Shopper\Support\HasHandle;

class Site extends Model
{
    use HasHandle;

    protected $fillable = [
        'handle',
        'name',
        'url',
        'locale',
        'lang',
        'attributes',
        'order',
        'is_enabled',
    ];

    protected $casts = [
        'attributes' => 'array',
        'is_enabled' => 'boolean',
        'order' => 'integer',
    ];

    /**
     * Scope to enabled sites
     */
    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    /**
     * Scope to default site
     */
    public function scopeDefault($query)
    {
        return $query->enabled()->orderBy('order')->limit(1);
    }

    /**
     * Get site by handle or default
     */
    public static function findByHandle(?string $handle = null)
    {
        if ($handle) {
            return static::where('handle', $handle)->enabled()->first();
        }

        return static::default()->first();
    }

    /**
     * Get the route key name for Laravel model route binding.
     */
    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    /**
     * Check if this is the current site
     */
    public function isCurrent(): bool
    {
        return $this->handle === request()->segment(1) ||
               ($this->order === 1 && ! request()->segment(1));
    }

    /**
     * Get site URL with path
     */
    public function urlTo(string $path = ''): string
    {
        return rtrim($this->url, '/').'/'.ltrim($path, '/');
    }

    /**
     * Get all channels for this site
     */
    public function channels()
    {
        return $this->hasMany(Channel::class);
    }

    /**
     * Get all products for this site
     */
    public function products()
    {
        return $this->hasMany(Product::class);
    }

    /**
     * Get all customers for this site
     */
    public function customers()
    {
        return $this->hasMany(Customer::class);
    }

    /**
     * Get all orders for this site
     */
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
