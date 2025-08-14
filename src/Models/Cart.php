<?php

declare(strict_types=1);

namespace LaravelShopper\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cart extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'customer_id',
        'currency_id',
        'subtotal',
        'tax_total',
        'shipping_total',
        'discount_total',
        'total',
        'applied_discounts',
        'shipping_address',
        'billing_address',
        'status',
        'expires_at',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax_total' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'discount_total' => 'decimal:2',
        'total' => 'decimal:2',
        'applied_discounts' => 'array',
        'shipping_address' => 'array',
        'billing_address' => 'array',
        'expires_at' => 'datetime',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class);
    }

    public function lines(): HasMany
    {
        return $this->hasMany(CartLine::class);
    }

    public function getTotalQuantityAttribute(): int
    {
        return $this->lines->sum('quantity');
    }

    public function calculateTotals(): void
    {
        $this->subtotal = $this->lines->sum('line_total');
        $this->total = $this->subtotal + $this->tax_total + $this->shipping_total - $this->discount_total;
        $this->save();
    }

    public function addItem(Product $product, int $quantity = 1, ?ProductVariant $variant = null, array $options = []): CartLine
    {
        $existingLine = $this->lines()
            ->where('product_id', $product->id)
            ->where('product_variant_id', $variant?->id)
            ->first();

        $unitPrice = $variant ? $variant->price : $product->price;
        $lineTotal = $unitPrice * $quantity;

        if ($existingLine) {
            $existingLine->update([
                'quantity' => $existingLine->quantity + $quantity,
                'line_total' => $existingLine->line_total + $lineTotal,
            ]);
            
            $this->calculateTotals();
            return $existingLine;
        }

        $line = $this->lines()->create([
            'product_id' => $product->id,
            'product_variant_id' => $variant?->id,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'line_total' => $lineTotal,
            'product_options' => $options,
        ]);

        $this->calculateTotals();
        return $line;
    }

    public function removeItem(CartLine $line): bool
    {
        $result = $line->delete();
        $this->calculateTotals();
        return $result;
    }

    public function clear(): bool
    {
        $result = $this->lines()->delete();
        $this->update([
            'subtotal' => 0,
            'tax_total' => 0,
            'shipping_total' => 0,
            'discount_total' => 0,
            'total' => 0,
        ]);
        return $result;
    }

    public function isEmpty(): bool
    {
        return $this->lines->isEmpty();
    }
}
