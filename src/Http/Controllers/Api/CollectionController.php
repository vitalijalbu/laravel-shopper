<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Repositories\CollectionRepository;

class CollectionController extends ApiController
{
    public function __construct(
        protected CollectionRepository $collectionRepository
    ) {}

    /**
     * Display a listing of collections
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'type']);
        $perPage = $request->get('per_page', 25);

        $collections = $this->collectionRepository->getPaginatedWithFilters($filters, $perPage);

        return response()->json([
            'data' => $collections->items(),
            'meta' => [
                'current_page' => $collections->currentPage(),
                'last_page' => $collections->lastPage(),
                'per_page' => $collections->perPage(),
                'total' => $collections->total(),
                'from' => $collections->firstItem(),
                'to' => $collections->lastItem(),
            ],
            'links' => [
                'first' => $collections->url(1),
                'last' => $collections->url($collections->lastPage()),
                'prev' => $collections->previousPageUrl(),
                'next' => $collections->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a newly created collection
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug',
            'description' => 'nullable|string',
            'type' => 'required|in:manual,automatic',
            'conditions' => 'nullable|array',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $collection = $this->collectionRepository->create($validated);

            return response()->json([
                'message' => 'Collezione creata con successo',
                'data' => $collection,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la creazione della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified collection
     */
    public function show(string $id): JsonResponse
    {
        try {
            $collection = $this->collectionRepository->findWithProducts($id);

            return response()->json([
                'data' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Collezione non trovata',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Update the specified collection
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:collections,slug,'.$id,
            'description' => 'nullable|string',
            'type' => 'required|in:manual,automatic',
            'conditions' => 'nullable|array',
            'image' => 'nullable|string',
            'is_published' => 'boolean',
            'published_at' => 'nullable|date',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:160',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $collection = $this->collectionRepository->update($id, $validated);

            return response()->json([
                'message' => 'Collezione aggiornata con successo',
                'data' => $collection,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiornamento della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified collection
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $this->collectionRepository->delete($id);

            return response()->json([
                'message' => 'Collezione eliminata con successo',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'eliminazione della collezione',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get collection products
     */
    public function products(Request $request, string $id): JsonResponse
    {
        try {
            $perPage = $request->get('per_page', 25);
            $products = $this->collectionRepository->getProducts($id, $perPage);

            return response()->json([
                'data' => $products->items(),
                'meta' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante il recupero dei prodotti',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add products to collection
     */
    public function addProducts(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        try {
            $result = $this->collectionRepository->addProducts($id, $validated['product_ids']);

            return response()->json([
                'message' => "Aggiunti {$result['added']} prodotti alla collezione",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'aggiunta dei prodotti',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove products from collection
     */
    public function removeProducts(Request $request, string $id): JsonResponse
    {
        $validated = $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        try {
            $result = $this->collectionRepository->removeProducts($id, $validated['product_ids']);

            return response()->json([
                'message' => "Rimossi {$result['removed']} prodotti dalla collezione",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante la rimozione dei prodotti',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'action' => 'required|in:delete,publish,unpublish',
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:collections,id',
        ]);

        try {
            $result = $this->collectionRepository->bulkAction($validated['action'], $validated['ids']);

            return response()->json([
                'message' => "Azione '{$validated['action']}' eseguita su {$result['count']} collezioni",
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Errore durante l\'esecuzione dell\'azione bulk',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
