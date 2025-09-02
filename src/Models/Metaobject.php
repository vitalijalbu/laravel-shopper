<?php

declare(strict_types=1);

namespace Shopper\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Metaobject extends Model
{
    protected $fillable = [
        'definition_id',
        'handle',
        'fields',
        'published_at',
    ];

    protected $casts = [
        'fields' => 'array',
        'published_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $this->table = shopper_table('metaobjects');
        parent::__construct($attributes);
    }

    public function getRouteKeyName(): string
    {
        return 'handle';
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(MetaobjectDefinition::class, 'definition_id');
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft($query)
    {
        return $query->whereNull('published_at');
    }

    public function scopeByDefinition($query, string $type)
    {
        return $query->whereHas('definition', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null && $this->published_at->isPast();
    }

    public function publish(): bool
    {
        return $this->update(['published_at' => now()]);
    }

    public function unpublish(): bool
    {
        return $this->update(['published_at' => null]);
    }

    public function getField(string $key, mixed $default = null): mixed
    {
        return data_get($this->fields, $key, $default);
    }

    public function setField(string $key, mixed $value): self
    {
        $fields = $this->fields ?? [];
        data_set($fields, $key, $value);
        $this->fields = $fields;

        return $this;
    }

    public function getDisplayName(): string
    {
        $displayFields = $this->definition->getDisplayableFields();

        if (empty($displayFields)) {
            return $this->handle;
        }

        foreach ($displayFields as $field) {
            if ($value = $this->getField($field)) {
                return is_array($value) ? implode(' ', $value) : (string) $value;
            }
        }

        return $this->handle;
    }

    public function toDisplayArray(): array
    {
        return [
            'id' => $this->id,
            'handle' => $this->handle,
            'display_name' => $this->getDisplayName(),
            'type' => $this->definition->type,
            'published' => $this->isPublished(),
            'fields' => $this->fields,
            'updated_at' => $this->updated_at,
        ];
    }
}
