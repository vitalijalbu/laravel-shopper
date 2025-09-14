<?php

declare(strict_types=1);

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\Country;

class CountryRepository extends BaseRepository
{
    protected string $cachePrefix = 'countries';

    protected function makeModel(): Model
    {
        return new Country;
    }

    public function getPaginatedWithFilters(array $filters = [], int $perPage = 50): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getEnabled(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('enabled', 'all'), $this->cacheTtl, function () {
            return $this->model->where('is_active', true)->orderBy('name')->get();
        });
    }

    public function getByRegion(): array
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('by_region', 'all'), $this->cacheTtl, function () {
            return $this->model->where('is_active', true)
                ->get()
                ->groupBy('region')
                ->map(fn ($countries) => $countries->sortBy('name')->values())
                ->toArray();
        });
    }

    public function getRegions(): Collection
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('regions', 'all'), $this->cacheTtl, function () {
            return $this->model->select('region')
                ->distinct()
                ->whereNotNull('region')
                ->orderBy('region')
                ->pluck('region');
        });
    }
}
