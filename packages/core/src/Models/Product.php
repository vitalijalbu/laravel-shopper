<?php

namespace VitaliJalbu\LaravelShopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'brand_id',
        'product_type_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'barcode',
        'status',
        'is_visible',
        'backorder',
        'requires_shipping',
        'track_quantity',
        'seo_title',
        'seo_description',
        'length',
        'width',
        'height',
        'weight',
        'dimension_unit',
        'weight_unit',
        'meta',
    ];

    protected $casts = [
        'is_visible' => 'boolean',
        'backorder' => 'boolean',
        'requires_shipping' => 'boolean',
        'track_quantity' => 'boolean',
        'length' => 'decimal:2',
        'width' => 'decimal:2',
        'height' => 'decimal:2',
        'weight' => 'decimal:3',
        'meta' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('shopper.database.table_prefix', 'shopper_') . 'products';
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(
            ProductOption::class,
            config('shopper.database.table_prefix', 'shopper_') . 'product_product_option'
        );
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(
            Category::class,
            config('shopper.database.table_prefix', 'shopper_') . 'category_product'
        );
    }

    public function collections(): BelongsToMany
    {
        return $this->belongsToMany(
            Collection::class,
            config('shopper.database.table_prefix', 'shopper_') . 'collection_product'
        );
    }

    public function prices(): MorphMany
    {
        return $this->morphMany(Price::class, 'priceable');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/gif', 'image/svg+xml']);

        $this->addMediaCollection('attachments');
    }

    public function registerMediaConversions(Media $media = null): void
    {
        $this->addMediaConversion('thumb')
            ->width(300)
            ->height(300)
            ->sharpen(10);

        $this->addMediaConversion('large')
            ->width(800)
            ->height(800)
            ->quality(85);
    }

    public function getFeaturedImageAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images');
    }

    public function getFeaturedImageThumbAttribute(): ?string
    {
        return $this->getFirstMediaUrl('images', 'thumb');
    }

    public function getDefaultVariantAttribute(): ?ProductVariant
    {
        return $this->variants()->first();
    }

    public function getPriceAttribute(): ?int
    {
        return $this->defaultVariant?->price;
    }

    public function getFormattedPriceAttribute(): ?string
    {
        $defaultCurrency = Currency::getDefault();
        if (!$defaultCurrency || !$this->price) {
            return null;
        }

        return $defaultCurrency->formatAmount($this->price);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_visible;
    }

    public function inStock(): bool
    {
        return $this->variants()->where('quantity', '>', 0)->exists();
    }
}
