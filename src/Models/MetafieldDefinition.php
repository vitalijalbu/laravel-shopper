<?php

declare(strict_types=1);

namespace LaravelShopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MetafieldDefinition extends Model
{
    protected $fillable = [
        'namespace',
        'key',
        'name',
        'description',
        'type',
        'type_config',
        'validations',
        'owner_type',
        'is_required',
        'is_unique',
        'list_position',
    ];

    protected $casts = [
        'type_config' => 'array',
        'validations' => 'array',
        'is_required' => 'boolean',
        'is_unique' => 'boolean',
        'list_position' => 'integer',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('metafield_definitions');
        parent::__construct($attributes);
    }

    public function getRouteKeyName(): string
    {
        return 'key';
    }

    public function scopeForOwnerType($query, string $ownerType)
    {
        return $query->where('owner_type', $ownerType);
    }

    public function scopeByNamespace($query, string $namespace)
    {
        return $query->where('namespace', $namespace);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeUnique($query)
    {
        return $query->where('is_unique', true);
    }

    public function getFullKey(): string
    {
        return $this->namespace . '.' . $this->key;
    }

    public function validateValue(mixed $value): bool
    {
        // Basic type validation
        if (!$this->isValidType($value)) {
            return false;
        }

        // Custom validations
        foreach ($this->validations ?? [] as $validation) {
            if (!$this->applyValidation($value, $validation)) {
                return false;
            }
        }

        return true;
    }

    private function isValidType(mixed $value): bool
    {
        return match ($this->type) {
            'text', 'url', 'email' => is_string($value),
            'number' => is_numeric($value),
            'boolean' => is_bool($value),
            'date' => $value instanceof \DateTime || is_string($value),
            'json' => true, // JSON can be any type
            'file_reference' => is_string($value) || is_numeric($value),
            default => true,
        };
    }

    private function applyValidation(mixed $value, array $validation): bool
    {
        $rule = $validation['rule'] ?? '';
        $params = $validation['params'] ?? [];

        return match ($rule) {
            'min_length' => strlen((string) $value) >= ($params['value'] ?? 0),
            'max_length' => strlen((string) $value) <= ($params['value'] ?? PHP_INT_MAX),
            'min_value' => (float) $value >= ($params['value'] ?? PHP_FLOAT_MIN),
            'max_value' => (float) $value <= ($params['value'] ?? PHP_FLOAT_MAX),
            'regex' => preg_match($params['pattern'] ?? '', (string) $value),
            'in' => in_array($value, $params['values'] ?? []),
            default => true,
        };
    }

    public function getInputType(): string
    {
        return match ($this->type) {
            'text' => 'text',
            'number' => 'number',
            'boolean' => 'checkbox',
            'date' => 'date',
            'email' => 'email',
            'url' => 'url',
            'json' => 'textarea',
            'file_reference' => 'file',
            default => 'text',
        };
    }
}
