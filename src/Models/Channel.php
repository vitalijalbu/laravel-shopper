<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Channel extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'url',
        'is_default',
        'is_enabled',
        'settings',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'is_enabled' => 'boolean',
        'settings' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }
}
