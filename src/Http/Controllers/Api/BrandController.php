<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\Controller;
use Shopper\Http\Requests\Api\StoreBrandRequest;
use Shopper\Http\Requests\Api\UpdateBrandRequest;
use Shopper\Models\Brand;
use Shopper\Traits\ApiResponseTrait;

class BrandController extends Controller
{
    use ApiResponseTrait;

    /**
     * Display a listing of brands
     */
    public function index(Request $request): JsonResponse
    {
        $query = Brand::query();

        // Search filter
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($request->has('is_enabled')) {
            $query->where('is_enabled', $request->boolean('is_enabled'));
        }

        // Featured filter
        if ($request->has('is_featured')) {
            $query->where('is_featured', $request->boolean('is_featured'));
        }

        $perPage = $request->get('per_page', 25);
        $brands = $query->orderBy('name')->paginate($perPage);

        return $this->paginatedResponse($brands);
    }

    /**
     * Store a newly created brand
     */
    public function store(StoreBrandRequest $request): JsonResponse
    {
        $validated = $request->validated();

        // Generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Illuminate\Support\Str::slug($validated['name']);
        }

        try {
            $brand = Brand::create($validated);

            return $this->createdResponse($brand, 'Brand creato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante la creazione del brand');
        }
    }

    /**
     * Display the specified brand
     */
    public function show(string $id): JsonResponse
    {
        try {
            $brand = Brand::with('products')->findOrFail($id);

            return $this->successResponse($brand);
        } catch (\Exception $e) {
            return $this->notFoundResponse('Brand non trovato');
        }
    }

    /**
     * Update the specified brand
     */
    public function update(UpdateBrandRequest $request, string $id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);
            $brand->update($request->validated());

            return $this->successResponse($brand->fresh(), 'Brand aggiornato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'aggiornamento del brand');
        }
    }

    /**
     * Remove the specified brand
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $brand = Brand::findOrFail($id);

            // Check if brand has products
            if ($brand->products()->exists()) {
                return $this->validationErrorResponse('Impossibile eliminare il brand con prodotti associati');
            }

            $brand->delete();

            return $this->successResponse(null, 'Brand eliminato con successo');
        } catch (\Exception $e) {
            return $this->errorResponse('Errore durante l\'eliminazione del brand');
        }
    }
}
