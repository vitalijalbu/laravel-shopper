<?php

declare(strict_types=1);

namespace Shopper\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Shopper\Models\Supplier;

interface SupplierRepositoryInterface extends RepositoryInterface
{
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 25): LengthAwarePaginator;

    public function findByCode(string $code): ?Supplier;

    public function getActive(): Collection;

    public function getPreferred(): Collection;

    public function getByCountry(string $countryCode): Collection;

    public function getByPriority(string $priority): Collection;

    public function getByRating(float $minRating): Collection;

    public function updateRating(int $id, float $rating): bool;

    public function getTopPerformers(int $limit = 10): Collection;

    public function getWithProducts(int $id): ?Supplier;

    public function getWithPurchaseOrders(int $id): ?Supplier;

    public function bulkUpdateStatus(array $ids, string $status): int;

    public function getSupplierProducts(int $supplierId): Collection;

    public function getSupplierPurchaseOrders(int $supplierId): Collection;

    public function calculatePerformanceMetrics(int $id): array;

    public function toggleStatus(int $id): Supplier;

    public function canDelete(int $id): bool;
}
