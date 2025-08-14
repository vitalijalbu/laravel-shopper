<?php

declare(strict_types=1);

namespace LaravelShopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use LaravelShopper\Support\HasHandle;
use LaravelShopper\Support\HasSite;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;
    use HasHandle;
    use HasSite;

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
        'is_enabled',
        'is_featured',
        'status',
        'brand_id',
        'product_type_id',
        'seo',
        'meta',
        'published_at',
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
        'is_enabled' => 'boolean',
        'is_featured' => 'boolean',
        'seo' => 'array',
        'meta' => 'array',
        'published_at' => 'datetime',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function productType(): BelongsTo
    {
        return $this->belongsTo(ProductType::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class)->withPivot('sort_order')->withTimestamps();
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
