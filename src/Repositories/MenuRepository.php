<?php

namespace Cartino\Repositories;

use Cartino\Models\Menu;
use Illuminate\Database\Eloquent\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class MenuRepository extends BaseRepository
{
    protected string $cachePrefix = 'menus';

    protected function makeModel(): Model
    {
        return new Menu;
    }

    /**
     * Get paginated menus with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->withCount('allItems');

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('handle', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'sort_order';
        $sortDirection = $filters['direction'] ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get active menus
     */
    public function getActive(): Category
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_active', true)
                ->orderBy('sort_order')
                ->get();
        });
    }

    /**
     * Get menu by handle
     */
    public function findByHandle(string $handle): ?Menu
    {
        $cacheKey = $this->getCacheKey('handle', $handle);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($handle) {
            return $this->model->where('handle', $handle)->first();
        });
    }

    /**
     * Get menu with tree structure
     */
    public function getWithTree(string $handle): ?Menu
    {
        $menu = $this->findByHandle($handle);

        if (! $menu) {
            return null;
        }

        return $menu->load(['items' => function ($query) {
            $query->whereNull('parent_id')->with('children')->orderBy('sort_order');
        }]);
    }

    /**
     * Duplicate menu
     */
    public function duplicate(int $id): ?Menu
    {
        $originalMenu = $this->find($id);

        if (! $originalMenu) {
            return null;
        }

        $duplicatedData = $originalMenu->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);

        $duplicatedData['title'] = $duplicatedData['title'].' (Copy)';
        $duplicatedData['handle'] = $duplicatedData['handle'].'_copy_'.time();
        $duplicatedData['is_active'] = false;

        $newMenu = $this->create($duplicatedData);

        // Duplicate menu items
        $this->duplicateMenuItems($originalMenu->items, $newMenu->id);

        $this->clearCache();

        return $newMenu;
    }

    /**
     * Duplicate menu items recursively
     */
    protected function duplicateMenuItems($items, int $newMenuId, ?int $newParentId = null): void
    {
        foreach ($items as $item) {
            $itemData = $item->toArray();
            unset($itemData['id'], $itemData['created_at'], $itemData['updated_at']);

            $itemData['menu_id'] = $newMenuId;
            $itemData['parent_id'] = $newParentId;

            $newItem = \Cartino\Models\MenuItem::create($itemData);

            if ($item->children->isNotEmpty()) {
                $this->duplicateMenuItems($item->children, $newMenuId, $newItem->id);
            }
        }
    }

    /**
     * Update sort orders
     */
    public function updateSortOrders(array $menus): bool
    {
        try {
            foreach ($menus as $menuData) {
                $this->model->where('id', $menuData['id'])
                    ->update(['sort_order' => $menuData['sort_order']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Toggle menu status
     */
    public function toggleStatus(int $id): ?Menu
    {
        $menu = $this->find($id);

        if (! $menu) {
            return null;
        }

        $menu->update(['is_active' => ! $menu->is_active]);
        $this->clearCache();

        return $menu->fresh();
    }

    /**
     * Get menus for select options
     */
    public function getForSelect(): Category
    {
        return $this->model->select('id', 'title', 'handle')
            ->where('is_active', true)
            ->orderBy('title')
            ->get();
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        \Illuminate\Support\Facades\Cache::tags($tags)->flush();
    }
}
