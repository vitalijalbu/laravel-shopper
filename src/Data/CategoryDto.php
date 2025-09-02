<?php

namespace Shopper\Data;

class CategoryDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public ?string $description = null,
        public ?int $parent_id = null,
        public int $sort_order = 0,
        public bool $is_enabled = true,
        public array $seo = [],
        public array $meta = [],
        public ?string $created_at = null,
        public ?string $updated_at = null
    ) {}

    /**
     * Create from array
     */
    public static function from(array $data): static
    {
        return new static(
            id: $data['id'] ?? null,
            name: $data['name'] ?? '',
            slug: $data['slug'] ?? '',
            description: $data['description'] ?? null,
            parent_id: isset($data['parent_id']) ? (int) $data['parent_id'] : null,
            sort_order: (int) ($data['sort_order'] ?? 0),
            is_enabled: filter_var($data['is_enabled'] ?? true, FILTER_VALIDATE_BOOLEAN),
            seo: $data['seo'] ?? [],
            meta: $data['meta'] ?? [],
            created_at: $data['created_at'] ?? null,
            updated_at: $data['updated_at'] ?? null,
        );
    }

    /**
     * Convert to array
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug ?: str($this->name)->slug()->toString(),
            'description' => $this->description,
            'parent_id' => $this->parent_id,
            'sort_order' => $this->sort_order,
            'is_enabled' => $this->is_enabled,
            'seo' => $this->seo,
            'meta' => $this->meta,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ], fn ($value) => $value !== null);
    }

    /**
     * Validate category data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->name))) {
            $errors['name'] = 'Category name is required';
        }

        if (strlen($this->name) > 255) {
            $errors['name'] = 'Category name cannot exceed 255 characters';
        }

        if (! empty($this->description) && strlen($this->description) > 1000) {
            $errors['description'] = 'Description cannot exceed 1000 characters';
        }

        if ($this->parent_id && $this->parent_id === $this->id) {
            $errors['parent_id'] = 'Category cannot be its own parent';
        }

        if ($this->sort_order < 0) {
            $errors['sort_order'] = 'Sort order must be a positive number';
        }

        return $errors;
    }

    /**
     * Check if category is active
     */
    public function isEnabled(): bool
    {
        return $this->is_enabled;
    }

    /**
     * Get formatted category name with parent
     */
    public function getDisplayName(?string $parentName = null): string
    {
        if ($parentName) {
            return "{$parentName} > {$this->name}";
        }

        return $this->name;
    }

    /**
     * Get SEO title
     */
    public function getSeoTitle(): string
    {
        return $this->seo['title'] ?? $this->name;
    }

    /**
     * Get SEO description
     */
    public function getSeoDescription(): string
    {
        return $this->seo['description'] ?? $this->description ?? '';
    }
}
