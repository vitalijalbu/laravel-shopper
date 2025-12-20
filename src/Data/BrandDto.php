<?php

namespace Cartino\Data;

class BrandDto extends BaseDto
{
    public function __construct(
        public ?int $id = null,
        public string $name = '',
        public string $slug = '',
        public ?string $description = null,
        public ?string $website = null,
        public string $status = 'active',
        public array $seo = [],
        public array $meta = [],
        public ?string $created_at = null,
        public ?string $updated_at = null,
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
            website: $data['website'] ?? null,
            status: $data['status'] ?? 'active',
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
        return array_filter(
            [
                'id' => $this->id,
                'name' => $this->name,
                'slug' => $this->slug ?: str($this->name)->slug()->toString(),
                'description' => $this->description,
                'website' => $this->website,
                'status' => $this->status,
                'seo' => $this->seo,
                'meta' => $this->meta,
                'created_at' => $this->created_at,
                'updated_at' => $this->updated_at,
            ],
            fn ($value) => $value !== null,
        );
    }

    /**
     * Validate brand data
     */
    public function validate(): array
    {
        $errors = [];

        if (empty(trim($this->name))) {
            $errors['name'] = 'Brand name is required';
        }

        if (strlen($this->name) > 255) {
            $errors['name'] = 'Brand name cannot exceed 255 characters';
        }

        if (! empty($this->description) && strlen($this->description) > 1000) {
            $errors['description'] = 'Description cannot exceed 1000 characters';
        }

        if (! empty($this->website) && ! filter_var($this->website, FILTER_VALIDATE_URL)) {
            $errors['website'] = 'Website must be a valid URL';
        }

        return $errors;
    }

    /**
     * Check if brand is active
     */
    public function isEnabled(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Get website domain
     */
    public function getWebsiteDomain(): ?string
    {
        if (! $this->website) {
            return null;
        }

        return parse_url($this->website, PHP_URL_HOST);
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

    /**
     * Check if has website
     */
    public function hasWebsite(): bool
    {
        return ! empty($this->website);
    }
}
