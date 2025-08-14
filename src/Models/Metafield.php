<?php

declare(strict_types=1);

namespace LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Metafield extends Model
{
    protected $fillable = [
        'definition_id',
        'namespace',
        'key',
        'metafieldable_type',
        'metafieldable_id',
        'value',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('metafields');
        parent::__construct($attributes);
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(MetafieldDefinition::class, 'definition_id');
    }

    public function metafieldable(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeForOwner($query, Model $owner)
    {
        return $query->where('metafieldable_type', get_class($owner))
                    ->where('metafieldable_id', $owner->getKey());
    }

    public function scopeByNamespace($query, string $namespace)
    {
        return $query->where('namespace', $namespace);
    }

    public function scopeByKey($query, string $key)
    {
        return $query->where('key', $key);
    }

    public function scopeByNamespaceAndKey($query, string $namespace, string $key)
    {
        return $query->where('namespace', $namespace)->where('key', $key);
    }

    public function getFullKey(): string
    {
        return $this->namespace . '.' . $this->key;
    }

    public function getCastedValue(): mixed
    {
        if (!$this->definition) {
            return $this->value;
        }

        return match ($this->definition->type) {
            'number' => is_numeric($this->value) ? (float) $this->value : $this->value,
            'boolean' => filter_var($this->value, FILTER_VALIDATE_BOOLEAN),
            'date' => $this->value ? \Carbon\Carbon::parse($this->value) : null,
            'json' => json_decode($this->value, true),
            default => $this->value,
        };
    }

    public function setValue(mixed $value): self
    {
        $this->value = match (gettype($value)) {
            'boolean' => $value ? '1' : '0',
            'array', 'object' => json_encode($value),
            'NULL' => null,
            default => (string) $value,
        };

        return $this;
    }

    public function validate(): bool
    {
        return $this->definition?->validateValue($this->getCastedValue()) ?? true;
    }
}
