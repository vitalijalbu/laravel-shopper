<?php

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\Customer;
use Shopper\Models\CustomerGroup;

class CustomerRepository extends BaseRepository
{
    protected array $with = ['customerGroup', 'orders', 'fidelityCard'];

    protected string $cachePrefix = 'customers';

    protected function makeModel(): Model
    {
        return new Customer;
    }

    /**
     * Get paginated customers with filters and search
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['customerGroup', 'fidelityCard'])
            ->withCount(['orders'])
            ->withSum('orders', 'total_amount');

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%")
                    ->orWhereHas('fidelityCard', function ($fq) use ($search) {
                        $fq->where('card_number', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $isActive = $filters['status'] === 'active';
            $query->where('is_active', $isActive);
        }

        // Customer group filter
        if (! empty($filters['customer_group_id'])) {
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
     * Find customer with fidelity card details
     */
    public function findWithFidelityCard(int $id): ?Customer
    {
        return $this->model->with(['fidelityCard.transactions' => function ($query) {
            $query->orderBy('created_at', 'desc')->limit(10);
        }])->find($id);
    }

    /**
     * Get customers with fidelity statistics
     */
    public function getWithFidelityStats(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()
            ->with(['customerGroup', 'fidelityCard'])
            ->withCount(['orders'])
            ->withSum('orders', 'total_amount');

        // Aggiungi filtri per fidelity card
        if (! empty($filters['has_fidelity_card'])) {
            if ($filters['has_fidelity_card'] === 'yes') {
                $query->whereHas('fidelityCard');
            } elseif ($filters['has_fidelity_card'] === 'no') {
                $query->whereDoesntHave('fidelityCard');
            }
        }

        if (! empty($filters['fidelity_tier'])) {
            $query->whereHas('fidelityCard', function ($q) use ($filters) {
                $tier = $filters['fidelity_tier'];
                switch ($tier) {
                    case 'bronze':
                        $q->where('total_spent_amount', '<', 100);
                        break;
                    case 'silver':
                        $q->whereBetween('total_spent_amount', [100, 499.99]);
                        break;
                    case 'gold':
                        $q->whereBetween('total_spent_amount', [500, 999.99]);
                        break;
                    case 'platinum':
                        $q->where('total_spent_amount', '>=', 1000);
                        break;
                }
            });
        }

        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('fidelityCard', function ($fq) use ($search) {
                        $fq->where('card_number', 'like', "%{$search}%");
                    });
            });
        }

        return $query->paginate($perPage);
    }

    /**
     * Get customer fidelity statistics
     */
    public function getFidelityStatistics(int $id): array
    {
        $customer = $this->findWithFidelityCard($id);

        if (! $customer || ! $customer->fidelityCard) {
            return [
                'has_fidelity_card' => false,
                'card_number' => null,
                'points' => 0,
                'tier' => null,
                'recent_transactions' => [],
            ];
        }

        $card = $customer->fidelityCard;

        return [
            'has_fidelity_card' => true,
            'card_number' => $card->card_number,
            'points' => [
                'total' => $card->total_points,
                'available' => $card->available_points,
                'redeemed' => $card->total_redeemed,
            ],
            'spending' => [
                'total' => $card->total_spent_amount,
                'tier' => $card->getCurrentTier(),
                'next_tier' => $card->getNextTier(),
            ],
            'card_status' => $card->is_active ? 'active' : 'inactive',
            'issued_at' => $card->issued_at,
            'last_activity' => $card->last_activity_at,
            'recent_transactions' => $card->transactions()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($transaction) {
                    return [
                        'id' => $transaction->id,
                        'type' => $transaction->type,
                        'points' => $transaction->points,
                        'description' => $transaction->description,
                        'created_at' => $transaction->created_at,
                    ];
                }),
        ];
    }

    /**
     * Find customer with relations
     */
    public function findWithRelations(int $id, array $relations = []): ?Customer
    {
        $query = $this->model->with($relations);
        return $query->find($id);
    }

    /**
     * Check if customer can be deleted
     */
    public function canDelete(int $id): bool
    {
        $customer = $this->model->find($id);
        return $customer && !$customer->orders()->exists();
    }

    /**
     * Bulk update customers
     */
    public function bulkUpdate(array $ids, array $data): int
    {
        $this->clearCache();
        return $this->model->whereIn('id', $ids)->update($data);
    }

    /**
     * Bulk delete customers
     */
    public function bulkDelete(array $ids): int
    {
        $this->clearCache();
        $count = 0;
        
        foreach ($ids as $id) {
            if ($this->canDelete($id)) {
                $this->model->find($id)->delete();
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Bulk export customers
     */
    public function bulkExport(array $ids): int
    {
        // TODO: Implement actual export logic
        return count($ids);
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
