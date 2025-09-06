<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Shopper\Support\HasHandle;
use Shopper\Support\HasSite;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Shopper\Traits\HasOptimizedFilters;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use HasHandle;
    use HasSite;
    use InteractsWithMedia;
    use SoftDeletes;
    use HasOptimizedFilters;

    protected $fillable = [
        'site_id',
        'name',
        'slug',
        'description',
        'short_description',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_quantity',
        'allow_out_of_stock_purchases',
        'stock_status',
        'weight',
        'dimensions',
        'is_physical',
        'is_digital',
        'requires_shipping',
        'is_featured',
        'status',
        'brand_id',
        'product_type_id',
        'seo',
        'meta',
        'published_at',
        'average_rating',
        'review_count',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'integer',
        'track_quantity' => 'boolean',
        'allow_out_of_stock_purchases' => 'boolean',
        'weight' => 'decimal:2',
        'dimensions' => 'array',
        'is_physical' => 'boolean',
        'is_digital' => 'boolean',
        'requires_shipping' => 'boolean',
        'is_featured' => 'boolean',
        'seo' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
        'average_rating' => 'decimal:2',
        'review_count' => 'integer',
    ];

    /**
     * Fields that should always be eager loaded (N+1 protection)
     */
    protected static array $defaultEagerLoad = [
        'brand:id,name,slug',
        'productType:id,name',
    ];

    /**
     * Fields that can be filtered
     */
    protected static array $filterable = [
        'id',
        'name',
        'slug',
        'sku',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'track_quantity',
        'stock_status',
        'weight',
        'is_physical',
        'is_digital',
        'requires_shipping',
        'is_featured',
        'status',
        'brand_id',
        'product_type_id',
        'published_at',
        'created_at',
        'updated_at',
        'average_rating',
        'review_count',
    ];

    /**
     * Fields that can be sorted
     */
    protected static array $sortable = [
        'id',
        'name',
        'price',
        'compare_price',
        'cost_price',
        'stock_quantity',
        'weight',
        'is_featured',
        'status',
        'published_at',
        'created_at',
        'updated_at',
        'average_rating',
        'review_count',
    ];

    /**
     * Fields that can be searched
     */
    protected static array $searchable = [
        'name',
        'description',
        'short_description',
        'sku',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function options(): BelongsToMany
    {
        return $this->belongsToMany(ProductOption::class, 'product_product_option')->withPivot('sort_order')->withTimestamps();
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function cartLines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function orderLines(): HasMany
    {
        return $this->hasMany(OrderLine::class);
    }

    public function wishlistItems(): HasMany
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function favorites(): MorphMany
    {
        return $this->morphMany(Favorite::class, 'favoriteable');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function approvedReviews(): HasMany
    {
        return $this->hasMany(ProductReview::class)->where('is_approved', true);
    }

    public function isFavoritedBy(Customer $customer): bool
    {
        return $this->favorites()->where('customer_id', $customer->id)->exists();
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
        $this->addMediaCollection('images')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);

        $this->addMediaCollection('gallery')
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/webp']);
    }
}
