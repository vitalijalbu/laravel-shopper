<?php

declare(strict_types=1);

namespace VitaliJalbu\LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Channel extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'url',
        'default',
        'enabled',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'default' => 'boolean',
        'enabled' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('channels');
        parent::__construct($attributes);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function scopeEnabled($query)
    {
        return $query->where('enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('default', true);
    }
}
