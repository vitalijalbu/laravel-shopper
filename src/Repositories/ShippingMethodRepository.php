<?php

namespace LaravelShopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use LaravelShopper\Models\ShippingMethod;

class ShippingMethodRepository extends BaseRepository
{
    protected string $cachePrefix = 'shipping_methods';

    protected function makeModel(): Model
    {
        return new ShippingMethod();
    }

    /**
     * Get paginated shipping methods with filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery()->with(['zones']);

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('carrier', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (isset($filters['is_enabled'])) {
            $query->where('is_enabled', $filters['is_enabled']);
        }

        // Carrier filter
        if (!empty($filters['carrier'])) {
            $query->where('carrier', $filters['carrier']);
        }

        // Calculation type filter
        if (!empty($filters['calculation_type'])) {
            $query->where('calculation_type', $filters['calculation_type']);
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'sort_order';
        $sortDirection = $filters['direction'] ?? 'asc';
        
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get enabled shipping methods
     */
    public function getEnabled(): Collection
    {
        $cacheKey = $this->getCacheKey('enabled', '');

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return $this->model->with(['zones'])
                              ->where('is_enabled', true)
                              ->orderBy('sort_order')
                              ->get();
        });
    }

    /**
     * Get shipping methods available for location
     */
    public function getAvailableForLocation(string $country, ?string $state = null): Collection
    {
        $cacheKey = $this->getCacheKey('location', $country . '_' . $state);

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($country, $state) {
            return $this->model->with(['zones'])
                              ->where('is_enabled', true)
                              ->whereHas('zones', function ($query) use ($country, $state) {
                                  $query->where('countries', 'like', "%{$country}%");
                                  if ($state) {
                                      $query->orWhere('states', 'like', "%{$state}%");
                                  }
                              })
                              ->orderBy('sort_order')
                              ->get();
        });
    }

    /**
     * Calculate shipping cost
     */
    public function calculateShippingCost(int $methodId, array $cartData): float
    {
        $method = $this->find($methodId);
        
        if (!$method || !$method->is_enabled) {
            return 0;
        }

        $weight = $cartData['weight'] ?? 0;
        $subtotal = $cartData['subtotal'] ?? 0;
        $itemCount = $cartData['item_count'] ?? 0;

        switch ($method->calculation_type) {
            case 'fixed':
                return $method->base_cost;

            case 'weight_based':
                $config = $method->config ?? [];
                $costPerKg = $config['cost_per_kg'] ?? 0;
                return $method->base_cost + ($weight * $costPerKg);

            case 'order_total':
                $config = $method->config ?? [];
                $percentage = $config['percentage'] ?? 0;
                return $method->base_cost + ($subtotal * $percentage / 100);

            case 'item_count':
                $config = $method->config ?? [];
                $costPerItem = $config['cost_per_item'] ?? 0;
                return $method->base_cost + ($itemCount * $costPerItem);

            case 'free_over_amount':
                $config = $method->config ?? [];
                $freeOverAmount = $config['free_over_amount'] ?? 0;
                return $subtotal >= $freeOverAmount ? 0 : $method->base_cost;

            default:
                return $method->base_cost;
        }
    }

    /**
     * Get carriers for filters
     */
    public function getCarriers(): Collection
    {
        return $this->model->select('carrier')
                          ->distinct()
                          ->whereNotNull('carrier')
                          ->orderBy('carrier')
                          ->pluck('carrier');
    }

    /**
     * Get calculation types
     */
    public function getCalculationTypes(): array
    {
        return [
            'fixed' => 'Tariffa fissa',
            'weight_based' => 'Basato sul peso',
            'order_total' => 'Basato sul totale ordine',
            'item_count' => 'Basato sul numero di articoli',
            'free_over_amount' => 'Gratuito sopra un importo',
        ];
    }

    /**
     * Toggle shipping method status
     */
    public function toggleStatus(int $id): ?ShippingMethod
    {
        $shippingMethod = $this->model->find($id);
        
        if (!$shippingMethod) {
            return null;
        }

        $shippingMethod->update([
            'is_enabled' => !$shippingMethod->is_enabled
        ]);

        $this->clearCache();

        return $shippingMethod->fresh();
    }

    /**
     * Update sort order for multiple shipping methods
     */
    public function updateSortOrder(array $shippingMethods): bool
    {
        try {
            foreach ($shippingMethods as $methodData) {
                $this->model->where('id', $methodData['id'])
                    ->update(['sort_order' => $methodData['sort_order']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Update method configuration
     */
    public function updateConfig(int $id, array $config): Model
    {
        $method = $this->find($id);
        $currentConfig = $method->config ?? [];
        $method->update(['config' => array_merge($currentConfig, $config)]);
        
        $this->clearCache();
        
        return $method;
    }

    /**
     * Clear repository cache
     */
    protected function clearCache(): void
    {
        $tags = [$this->cachePrefix];
        \Illuminate\Support\Facades\Cache::tags($tags)->flush();
    }

    /**
     * Get available shipping zones
     */
    public function getShippingZones(): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('shipping_zones', 'all');
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                ['code' => 'IT', 'name' => 'Italia', 'region' => 'Europa'],
                ['code' => 'FR', 'name' => 'Francia', 'region' => 'Europa'],
                ['code' => 'DE', 'name' => 'Germania', 'region' => 'Europa'],
                ['code' => 'ES', 'name' => 'Spagna', 'region' => 'Europa'],
                ['code' => 'US', 'name' => 'Stati Uniti', 'region' => 'Nord America'],
                ['code' => 'CA', 'name' => 'Canada', 'region' => 'Nord America'],
                ['code' => 'GB', 'name' => 'Regno Unito', 'region' => 'Europa'],
                ['code' => 'AU', 'name' => 'Australia', 'region' => 'Oceania'],
                ['code' => 'JP', 'name' => 'Giappone', 'region' => 'Asia'],
            ]);
        });
    }

    /**
     * Get available shipping types
     */
    public function getShippingTypes(): \Illuminate\Support\Collection
    {
        return collect([
            [
                'value' => 'flat_rate',
                'label' => 'Tariffa Fissa',
                'description' => 'Costo fisso per tutte le spedizioni'
            ],
            [
                'value' => 'free',
                'label' => 'Spedizione Gratuita',
                'description' => 'Nessun costo di spedizione'
            ],
            [
                'value' => 'local_pickup',
                'label' => 'Ritiro in Negozio',
                'description' => 'Cliente ritira in negozio'
            ],
            [
                'value' => 'weight_based',
                'label' => 'Basato sul Peso',
                'description' => 'Costo basato sul peso del pacco'
            ],
            [
                'value' => 'zone_based',
                'label' => 'Basato su Zone',
                'description' => 'Costo basato sulla zona di destinazione'
            ]
        ]);
    }

    /**
     * Duplicate a shipping method
     */
    public function duplicate(int $id): ?ShippingMethod
    {
        $originalMethod = $this->model->find($id);
        
        if (!$originalMethod) {
            return null;
        }

        $duplicatedData = $originalMethod->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);
        $duplicatedData['name'] = $duplicatedData['name'] . ' (Copia)';
        $duplicatedData['slug'] = $duplicatedData['slug'] . '-copy-' . time();

        return $this->create($duplicatedData);
    }

    /**
     * Get cache key
     */
    protected function getCacheKey(string $method, mixed $identifier): string
    {
        return $this->cachePrefix . '_' . $method . ($identifier ? '_' . $identifier : '');
    }
}
