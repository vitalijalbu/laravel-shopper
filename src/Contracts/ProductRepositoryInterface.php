<?php

namespace LaravelShopper\Contracts;

use Illuminate\Database\Eloquent\Collection;
use LaravelShopper\Models\Product;

interface ProductRepositoryInterface extends RepositoryInterface
{
    public function findWithRelations(int $id, array $relations = []): ?Product;

    public function searchPaginated(array $filters, int $perPage = 20): \Illuminate\Pagination\LengthAwarePaginator;

    public function createWithRelations(array $data, array $relations = []): Product;

    public function getByCategory(int $categoryId): Collection;

    public function getByBrand(int $brandId): Collection;

    public function getByCollection(int $collectionId): Collection;

    public function getPublished(): Collection;

    public function getVisible(): Collection;

    public function getOnSale(): Collection;

    public function getFeatured(): Collection;

    public function getPopular(int $limit = 10): Collection;

    public function getRelated(Product $product, int $limit = 4): Collection;

    public function getBySku(string $sku): ?Product;

    public function searchByName(string $name): Collection;

    public function filterByPrice(int $minPrice, int $maxPrice): static;

    public function sortByPopularity(): static;

    public function sortByNewest(): static;

    public function sortByPrice(string $direction = 'asc'): static;
}
