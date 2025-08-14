<?php

declare(strict_types=1);

namespace LaravelShopper\Traits;

use LaravelShopper\Models\Metafield;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasMetafields
{
    public function metafields(): MorphMany
    {
        return $this->morphMany(Metafield::class, 'metafieldable');
    }

    public function getMetafield(string $namespace, string $key): ?Metafield
    {
        return $this->metafields()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();
    }

    public function getMetafieldValue(string $namespace, string $key, mixed $default = null): mixed
    {
        $metafield = $this->getMetafield($namespace, $key);
        
        return $metafield ? $metafield->getCastedValue() : $default;
    }

    public function setMetafield(string $namespace, string $key, mixed $value): Metafield
    {
        $metafield = $this->metafields()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->first();

        if (!$metafield) {
            $metafield = new Metafield([
                'namespace' => $namespace,
                'key' => $key,
            ]);
            $metafield->metafieldable()->associate($this);
        }

        $metafield->setValue($value);
        $metafield->save();

        return $metafield;
    }

    public function deleteMetafield(string $namespace, string $key): bool
    {
        return $this->metafields()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->delete() > 0;
    }

    public function getMetafieldsByNamespace(string $namespace): array
    {
        return $this->metafields()
            ->where('namespace', $namespace)
            ->get()
            ->mapWithKeys(function (Metafield $metafield) {
                return [$metafield->key => $metafield->getCastedValue()];
            })
            ->toArray();
    }

    public function setMetafields(string $namespace, array $fields): void
    {
        foreach ($fields as $key => $value) {
            $this->setMetafield($namespace, $key, $value);
        }
    }

    public function syncMetafields(string $namespace, array $fields): void
    {
        // Delete existing metafields for this namespace
        $this->metafields()->where('namespace', $namespace)->delete();
        
        // Create new ones
        $this->setMetafields($namespace, $fields);
    }

    public function getAllMetafieldsGrouped(): array
    {
        return $this->metafields()
            ->get()
            ->groupBy('namespace')
            ->map(function ($metafields) {
                return $metafields->mapWithKeys(function (Metafield $metafield) {
                    return [$metafield->key => $metafield->getCastedValue()];
                });
            })
            ->toArray();
    }

    public function hasMetafield(string $namespace, string $key): bool
    {
        return $this->metafields()
            ->where('namespace', $namespace)
            ->where('key', $key)
            ->exists();
    }
}
