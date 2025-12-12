<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\Api;

use Cartino\DTO\ProductDTO;
use Cartino\Http\Requests\Api\StoreProductRequest;
use Cartino\Http\Requests\Api\UpdateProductRequest;
use Cartino\Http\Resources\ProductResource;
use Cartino\Models\Product;
use Cartino\Repositories\ProductRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductsController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $repository
    ) {}

    /**
     * Display a listing of products
     */
    public function index(Request $request): JsonResponse
    {
        $data = $this->repository->findAll($request->all());

        return $this->paginatedResponse($data);
    }

    /**
     * Display the specified product
     */
    public function show(int|string $handle): JsonResponse
    {
        $data = $this->repository->findOne($handle);

        return $this->successResponse(new ProductResource($data));
    }

    /**
     * Store a newly created product
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            $product = $this->repository->createOne($request->validated());

            return $this->created(new ProductResource($product), 'Product creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nella creazione del product: '.$e->getMessage());
        }
    }

    /**
     * Update the specified product
     */
    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->repository->updateOne($product->id, $request->validated());

            return $this->successResponse(new ProductResource($updatedProduct), 'Product aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'aggiornamento del product: '.$e->getMessage());
        }
    }

    /**
     * Remove the specified product
     */
    public function destroy(Product $product): JsonResponse
    {
        try {
            if (!$this->repository->canDelete($product->id)) {
                return $this->errorResponse('Impossibile eliminare il product: ha relazioni attive', 422);
            }

            $this->repository->deleteOne($product->id);

            return $this->successResponse(null, 'Product eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nell\'eliminazione del product: '.$e->getMessage());
        }
    }

    /**
     * Toggle product status
     */
    public function toggleStatus(Product $product): JsonResponse
    {
        try {
            $updatedProduct = $this->repository->toggleStatus($product->id);

            return $this->successResponse(new ProductResource($updatedProduct), 'Stato del product aggiornato');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore nel cambio stato: '.$e->getMessage());
        }
    }
}
