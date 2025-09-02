<?php

namespace LaravelShopper\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelShopper\Models\Customer;
use LaravelShopper\Models\CustomerGroup;

class CustomerRepository extends BaseRepository
{
    protected array $with = ['customerGroup', 'orders'];

    protected string $cachePrefix = 'customers';

    protected function makeModel(): Model
    {
        return new Customer();
    }

    /**
     * Get paginated customers with filters and search
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['customerGroup'])
            ->withCount(['orders'])
            ->withSum('orders', 'total_amount');

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        // Customer group filter
        if (!empty($filters['customer_group_id'])) {
            $query->where('customer_group_id', $filters['customer_group_id']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'created_at';
        $sortDirection = $filters['direction'] ?? 'desc';
        
        // Handle special sort fields
        if ($sortField === 'total_spent') {
            $query->orderBy('orders_sum_total_amount', $sortDirection);
        } elseif ($sortField === 'orders_count') {
            $query->orderBy('orders_count', $sortDirection);
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        return $query->paginate($perPage);
    }

    /**
     * Create a new customer
     */
    public function create(array $data): Customer
    {
        // Clear cache
        $this->clearCache();

        return $this->model->create($data);
    }

    /**
     * Update customer
     */
    public function update(int $id, array $attributes): Model
    {
        // Clear cache
        $this->clearCache();

        $customer = $this->model->find($id);
        $customer->update($attributes);
        
        return $customer;
    }

    /**
     * Delete customer
     */
    public function delete(int $id): bool
    {
        // Clear cache
        $this->clearCache();

        return $this->model->find($id)->delete();
    }

    /**
     * Get customer groups for filters
     */
    public function getCustomerGroups(): \Illuminate\Database\Eloquent\Collection
    {
        return CustomerGroup::select('id', 'name')->orderBy('name')->get();
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
