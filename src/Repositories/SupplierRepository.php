<?php

declare(strict_types=1);

namespace Cartino\Repositories;

use Cartino\Contracts\SupplierRepositoryInterface;
use Cartino\Models\Supplier;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\QueryBuilder;

class SupplierRepository extends BaseRepository implements SupplierRepositoryInterface
{
    protected string $cachePrefix = 'suppliers';

    protected array $with = ['site'];

    protected function makeModel(): Model
    {
        return new Supplier;
    }

    /**
     * Get paginated suppliers with filters
     */
    public function findAll(array $filters = []): LengthAwarePaginator
    {
        return QueryBuilder::for(Supplier::class)
            ->allowedFilters([
                'name',
                'code',
                'email',
                'status',
                AllowedFilter::exact('country_code'),
                AllowedFilter::exact('is_preferred'),
            ])
            ->allowedSorts(['name', 'code', 'created_at', 'rating'])
            ->allowedIncludes(['site', 'purchaseOrders'])
            ->paginate($filters['per_page'] ?? config('settings.pagination.per_page', 15))
            ->appends($filters);
    }

    /**
     * Find one by ID or code
     */
    public function findOne(int|string $codeOrId): ?Supplier
    {
        return $this->model
            ->where('id', $codeOrId)
            ->orWhere('code', $codeOrId)
            ->firstOrFail();
    }

    /**
     * Create one
     */
    public function createOne(array $data): Supplier
    {
        $supplier = $this->model->create($data);
        $this->clearCache();

        return $supplier;
    }

    /**
     * Update one
     */
    public function updateOne(int $id, array $data): Supplier
    {
        $supplier = $this->findOrFail($id);
        $supplier->update($data);
        $this->clearCache();

        return $supplier->fresh();
    }

    /**
     * Delete one
     */
    public function deleteOne(int $id): bool
    {
        $supplier = $this->findOrFail($id);
        $deleted = $supplier->delete();
        $this->clearCache();

        return $deleted;
    }

    /**
     * Check if can delete
     */
    public function canDelete(int $id): bool
    {
        $supplier = $this->findOrFail($id);

        return ! $supplier->purchaseOrders()->exists();
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(int $id): Supplier
    {
        $supplier = $this->findOrFail($id);
        $newStatus = $supplier->status === 'active' ? 'inactive' : 'active';
        $supplier->update(['status' => $newStatus]);
        $this->clearCache();

        return $supplier->fresh();
    }

    /**
     * Find supplier by code
     */
    public function findByCode(string $code): ?Supplier
    {
        $cacheKey = $this->getCacheKey('code', $code);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($code) {
            return $this->model->where('code', $code)->first();
        });
    }

    /**
     * Get active suppliers
     */
    public function getActive(): Category
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->active()->orderBy('name')->get();
        });
    }

    /**
     * Get preferred suppliers
     */
    public function getPreferred(): Category
    {
        $cacheKey = $this->getCacheKey('preferred', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->preferred()->orderBy('rating', 'desc')->get();
        });
    }

    /**
     * Get suppliers by country
     */
    public function getByCountry(string $countryCode): Category
    {
        $cacheKey = $this->getCacheKey('country', $countryCode);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($countryCode) {
            return $this->model->where('country_code', $countryCode)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get suppliers by priority
     */
    public function getByPriority(string $priority): Category
    {
        $cacheKey = $this->getCacheKey('priority', $priority);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($priority) {
            return $this->model->where('priority', $priority)
                ->orderBy('name')
                ->get();
        });
    }

    /**
     * Get suppliers by minimum rating
     */
    public function getByRating(float $minRating): Category
    {
        $cacheKey = $this->getCacheKey('rating', (string) $minRating);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($minRating) {
            return $this->model->where('rating', '>=', $minRating)
                ->orderBy('rating', 'desc')
                ->get();
        });
    }

    /**
     * Update supplier rating
     */
    public function updateRating(int $id, float $rating): bool
    {
        $result = $this->model->where('id', $id)->update(['rating' => $rating]);
        $this->clearCache();

        return (bool) $result;
    }

    /**
     * Get top performing suppliers
     */
    public function getTopPerformers(int $limit = 10): Category
    {
        $cacheKey = $this->getCacheKey('top_performers', (string) $limit);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($limit) {
            return $this->model->orderBy('rating', 'desc')
                ->orderBy('on_time_delivery_rate', 'desc')
                ->limit($limit)
                ->get();
        });
    }

    /**
     * Get supplier with products
     */
    public function getWithProducts(int $id): ?Supplier
    {
        return $this->model->with(['products', 'productSuppliers.product'])->find($id);
    }

    /**
     * Get supplier with purchase orders
     */
    public function getWithPurchaseOrders(int $id): ?Supplier
    {
        return $this->model->with(['purchaseOrders', 'purchaseOrders.items'])->find($id);
    }

    /**
     * Bulk update supplier status
     */
    public function bulkUpdateStatus(array $ids, string $status): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['status' => $status]);
        $this->clearCache();

        return $updated;
    }

    /**
     * Get supplier products
     */
    public function getSupplierProducts(int $supplierId): Category
    {
        $supplier = $this->find($supplierId);

        return $supplier ? $supplier->products : collect();
    }

    /**
     * Get supplier purchase orders
     */
    public function getSupplierPurchaseOrders(int $supplierId): Category
    {
        $supplier = $this->find($supplierId);

        return $supplier ? $supplier->purchaseOrders : collect();
    }

    /**
     * Calculate performance metrics for supplier
     */
    public function calculatePerformanceMetrics(int $id): array
    {
        $supplier = $this->find($id);

        if (! $supplier) {
            return [];
        }

        $purchaseOrders = $supplier->purchaseOrders;
        $totalOrders = $purchaseOrders->count();

        if ($totalOrders === 0) {
            return [
                'total_orders' => 0,
                'on_time_deliveries' => 0,
                'on_time_delivery_rate' => 0,
                'average_delivery_time' => 0,
                'total_value' => 0,
                'average_order_value' => 0,
            ];
        }

        $onTimeDeliveries = $purchaseOrders->filter(fn ($order) => $order->delivered_on_time)->count();
        $totalValue = $purchaseOrders->sum('total_amount');
        $deliveredOrders = $purchaseOrders->whereNotNull('delivery_date');
        $averageDeliveryTime = $deliveredOrders->avg(function ($order) {
            return $order->created_at->diffInDays($order->delivery_date);
        });

        return [
            'total_orders' => $totalOrders,
            'on_time_deliveries' => $onTimeDeliveries,
            'on_time_delivery_rate' => round(($onTimeDeliveries / $totalOrders) * 100, 2),
            'average_delivery_time' => round($averageDeliveryTime, 1),
            'total_value' => $totalValue,
            'average_order_value' => round($totalValue / $totalOrders, 2),
        ];

        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->exists()) {
            return false;
        }

        return true;
    }
}
