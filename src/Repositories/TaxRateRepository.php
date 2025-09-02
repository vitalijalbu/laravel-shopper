<?php

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Shopper\Models\TaxRate;

class TaxRateRepository extends BaseRepository
{
    protected string $cachePrefix = 'tax_rates';

    protected function makeModel(): Model
    {
        return new TaxRate();
    }

    /**
     * Get paginated tax rates with filters
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search filter
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if (!empty($filters['status'])) {
            if ($filters['status'] === 'active') {
                $query->active();
            } elseif ($filters['status'] === 'enabled') {
                $query->enabled();
            } elseif ($filters['status'] === 'disabled') {
                $query->where('is_enabled', false);
            }
        }

        // Type filter
        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        // Country filter
        if (!empty($filters['country'])) {
            $query->whereJsonContains('countries', strtoupper($filters['country']));
        }

        // Sorting
        $sortField = $filters['sort'] ?? 'name';
        $sortDirection = $filters['direction'] ?? 'asc';
        $query->orderBy($sortField, $sortDirection);

        return $query->paginate($perPage);
    }

    /**
     * Get active tax rates for a location
     */
    public function getActiveForLocation(string $countryCode, string $stateCode = null, string $postcode = null): \Illuminate\Database\Eloquent\Collection
    {
        $cacheKey = $this->getCacheKey('location', md5($countryCode . '_' . $stateCode . '_' . $postcode));

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () use ($countryCode, $stateCode, $postcode) {
            $query = $this->model->active();

            // Filter by country
            $query->where(function ($q) use ($countryCode) {
                $q->whereJsonContains('countries', strtoupper($countryCode))
                  ->orWhereNull('countries');
            });

            // Filter by state if provided
            if ($stateCode) {
                $query->where(function ($q) use ($countryCode, $stateCode) {
                    $stateKey = strtoupper($countryCode . '_' . $stateCode);
                    $q->whereJsonContains('states', $stateKey)
                      ->orWhereNull('states');
                });
            }

            // Filter by postcode if provided
            if ($postcode) {
                $query->where(function ($q) use ($postcode) {
                    $q->whereNull('postcodes');
                    
                    // Check postcode patterns
                    $taxRates = $this->model->active()->whereNotNull('postcodes')->get();
                    $matchingIds = [];
                    
                    foreach ($taxRates as $rate) {
                        foreach ($rate->postcodes as $pattern) {
                            if (fnmatch($pattern, $postcode)) {
                                $matchingIds[] = $rate->id;
                                break;
                            }
                        }
                    }
                    
                    if (!empty($matchingIds)) {
                        $q->orWhereIn('id', $matchingIds);
                    }
                });
            }

            return $query->orderBy('rate', 'desc')->get();
        });
    }

    /**
     * Calculate tax for an amount
     */
    public function calculateTax(float $amount, string $countryCode, string $stateCode = null, string $postcode = null, array $productCategories = []): array
    {
        $taxRates = $this->getActiveForLocation($countryCode, $stateCode, $postcode);
        
        $totalTax = 0;
        $appliedRates = [];
        $currentAmount = $amount;

        foreach ($taxRates as $rate) {
            // Check if tax applies to product categories
            if (!empty($rate->product_categories) && !empty($productCategories)) {
                $hasMatchingCategory = !empty(array_intersect($rate->product_categories, $productCategories));
                if (!$hasMatchingCategory) {
                    continue;
                }
            }

            // Check amount thresholds
            if ($rate->min_amount && $amount < $rate->min_amount) {
                continue;
            }
            
            if ($rate->max_amount && $amount > $rate->max_amount) {
                continue;
            }

            $taxAmount = 0;
            
            if ($rate->type === 'percentage') {
                $baseAmount = $rate->is_compound ? $currentAmount : $amount;
                $taxAmount = $baseAmount * $rate->rate;
            } else {
                $taxAmount = $rate->rate;
            }

            $totalTax += $taxAmount;
            
            if ($rate->is_compound) {
                $currentAmount += $taxAmount;
            }

            $appliedRates[] = [
                'id' => $rate->id,
                'name' => $rate->name,
                'code' => $rate->code,
                'rate' => $rate->rate,
                'type' => $rate->type,
                'amount' => round($taxAmount, 2),
                'is_inclusive' => $rate->is_inclusive,
                'is_compound' => $rate->is_compound,
            ];
        }

        return [
            'total_tax' => round($totalTax, 2),
            'applied_rates' => $appliedRates,
            'tax_inclusive_amount' => round($amount + $totalTax, 2),
        ];
    }

    /**
     * Create a new tax rate
     */
    public function create(array $data): TaxRate
    {
        $this->clearCache();
        return $this->model->create($data);
    }

    /**
     * Update tax rate
     */
    public function update(int $id, array $attributes): Model
    {
        $this->clearCache();
        
        $taxRate = $this->model->find($id);
        $taxRate->update($attributes);
        
        return $taxRate;
    }

    /**
     * Delete tax rate
     */
    public function delete(int $id): bool
    {
        $this->clearCache();
        return $this->model->find($id)->delete();
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
     * Get all countries with tax rates
     */
    public function getCountries(): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('countries', 'all');
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                'IT' => 'Italia',
                'FR' => 'Francia', 
                'DE' => 'Germania',
                'ES' => 'Spagna',
                'US' => 'Stati Uniti',
                'CA' => 'Canada',
                'GB' => 'Regno Unito',
                'AU' => 'Australia',
                'JP' => 'Giappone',
            ]);
        });
    }

    /**
     * Get tax zones for dropdown
     */
    public function getTaxZones(): \Illuminate\Support\Collection
    {
        $cacheKey = $this->getCacheKey('tax_zones', 'all');
        
        return \Illuminate\Support\Facades\Cache::remember($cacheKey, $this->cacheTtl, function () {
            return collect([
                ['id' => 1, 'name' => 'Europa', 'countries' => ['IT', 'FR', 'DE', 'ES']],
                ['id' => 2, 'name' => 'Nord America', 'countries' => ['US', 'CA']],
                ['id' => 3, 'name' => 'Asia-Pacifico', 'countries' => ['JP', 'AU', 'SG']],
            ]);
        });
    }

    /**
     * Toggle tax rate status
     */
    public function toggleStatus(int $id): ?TaxRate
    {
        $taxRate = $this->model->find($id);
        
        if (!$taxRate) {
            return null;
        }

        $taxRate->update([
            'is_active' => !$taxRate->is_active
        ]);

        $this->clearCache();

        return $taxRate->fresh();
    }

    /**
     * Update priorities for multiple tax rates
     */
    public function updatePriorities(array $taxRates): bool
    {
        try {
            foreach ($taxRates as $taxRateData) {
                $this->model->where('id', $taxRateData['id'])
                    ->update(['priority' => $taxRateData['priority']]);
            }

            $this->clearCache();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Duplicate a tax rate
     */
    public function duplicate(int $id): ?TaxRate
    {
        $originalTaxRate = $this->model->find($id);
        
        if (!$originalTaxRate) {
            return null;
        }

        $duplicatedData = $originalTaxRate->toArray();
        unset($duplicatedData['id'], $duplicatedData['created_at'], $duplicatedData['updated_at']);
        $duplicatedData['name'] = $duplicatedData['name'] . ' (Copia)';

        return $this->create($duplicatedData);
    }
}
