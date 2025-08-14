<?php

declare(strict_types=1);

namespace VitaliJalbu\LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'channel_id',
        'currency_code',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('carts');
        parent::__construct($attributes);
    }

    public function user()
    {
        return $this->belongsTo(config('shopper.auth.model', 'App\\Models\\User'));
    }

    public function channel(): BelongsTo
    {
        return $this->belongsTo(Channel::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CartAddress::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->lines->sum('quantity');
    }

    public function getSubTotalAttribute(): float
    {
        return $this->lines->sum(function ($line) {
            return $line->quantity * $line->unit_price;
        }) / 100;
    }

    public function getTaxTotalAttribute(): float
    {
        return $this->lines->sum(function ($line) {
            return $line->quantity * $line->unit_price * ($line->tax_rate / 100);
        }) / 100;
    }

    public function getTotalAttribute(): float
    {
        return $this->sub_total + $this->tax_total;
    }

    public function getFormattedSubTotalAttribute(): string
    {
        return number_format($this->sub_total, 2);
    }

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 2);
    }

    public function addLine(Product $product, int $quantity = 1, array $meta = []): CartLine
    {
        $existingLine = $this->lines()
            ->where('purchasable_type', get_class($product))
            ->where('purchasable_id', $product->id)
            ->first();

        if ($existingLine) {
            $existingLine->update([
                'quantity' => $existingLine->quantity + $quantity,
                'meta' => array_merge($existingLine->meta ?? [], $meta),
            ]);
            
            return $existingLine;
        }

        return $this->lines()->create([
            'purchasable_type' => get_class($product),
            'purchasable_id' => $product->id,
            'quantity' => $quantity,
            'unit_price' => $product->price,
            'tax_rate' => $product->tax_rate ?? 0,
            'meta' => $meta,
        ]);
    }

    public function removeLine(CartLine $line): bool
    {
        return $line->delete();
    }

    public function clear(): bool
    {
        $this->lines()->delete();
        $this->addresses()->delete();
        
        return true;
    }
}
