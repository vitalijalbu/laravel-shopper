<?php

declare(strict_types=1);

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Contracts\SupplierRepositoryInterface;
use Shopper\Models\Supplier;

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
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['site']);

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Country filter
        if (! empty($filters['country_code'])) {
            $query->where('country_code', $filters['country_code']);
        }

        // Priority filter
        if (! empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        // Rating filter
        if (! empty($filters['min_rating'])) {
            $query->where('rating', '>=', $filters['min_rating']);
        }

        // Preferred filter
        if (isset($filters['is_preferred'])) {
            $query->where('is_preferred', $filters['is_preferred']);
        }

        // Verified filter
        if (isset($filters['is_verified'])) {
            $query->where('is_verified', $filters['is_verified']);
        }

        // Date range filters
        if (! empty($filters['created_from'])) {
            $query->whereDate('created_at', '>=', $filters['created_from']);
        }

        if (! empty($filters['created_to'])) {
            $query->whereDate('created_at', '<=', $filters['created_to']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
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
    public function getActive(): Collection
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->active()->orderBy('name')->get();
        });
    }

    /**
     * Get preferred suppliers
     */
    public function getPreferred(): Collection
    {
        $cacheKey = $this->getCacheKey('preferred', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->preferred()->orderBy('rating', 'desc')->get();
        });
    }

    /**
     * Get suppliers by country
     */
    public function getByCountry(string $countryCode): Collection
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
    public function getByPriority(string $priority): Collection
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
    public function getByRating(float $minRating): Collection
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
    public function getTopPerformers(int $limit = 10): Collection
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
    public function getSupplierProducts(int $supplierId): Collection
    {
        $supplier = $this->find($supplierId);

        return $supplier ? $supplier->products : collect();
    }

    /**
     * Get supplier purchase orders
     */
    public function getSupplierPurchaseOrders(int $supplierId): Collection
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
     * Check if supplier can be deleted
     */
    public function canDelete(int $id): bool
    {
        $supplier = $this->find($id);

        if (! $supplier) {
            return false;
        }

        // Check if supplier has products
        if ($supplier->products()->exists()) {
            return false;
        }

        // Check if supplier has purchase orders
        if ($supplier->purchaseOrders()->exists()) {
            return false;
        }

        return true;
    }
}
