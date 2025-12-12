<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Brand;
use Illuminate\Database\Eloquent\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\QueryBuilder;

class BrandRepository extends BaseRepository
{
    protected string $cachePrefix = 'brands';

    protected function makeModel(): Model
    {
        return new Brand;
    }

    /**
     * Get paginated data with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return $query = QueryBuilder::for(Brand::class)
            ->allowedFilters(['name', 'email'])
            ->allowedSorts(['name', 'created_at', 'status'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page'))
            ->appends($filters);
    }

    /**
     * Find one by ID or slug
     */
    public function findOne(int|string $handle): ?Brand
    {
        return $this->model
            ->where('id', $handle)
            ->orWhere('slug', $handle)
            ->firstOrfail();
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

    public function createOne(array $data): Brand
    {
        $brand = $this->model->create($data);

        $this->clearCache();

        return $brand;
    }

    public function createMany(array $dataArray): Category
    {
        $brands = collect();

        foreach ($dataArray as $data) {
            $brands->push($this->model->create($data));
        }

        $this->clearCache();

        return $brands;
    }

    public function updateOne(int $id, array $data): Brand
    {
        $brand = $this->findOrFail($id);
        $brand->update($data);

        $this->clearCache();

        return $brand->fresh();
    }

    public function updateMany(array $ids, array $data): int
    {
        $updated = $this->model->whereIn('id', $ids)->update($data);

        $this->clearCache();

        return $updated;
    }

    public function deleteOne(int $id): bool
    {
        $brand = $this->findOrFail($id);
        $result = $brand->delete();

        $this->clearCache();

        return $result;
    }

    public function deleteMany(array $ids): int
    {
        $deleted = $this->model->whereIn('id', $ids)->delete();

        $this->clearCache();

        return $deleted;
    }

    /**
     * Find brands by IDs
     */
    public function findByIds(array $ids): Category
    {
        return $this->model->whereIn('id', $ids)->get();
    }
}
