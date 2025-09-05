<?php

namespace Shopper\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Shopper\Contracts\ProductRepositoryInterface;
use Shopper\Models\Product;

class ProductRepository extends BaseRepository implements ProductRepositoryInterface
{
    protected string $cachePrefix = 'products';

    protected int $cacheTtl = 3600; // 1 hour

    protected function makeModel(): Model
    {
        return new Product;
    }

    public function findWithRelations(int $id, array $relations = []): ?Product
    {
        if (! empty($relations)) {
            $this->with($relations);
        }

        return $this->find($id);
    }

    public function searchPaginated(array $filters, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator
    {
        $query = $this->model->newQuery();

        // Search term
        if (! empty($filters['search'])) {
            $searchTerm = $filters['search'];
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                    ->orWhere('sku', 'like', "%{$searchTerm}%")
                    ->orWhere('description', 'like', "%{$searchTerm}%");
            });
        }

        // Status filter
        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        // Category filter
        if (! empty($filters['category_id'])) {
            $query->where('shopper_category_id', $filters['category_id']);
        }

        // Brand filter
        if (! empty($filters['brand_id'])) {
            $query->where('shopper_brand_id', $filters['brand_id']);
        }

        // Price range filter
        if (! empty($filters['min_price'])) {
            $query->where('price_amount', '>=', $filters['min_price']);
        }

        if (! empty($filters['max_price'])) {
            $query->where('price_amount', '<=', $filters['max_price']);
        }

        // Visibility filter
        if (isset($filters['is_visible'])) {
            $query->where('is_visible', $filters['is_visible']);
        }

        // On sale filter
        if (! empty($filters['on_sale'])) {
            $query->whereNotNull('sale_price_amount');
        }

        // Sorting
        $sortBy = $filters['sort_by'] ?? 'created_at';
        $sortDirection = $filters['sort_direction'] ?? 'desc';

        switch ($sortBy) {
            case 'name':
                $query->orderBy('name', $sortDirection);
                break;
            case 'price':
                $query->orderBy('price_amount', $sortDirection);
                break;
            case 'popularity':
                // Assuming you have a views or orders count
                $query->orderBy('views_count', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
        }

        return $query->with(['category', 'brand'])->paginate($perPage);
    }

    public function createWithRelations(array $data, array $relations = []): Product
    {
        $product = $this->create($data);

        // Handle relations
        if (! empty($relations['categories'])) {
            $product->categories()->sync($relations['categories']);
        }

        if (! empty($relations['collections'])) {
            $product->collections()->sync($relations['collections']);
        }

        if (! empty($relations['tags'])) {
            $product->tags()->sync($relations['tags']);
        }

        return $product->load(['category', 'brand', 'collections', 'tags']);
    }

    public function getByCategory(int $categoryId): Collection
    {
        return $this->findWhere(['shopper_category_id' => $categoryId]);
    }

    public function getByBrand(int $brandId): Collection
    {
        return $this->findWhere(['shopper_brand_id' => $brandId]);
    }

    public function getByCollection(int $collectionId): Collection
    {
        return $this->whereHas('collections', function ($query) use ($collectionId) {
            $query->where('id', $collectionId);
        })->all();
    }

    public function getPublished(): Collection
    {
        return $this->findWhere(['status' => 'published']);
    }

    public function getVisible(): Collection
    {
        return $this->findWhere(['is_visible' => true]);
    }

    public function getOnSale(): Collection
    {
        return $this->model->whereNotNull('sale_price_amount')
            ->where('sale_price_amount', '>', 0)
            ->get();
    }

    public function getFeatured(): Collection
    {
        return $this->findWhere(['is_featured' => true]);
    }

    public function getPopular(int $limit = 10): Collection
    {
        return $this->model->orderBy('views_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public function getRelated(Product $product, int $limit = 4): Collection
    {
        $query = $this->model->where('id', '!=', $product->id);

        // Same category
        if ($product->shopper_category_id) {
            $query->where('shopper_category_id', $product->shopper_category_id);
        }

        // Same brand
        if ($product->shopper_brand_id) {
            $query->orWhere('shopper_brand_id', $product->shopper_brand_id);
        }

        return $query->published()
            ->visible()
            ->limit($limit)
            ->get();
    }

    public function getBySku(string $sku): ?Product
    {
        return $this->findWhereFirst(['sku' => $sku]);
    }

    public function searchByName(string $name): Collection
    {
        return $this->model->where('name', 'like', "%{$name}%")->get();
    }

    public function search(string $term): static
    {
        $this->query->where(function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('description', 'like', "%{$term}%");
        });

        return $this;
    }

    public function filterByPrice(int $minPrice, int $maxPrice): static
    {
        $this->query->whereBetween('price_amount', [$minPrice, $maxPrice]);

        return $this;
    }

    public function sortByPopularity(): static
    {
        $this->query->orderBy('views_count', 'desc');

        return $this;
    }

    public function sortByNewest(): static
    {
        $this->query->orderBy('created_at', 'desc');

        return $this;
    }

    public function sortByPrice(string $direction = 'asc'): static
    {
        $this->query->orderBy('price_amount', $direction);

        return $this;
    }

    public function canDelete(int $id): bool
    {
        $product = $this->find($id);
        
        if (!$product) {
            return false;
        }

        // Check if product has orders
        if ($product->orders()->exists()) {
            return false;
        }

        // Check if product has variants
        if ($product->variants()->exists()) {
            return false;
        }

        return true;
    }

    public function bulkUpdate(array $ids, array $data): int
    {
        return $this->model->whereIn('id', $ids)->update($data);
    }

    public function bulkDelete(array $ids): int
    {
        $count = 0;
        
        foreach ($ids as $id) {
            if ($this->canDelete($id)) {
                $this->delete($id);
                $count++;
            }
        }

        return $count;
    }

    public function bulkExport(array $ids): int
    {
        // TODO: Implement actual export logic
        return count($ids);
    }
}
