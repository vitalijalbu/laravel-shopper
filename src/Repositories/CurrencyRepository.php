<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Models\Currency;
use Illuminate\Database\Eloquent\Category;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;

class CurrencyRepository extends BaseRepository
{
    protected string $cachePrefix = 'currencies';

    protected function makeModel(): Model
    {
        return new Currency;
    }

    public function findAll(array $filters = []): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('symbol', 'like', "%{$search}%");
            });
        }

        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', $filters['is_enabled']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    public function getEnabled(): Category
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('enabled', 'all'), $this->cacheTtl, function () {
            return $this->model->where('is_enabled', true)->orderBy('name')->get();
        });
    }

    public function getDefault(): ?Currency
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('default', 'currency'), $this->cacheTtl, function () {
            return $this->model->where('is_default', true)->first();
        });
    }

    public function setAsDefault(int $id): Currency
    {
        $this->model->where('is_default', true)->update(['is_default' => false]);

        $currency = $this->findOrFail($id);
        $currency->update(['is_default' => true]);

        $this->clearCache();

        return $currency->fresh();
    }

    public function convert(string $from, string $to, float $amount): array
    {
        // Mock conversion - in real app you'd use an API
        $rate = 1.0; // This should come from external API

        return [
            'from' => $from,
            'to' => $to,
            'amount' => $amount,
            'converted_amount' => $amount * $rate,
            'rate' => $rate,
            'timestamp' => now()->toISOString(),
        ];
    }

    public function getRates(): array
    {
        return \Illuminate\Support\Facades\Cache::remember($this->getCacheKey('rates', 'all'), 3600, function () {
            // Mock rates - in real app you'd fetch from external API
            return [
                'EUR' => 1.0,
                'USD' => 1.18,
                'GBP' => 0.85,
                'JPY' => 130.0,
            ];
        });
    }
}
