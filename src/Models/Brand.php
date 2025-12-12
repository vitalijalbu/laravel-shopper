<?php

declare(strict_types=1);

namespace Cartino\Models;

use Cartino\Traits\HasCustomFields;
use Cartino\Traits\HasOptimizedFilters;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Brand extends Model implements HasMedia
{
    use HasCustomFields;
    use HasFactory;
    use HasOptimizedFilters;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'website',
        'is_enabled',
        'seo',
        'meta',
        'data',
    ];

    protected $casts = [
        'is_enabled' => 'boolean',
        'seo' => 'array',
        'meta' => 'array',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'name',
        'slug',
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'name',
        'slug',
        'is_enabled',
        'created_at',
        'updated_at',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'name',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
