<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MetaobjectDefinition extends Model
{
    protected $fillable = [
        'name',
        'type',
        'description',
        'access',
        'capabilities',
        'displayable_fields',
        'is_single',
    ];

    protected $casts = [
        'access' => 'array',
        'capabilities' => 'array',
        'displayable_fields' => 'array',
        'is_single' => 'boolean',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('metaobject_definitions');
        parent::__construct($attributes);
    }

    public function getRouteKeyName(): string
    {
        return 'type';
    }

    public function metaobjects(): HasMany
    {
        return $this->hasMany(Metaobject::class, 'definition_id');
    }

    public function scopeSingle($query)
    {
        return $query->where('is_single', true);
    }

    public function scopeMultiple($query)
    {
        return $query->where('is_single', false);
    }

    public function getFieldDefinitions(): array
    {
        return $this->capabilities['fields'] ?? [];
    }

    public function hasCapability(string $capability): bool
    {
        return in_array($capability, $this->capabilities ?? []);
    }

    public function isPublishable(): bool
    {
        return $this->hasCapability('publishable');
    }

    public function isSearchable(): bool
    {
        return $this->hasCapability('searchable');
    }

    public function getDisplayableFields(): array
    {
        return $this->displayable_fields ?? [];
    }
}
