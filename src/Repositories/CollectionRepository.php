<?php

declare(strict_types=1);

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\Collection;

final class CollectionRepository extends BaseRepository
{
    protected string $cachePrefix = 'collections';

    protected function makeModel(): Model
    {
        return new Collection;
    }

    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['products']);

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Visibility filter
        if (isset($filters['is_visible'])) {
            $query->where('is_visible', $filters['is_visible']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'sort_order';
        $sortDirection = $filters['direction'] ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    public function findWithProducts(int $id): ?Collection
    {
        $cacheKey = $this->getCacheKey('with_products', $id);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($id) {
            return $this->model->with(['products'])->find($id);
        });
    }

    public function getProducts(int $collectionId, int $perPage = 25): LengthAwarePaginator
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return new LengthAwarePaginator([], 0, $perPage);
        }

        return $collection->products()->paginate($perPage);
    }

    public function addProducts(int $collectionId, array $productIds): array
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return ['success' => false, 'message' => 'Collection not found'];
        }

        try {
            $collection->products()->syncWithoutDetaching($productIds);
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Products added successfully',
                'added_count' => count($productIds),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function removeProducts(int $collectionId, array $productIds): array
    {
        $collection = $this->model->find($collectionId);

        if (! $collection) {
            return ['success' => false, 'message' => 'Collection not found'];
        }

        try {
            $collection->products()->detach($productIds);
            $this->clearCache();

            return [
                'success' => true,
                'message' => 'Products removed successfully',
                'removed_count' => count($productIds),
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public function bulkAction(string $action, array $ids): array
    {
        $validatedIds = $this->model->whereIn('id', $ids)->pluck('id')->toArray();
        $processedCount = 0;
        $errors = [];

        foreach ($validatedIds as $id) {
            try {
                switch ($action) {
                    case 'show':
                        $this->model->where('id', $id)->update(['is_visible' => true]);
                        $processedCount++;
                        break;

                    case 'hide':
                        $this->model->where('id', $id)->update(['is_visible' => false]);
                        $processedCount++;
                        break;

                    case 'delete':
                        $this->model->where('id', $id)->delete();
                        $processedCount++;
                        break;

                    default:
                        $errors[] = "Collection ID {$id}: Unknown action '{$action}'";
                }
            } catch (\Exception $e) {
                $errors[] = "Collection ID {$id}: {$e->getMessage()}";
            }
        }

        $this->clearCache();

        return [
            'processed' => $processedCount,
            'total' => count($ids),
            'errors' => $errors,
            'success' => count($errors) === 0,
        ];
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        \Illuminate\Support\Facades\Cache::tags($tags)->flush();
    }

    protected function getFilterableFields(): array
    {
        return [
            'name',
            'slug',
            'is_visible',
            'sort_order',
            'created_at',
            'updated_at',
        ];
    }

    protected function getSearchableFields(): array
    {
        return ['name', 'description'];
    }

    protected function getDefaultSortField(): string
    {
        return 'sort_order';
    }

    protected function getDefaultSortDirection(): string
    {
        return 'asc';
    }
}
