<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Global;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class GlobalRepository extends BaseRepository
{
    protected string $cachePrefix = 'global';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): \Illuminate\Database\Eloquent\Model
    {
        return new Global;
    }

    /**
     * Get paginated globals with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Global::class)
            ->allowedFilters([
                'handle',
                'title',
                AllowedFilter::scope('search'),
            ])
            ->allowedSorts(['handle', 'title', 'created_at', 'updated_at'])
            ->paginate($filters['per_page'] ?? 15)
            ->appends($filters);
    }

    /**
     * Find one by ID or handle
     */
    public function findOne(int|string $id): ?Global
    {
        if (is_numeric($id)) {
            return $this->model->find($id);
        }

        return $this->model->byHandle($id)->first();
    }

    /**
     * Create a new global
     */
    public function createOne(array $data): Global
    {
        $global = $this->model->create($data);
        $this->clearCache();

        return $global;
    }

    /**
     * Update a global
     */
    public function updateOne(int $id, array $data): Global
    {
        $global = $this->model->findOrFail($id);
        $global->update($data);
        $this->clearCache();

        return $global->fresh();
    }

    /**
     * Delete a global
     */
    public function deleteOne(int $id): bool
    {
        $global = $this->model->findOrFail($id);
        $deleted = $global->delete();
        
        if ($deleted) {
            $this->clearCache();
        }

        return $deleted;
    }

    /**
     * Check if global can be deleted
     */
    public function canDelete(int $id): bool
    {
        // All globals can be deleted
        return true;
    }

    /**
     * Get global by handle
     */
    public function getByHandle(string $handle): ?Global
    {
        return $this->model->byHandle($handle)->first();
    }

    /**
     * Get all globals
     */
    public function getAll(): Collection
    {
        return $this->model->orderBy('handle')->get();
    }

    /**
     * Update global data by handle
     */
    public function updateByHandle(string $handle, array $data): ?Global
    {
        $global = $this->getByHandle($handle);
        
        if (!$global) {
            return null;
        }

        $global->update(['data' => $data]);
        $this->clearCache();

        return $global->fresh();
    }

    /**
     * Set a specific value in global data
     */
    public function setValue(string $handle, string $key, mixed $value): ?Global
    {
        $global = $this->getByHandle($handle);
        
        if (!$global) {
            return null;
        }

        $global->set($key, $value);
        $global->save();
        $this->clearCache();

        return $global->fresh();
    }

    /**
     * Get a specific value from global data
     */
    public function getValue(string $handle, string $key, mixed $default = null): mixed
    {
        $global = $this->getByHandle($handle);
        
        if (!$global) {
            return $default;
        }

        return $global->get($key, $default);
    }
}
