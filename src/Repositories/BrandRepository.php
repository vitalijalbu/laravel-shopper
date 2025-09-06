<?php

declare(strict_types=1);

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\Brand;

class BrandRepository extends BaseRepository
{
    protected string $cachePrefix = 'brands';

    protected function makeModel(): Model
    {
        return new Brand;
    }

    /**
     * Get paginated brands with filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Featured filter
        if (isset($filters['is_featured'])) {
            $query->where('is_featured', $filters['is_featured']);
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get featured brands
     */
    public function getFeatured(): Collection
    {
        $cacheKey = $this->getCacheKey('featured', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_featured', true)->orderBy('name')->get();
        });
    }

    /**
     * Toggle brand featured status
     */
    public function toggleFeatured(int $id): Brand
    {
        $brand = $this->findOrFail($id);
        $brand->update(['is_featured' => ! $brand->is_featured]);

        $this->clearCache();

        return $brand->fresh();
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['status' => $status]);
        $this->clearCache();

        return $updated;
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(int $id): Brand
    {
        $brand = $this->findOrFail($id);
        $newStatus = $brand->status === 'active' ? 'inactive' : 'active';
        $brand->update(['status' => $newStatus]);

        $this->clearCache();

        return $brand->fresh();
    }

    /**
     * Check if brand can be deleted
     */
    public function canDelete(int $id): bool
    {
        $brand = $this->find($id);

        if (!$brand) {
            return false;
        }

        // Check if brand has products
        return !$brand->products()->exists();
    }

    /**
     * Get brand products
     */
    public function getBrandProducts(int $brandId): Collection
    {
        $brand = $this->find($brandId);

        return $brand ? $brand->products : collect();
    }
}
