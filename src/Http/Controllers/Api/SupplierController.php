<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Contracts\SupplierRepositoryInterface;
use Shopper\Http\Requests\Api\StoreSupplierRequest;
use Shopper\Http\Requests\Api\UpdateSupplierRequest;
use Shopper\Http\Resources\SupplierCollection;
use Shopper\Http\Resources\SupplierResource;
use Shopper\Models\Supplier;

class SupplierController extends ApiController
{
    public function __construct(
        protected SupplierRepositoryInterface $supplierRepository
    ) {}

    /**
     * Display a listing of suppliers
     */
    public function index(Request $request): SupplierCollection
    {
        $filters = $request->only([
            'search',
            'status',
            'country_code',
            'priority',
            'min_rating',
            'is_preferred',
            'is_verified',
            'created_from',
            'created_to',
            'sort',
            'direction',
        ]);

        $perPage = $request->get('per_page', 25);
        $suppliers = $this->supplierRepository->getPaginatedWithFilters($filters, $perPage);

        return new SupplierCollection($suppliers);
    }

    /**
     * Store a newly created supplier
     */
    public function store(StoreSupplierRequest $request): JsonResponse
    {
        try {
            $supplierDto = $request->toDto();
            $supplier = $this->supplierRepository->create($supplierDto->toArray());

            return $this->created(
                new SupplierResource($supplier),
                'Fornitore creato con successo.'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante la creazione del fornitore: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Display the specified supplier
     */
    public function show(Request $request, Supplier $supplier): SupplierResource
    {
        $includes = explode(',', $request->query('include', ''));

        if (in_array('products', $includes)) {
            $supplier = $this->supplierRepository->getWithProducts($supplier->id);
        }

        if (in_array('purchase_orders', $includes)) {
            $supplier = $this->supplierRepository->getWithPurchaseOrders($supplier->id);
        }

        return new SupplierResource($supplier);
    }

    /**
     * Update the specified supplier
     */
    public function update(UpdateSupplierRequest $request, Supplier $supplier): JsonResponse
    {
        try {
            $supplierDto = $request->toDto();
            $updatedSupplier = $this->supplierRepository->update($supplier->id, $supplierDto->toArray());

            return $this->updated(
                new SupplierResource($updatedSupplier),
                'Fornitore aggiornato con successo.'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'aggiornamento del fornitore: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Remove the specified supplier
     */
    public function destroy(Supplier $supplier): JsonResponse
    {
        try {
            if (! $this->supplierRepository->canDelete($supplier->id)) {
                return $this->error(
                    'Impossibile eliminare il fornitore: ha prodotti o ordini d\'acquisto associati.',
                    422
                );
            }

            $this->supplierRepository->delete($supplier->id);

            return $this->deleted('Fornitore eliminato con successo.');
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'eliminazione del fornitore: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Toggle supplier status
     */
    public function toggleStatus(Supplier $supplier): JsonResponse
    {
        try {
            $updatedSupplier = $this->supplierRepository->toggleStatus($supplier->id);

            return $this->success(
                new SupplierResource($updatedSupplier),
                'Stato del fornitore aggiornato con successo.'
            );
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'aggiornamento dello stato: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get supplier products
     */
    public function products(Supplier $supplier): JsonResponse
    {
        try {
            $products = $this->supplierRepository->getSupplierProducts($supplier->id);

            return $this->success($products->map(fn ($product) => [
                'id' => $product->id,
                'name' => $product->name,
                'sku' => $product->sku,
                'price' => $product->price_amount,
                'status' => $product->status,
                'created_at' => $product->created_at?->toISOString(),
            ]));
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante il recupero dei prodotti: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get supplier purchase orders
     */
    public function purchaseOrders(Supplier $supplier): JsonResponse
    {
        try {
            $orders = $this->supplierRepository->getSupplierPurchaseOrders($supplier->id);

            return $this->success($orders->map(fn ($order) => [
                'id' => $order->id,
                'order_number' => $order->order_number,
                'status' => $order->status,
                'total_amount' => $order->total_amount,
                'order_date' => $order->order_date?->toISOString(),
                'expected_delivery_date' => $order->expected_delivery_date?->toISOString(),
                'delivery_date' => $order->delivery_date?->toISOString(),
                'delivered_on_time' => $order->delivered_on_time,
                'created_at' => $order->created_at?->toISOString(),
            ]));
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante il recupero degli ordini d\'acquisto: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get supplier performance metrics
     */
    public function performance(Supplier $supplier): JsonResponse
    {
        try {
            $metrics = $this->supplierRepository->calculatePerformanceMetrics($supplier->id);

            return $this->success($metrics);
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante il calcolo delle metriche di performance: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Bulk activate suppliers
     */
    public function bulkActivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:shopper_suppliers,id'],
        ]);

        try {
            $updated = $this->supplierRepository->bulkUpdateStatus($request->ids, 'active');

            return $this->success([
                'updated_count' => $updated,
                'message' => "Attivati {$updated} fornitori con successo.",
            ]);
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'attivazione dei fornitori: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Bulk deactivate suppliers
     */
    public function bulkDeactivate(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:shopper_suppliers,id'],
        ]);

        try {
            $updated = $this->supplierRepository->bulkUpdateStatus($request->ids, 'inactive');

            return $this->success([
                'updated_count' => $updated,
                'message' => "Disattivati {$updated} fornitori con successo.",
            ]);
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante la disattivazione dei fornitori: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Bulk delete suppliers
     */
    public function bulkDelete(Request $request): JsonResponse
    {
        $request->validate([
            'ids' => ['required', 'array'],
            'ids.*' => ['integer', 'exists:shopper_suppliers,id'],
        ]);

        try {
            $deletedCount = 0;
            $errorMessages = [];

            foreach ($request->ids as $id) {
                if ($this->supplierRepository->canDelete($id)) {
                    $this->supplierRepository->delete($id);
                    $deletedCount++;
                } else {
                    $supplier = $this->supplierRepository->find($id);
                    $errorMessages[] = "Fornitore '{$supplier?->name}' non può essere eliminato (ha relazioni attive).";
                }
            }

            if (empty($errorMessages)) {
                return $this->success([
                    'deleted_count' => $deletedCount,
                    'message' => "Eliminati {$deletedCount} fornitori con successo.",
                ]);
            }

            return $this->success([
                'deleted_count' => $deletedCount,
                'errors' => $errorMessages,
                'message' => "Eliminati {$deletedCount} fornitori. Alcuni non sono stati eliminati per vincoli di integrità.",
            ]);
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'eliminazione dei fornitori: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Export suppliers data
     */
    public function bulkExport(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search',
            'status',
            'country_code',
            'priority',
            'min_rating',
            'is_preferred',
            'is_verified',
            'created_from',
            'created_to',
        ]);

        try {
            $suppliers = $this->supplierRepository->getPaginatedWithFilters($filters, 10000);

            $exportData = collect($suppliers->items())->map(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'code' => $supplier->code,
                'email' => $supplier->email,
                'phone' => $supplier->phone,
                'contact_person' => $supplier->contact_person,
                'country_code' => $supplier->country_code,
                'status' => $supplier->status,
                'priority' => $supplier->priority,
                'rating' => $supplier->rating,
                'is_preferred' => $supplier->is_preferred ? 'Sì' : 'No',
                'is_verified' => $supplier->is_verified ? 'Sì' : 'No',
                'created_at' => $supplier->created_at?->format('d/m/Y H:i'),
            ]);

            return $this->success([
                'data' => $exportData,
                'total' => $exportData->count(),
                'exported_at' => now()->toISOString(),
            ]);
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante l\'esportazione dei fornitori: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get active suppliers for select options
     */
    public function select(): JsonResponse
    {
        try {
            $suppliers = $this->supplierRepository->getActive();

            return $this->success($suppliers->map(fn ($supplier) => [
                'value' => $supplier->id,
                'label' => $supplier->name,
                'code' => $supplier->code,
                'rating' => $supplier->rating,
                'is_preferred' => $supplier->is_preferred,
            ]));
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante il recupero dei fornitori: '.$e->getMessage(),
                500
            );
        }
    }

    /**
     * Get top performing suppliers
     */
    public function topPerformers(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);

        try {
            $suppliers = $this->supplierRepository->getTopPerformers($limit);

            return $this->success($suppliers->map(fn ($supplier) => [
                'id' => $supplier->id,
                'name' => $supplier->name,
                'rating' => $supplier->rating,
                'on_time_delivery_rate' => $supplier->on_time_delivery_rate,
                'total_orders' => $supplier->purchaseOrders()->count(),
            ]));
        } catch (\Exception $e) {
            return $this->error(
                'Errore durante il recupero dei top performers: '.$e->getMessage(),
                500
            );
        }
    }
}
