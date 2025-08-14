<?php

declare(strict_types=1);

namespace VitaliJalbu\LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    protected $fillable = [
        'name',
        'iso3',
        'iso2',
        'phonecode',
        'capital',
        'currency_id',
        'currency_symbol',
        'tld',
        'native',
        'region',
        'subregion',
        'timezones',
        'translations',
        'latitude',
        'longitude',
        'emoji',
        'emojiU',
    ];

    protected $casts = [
        'timezones' => 'array',
        'translations' => 'array',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('countries');
        parent::__construct($attributes);
    }

    public function getRouteKeyName(): string
    {
        return 'iso2';
    }

    public function currency()
    {
        return $this->belongsTo(Currency::class);
    }

    public function states(): HasMany
    {
        return $this->hasMany(State::class);
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'shipping_country_id');
    }
}
