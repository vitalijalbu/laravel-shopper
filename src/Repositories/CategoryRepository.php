<?php

namespace LaravelShopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Cache;
use LaravelShopper\Models\Category;

class CategoryRepository extends BaseRepository
{
    protected string $cachePrefix = 'categories';

    protected function makeModel(): Model
    {
        return new Category();
    }

    /**
     * Get paginated categories with filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Apply filters
        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('description', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (isset($filters['status'])) {
            $query->where('is_active', $filters['status']);
        }

        if (!empty($filters['parent_id'])) {
            $query->where('parent_id', $filters['parent_id']);
        }

        // Default ordering
        $query->orderBy('sort_order', 'asc')
              ->orderBy('name', 'asc');

        return $query->with(['parent', 'children'])->paginate($perPage);
    }

    /**
     * Find category with relations
     */
    public function findWithRelations(int $id, array $relations = []): ?Category
    {
        $cacheKey = $this->getCacheKey('find_with_relations', $id . '_' . md5(serialize($relations)));

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($id, $relations) {
            return $this->model->with($relations)->find($id);
        });
    }

    /**
     * Get category tree structure
     */
    public function getTree(): Collection
    {
        $cacheKey = $this->getCacheKey('tree', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->whereNull('parent_id')
                ->with(['children' => function ($query) {
                    $query->orderBy('sort_order', 'asc')->orderBy('name', 'asc');
                }])
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Get categories by parent
     */
    public function getByParent(?int $parentId = null): Collection
    {
        $cacheKey = $this->getCacheKey('by_parent', $parentId ?? 'root');

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($parentId) {
            return $this->model->where('parent_id', $parentId)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Get active categories only
     */
    public function getActive(): Collection
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_active', true)
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Update sort order for categories
     */
    public function updateSortOrder(array $sortData): void
    {
        foreach ($sortData as $item) {
            $this->model->where('id', $item['id'])
                ->update([
                    'sort_order' => $item['sort_order'],
                    'parent_id' => $item['parent_id'] ?? null
                ]);
        }

        $this->clearCache();
    }

    /**
     * Bulk update categories
     */
    public function bulkUpdate(array $ids, string $action): int
    {
        $query = $this->model->whereIn('id', $ids);

        $affected = match ($action) {
            'activate' => $query->update(['is_active' => true]),
            'deactivate' => $query->update(['is_active' => false]),
            'delete' => $query->delete(),
            default => 0
        };

        $this->clearCache();

        return $affected;
    }

    /**
     * Get categories with product count
     */
    public function getWithProductCount(): Collection
    {
        $cacheKey = $this->getCacheKey('with_product_count');

        return $this->cache->remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->withCount('products')
                ->orderBy('sort_order', 'asc')
                ->orderBy('name', 'asc')
                ->get();
        });
    }

    /**
     * Search categories
     */
    public function search(string $term, int $limit = 10): Collection
    {
        return $this->model->where('name', 'like', '%' . $term . '%')
            ->orWhere('description', 'like', '%' . $term . '%')
            ->where('is_active', true)
            ->orderBy('name', 'asc')
            ->limit($limit)
            ->get();
    }
}
