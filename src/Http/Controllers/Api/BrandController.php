<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Requests\Api\StoreBrandRequest;
use Shopper\Http\Requests\Api\UpdateBrandRequest;
use Shopper\Http\Resources\BrandResource;
use Shopper\Http\Resources\BrandCollection;
use Shopper\Models\Brand;
use Shopper\Repositories\BrandRepository;

class BrandController extends ApiController
{
    public function __construct(
        private readonly BrandRepository $brandRepository
    ) {}

    /**
     * Display a listing of brands
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'is_featured', 'status']);
        $perPage = $request->get('per_page', 25);
        
        $brands = $this->brandRepository->getPaginatedWithFilters($filters, $perPage);
        
        return $this->paginatedResponse($brands);
    }

    /**
     * Store a newly created brand
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        try {
            $brand = $this->brandRepository->create($request->validated());
            
            return $this->created(new BrandResource($brand), 'Brand creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del brand: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified brand
     */
    public function show(Brand $brand): JsonResponse
    {
        return $this->successResponse(new BrandResource($brand));
    }

    /**
     * Update the specified brand
     */
    public function update(UpdateBrandRequest $request, Brand $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->brandRepository->update($brand->id, $request->validated());
            
            return $this->successResponse(new BrandResource($updatedBrand), 'Brand aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del brand: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy(Brand $brand): JsonResponse
    {
        try {
            if (!$this->brandRepository->canDelete($brand->id)) {
                return $this->errorResponse('Impossibile eliminare il brand: è associato a dei prodotti', 422);
            }

            $this->brandRepository->delete($brand->id);
            
            return $this->successResponse(null, 'Brand eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del brand: ' . $e->getMessage());
        }
    }

    /**
     * Toggle brand status
     */
    public function toggleStatus(Brand $brand): JsonResponse
    {
        try {
            $updatedBrand = $this->brandRepository->toggleStatus($brand->id);
            
            return $this->successResponse(new BrandResource($updatedBrand), 'Stato del brand aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: ' . $e->getMessage());
        }
    }

    /**
     * Get brand products
     */
    public function products(Brand $brand): JsonResponse
    {
        $products = $this->brandRepository->getBrandProducts($brand->id);
        
        return $this->successResponse($products);
    }

    /**
     * Bulk operations
     */
    public function bulk(Request $request): JsonResponse
    {
        $action = $request->get('action');
        $ids = $request->get('ids', []);

        if (empty($ids)) {
            return $this->validationErrorResponse('Nessun ID selezionato');
        }

        try {
            switch ($action) {
                case 'activate':
                    $count = $this->brandRepository->bulkUpdateStatus($ids, 'active');
                    return $this->bulkActionResponse('attivazione', $count);

                case 'deactivate':
                    $count = $this->brandRepository->bulkUpdateStatus($ids, 'inactive');
                    return $this->bulkActionResponse('disattivazione', $count);

                case 'delete':
                    $errors = [];
                    $deleted = 0;
                    
                    foreach ($ids as $id) {
                        if ($this->brandRepository->canDelete($id)) {
                            $this->brandRepository->delete($id);
                            $deleted++;
                        } else {
                            $errors[] = "Brand ID {$id} non può essere eliminato";
                        }
                    }
                    
                    return $this->bulkActionResponse('eliminazione', $deleted, $errors);

                default:
                    return $this->validationErrorResponse('Azione non riconosciuta');
            }
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'operazione bulk: ' . $e->getMessage());
        }
    }
}
