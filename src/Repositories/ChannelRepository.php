<?php

declare(strict_types=1);

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\Channel;

class ChannelRepository extends BaseRepository
{
    protected string $cachePrefix = 'channels';

    protected function makeModel(): Model
    {
        return new Channel;
    }

    /**
     * Get paginated channels with filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search filter
        if (! empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Default filter
        if (isset($filters['is_default'])) {
            $query->where('is_default', $filters['is_default']);
        }

        // Active filter
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';

        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get active channels
     */
    public function getActive(): Collection
    {
        $cacheKey = $this->getCacheKey('active', 'all');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_active', true)->orderBy('name')->get();
        });
    }

    /**
     * Get default channel
     */
    public function getDefault(): ?Channel
    {
        $cacheKey = $this->getCacheKey('default', 'channel');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->where('is_default', true)->first();
        });
    }

    /**
     * Toggle channel status
     */
    public function toggleStatus(int $id): Channel
    {
        $channel = $this->findOrFail($id);
        $newStatus = $channel->is_active ? false : true;
        $channel->update(['is_active' => $newStatus]);

        $this->clearCache();

        return $channel->fresh();
    }

    /**
     * Set as default channel
     */
    public function setAsDefault(int $id): Channel
    {
        // Remove default from all channels
        $this->model->where('is_default', true)->update(['is_default' => false]);
        
        // Set new default
        $channel = $this->findOrFail($id);
        $channel->update(['is_default' => true]);

        $this->clearCache();

        return $channel->fresh();
    }

    /**
     * Check if channel can be deleted
     */
    public function canDelete(int $id): bool
    {
        $channel = $this->find($id);

        if (!$channel) {
            return false;
        }

        // Cannot delete default channel
        if ($channel->is_default) {
            return false;
        }

        // Check if channel has products or orders
        return !$channel->products()->exists() && !$channel->orders()->exists();
    }

    /**
     * Bulk update status
     */
    public function bulkUpdateStatus(array $ids, bool $status): int
    {
        $updated = $this->model->whereIn('id', $ids)->update(['is_active' => $status]);
        $this->clearCache();

        return $updated;
    }
}
