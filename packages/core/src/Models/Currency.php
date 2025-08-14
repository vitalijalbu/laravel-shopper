<?php

namespace VitaliJalbu\LaravelShopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Currency extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'exchange_rate',
        'default',
        'enabled',
    ];

    protected $casts = [
        'exchange_rate' => 'decimal:4',
        'default' => 'boolean',
        'enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('shopper.database.table_prefix', 'shopper_') . 'currencies';
    }

    public function carts(): HasMany
    {
        return $this->hasMany(Cart::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public static function getDefault(): ?self
    {
        return static::where('default', true)->first();
    }

    public function formatAmount(int $amountInCents): string
    {
        $amount = $amountInCents / 100;
        
        return match ($this->code) {
            'USD' => '$' . number_format($amount, 2),
            'EUR' => '€' . number_format($amount, 2, ',', '.'),
            'GBP' => '£' . number_format($amount, 2),
            'JPY' => '¥' . number_format($amount, 0),
            default => $this->code . ' ' . number_format($amount, 2),
        };
    }
}
