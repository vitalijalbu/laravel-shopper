<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Category extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'parent_id',
        'sort_order',
        'is_enabled',
        'seo',
        'meta',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'sort_order' => 'integer',
        'seo' => 'array',
        'meta' => 'array',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order');
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('sort_order')->withTimestamps();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(800)
            ->height(600)
            ->sharpen(10);
    }

    public function getImageAttribute()
    {
        return $this->getFirstMediaUrl('image');
    }

    public function getThumbAttribute()
    {
        return $this->getFirstMediaUrl('image', 'thumb');
    }

    public function scopeEnabled($query)
    {
        return $query->where('is_enabled', true);
    }

    public function scopeParent($query)
    {
        return $query->whereNull('parent_id');
    }

    public function scopeChild($query)
    {
        return $query->whereNotNull('parent_id');
    }
}
