<?php

declare(strict_types=1);

namespace LaravelShopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'values',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'values' => 'array',
        'is_required' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_product_option')->withPivot('sort_order')->withTimestamps();
    }
}
