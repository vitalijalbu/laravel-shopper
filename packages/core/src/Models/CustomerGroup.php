<?php

namespace VitaliJalbu\LaravelShopper\Core\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'handle',
        'default',
    ];

    protected $casts = [
        'default' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function getTable(): string
    {
        return config('shopper.database.table_prefix', 'shopper_') . 'customer_groups';
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public static function getDefault(): ?self
    {
        return static::where('default', true)->first();
    }
}
