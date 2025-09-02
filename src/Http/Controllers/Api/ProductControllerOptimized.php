<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Shopper\Contracts\ProductRepositoryInterface;
use Shopper\Http\Requests\StoreProductRequest;
use Shopper\Http\Requests\UpdateProductRequest;
use Shopper\Http\Resources\ProductResource;
use Shopper\Jobs\UpdateProductIndexJob;
use Shopper\Services\CacheService;
use Shopper\Services\WebhookService;

class ProductController extends BaseController
{
    public function __construct(
        private ProductRepositoryInterface $productRepository,
        private CacheService $cache,
        private WebhookService $webhooks
    ) {}

    /**
     * Display a listing of products with optimized performance
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'status', 'category_id', 'brand_id',
            'min_price', 'max_price', 'is_visible', 'on_sale',
            'sort_by', 'sort_direction',
        ]);

        $perPage = min($request->get('per_page', 20), 100); // Max 100 per page

        // Use repository with caching
        $products = $this->productRepository
            ->with(['category', 'brand', 'media']) // Eager load relationships
            ->searchPaginated($filters, $perPage);

        return $this->successResponse([
            'data' => ProductResource::collection($products->items()),
            'meta' => [
                'current_page' => $products->currentPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
                'last_page' => $products->lastPage(),
            ],
            'links' => [
                'first' => $products->url(1),
                'last' => $products->url($products->lastPage()),
                'prev' => $products->previousPageUrl(),
                'next' => $products->nextPageUrl(),
            ],
        ]);
    }

    /**
     * Store a new product with optimized creation
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $productData = $request->validated();
            $relations = $request->get('relations', []);

            // Create product with relations
            $product = $this->productRepository->createWithRelations($productData, $relations);

            // Clear related cache
            $this->cache->invalidateProduct();
            $this->cache->invalidateCategory();

            // Index for search
            UpdateProductIndexJob::dispatch($product, 'update');

            // Dispatch webhook
            $this->webhooks->dispatch('product.created', [
                'product' => $product->toArray(),
            ]);

            DB::commit();

            return $this->successResponse([
                'message' => 'Product created successfully',
                'data' => new ProductResource($product),
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to create product: '.$e->getMessage(), 500);
        }
    }

    /**
     * Display the specified product with caching
     */
    public function show(int $id): JsonResponse
    {
        $product = $this->cache->rememberProduct("show_{$id}", function () use ($id) {
            return $this->productRepository->findWithRelations($id, [
                'category', 'brand', 'collections', 'variants', 'media', 'reviews',
            ]);
        });

        if (! $product) {
            return $this->errorResponse('Product not found', 404);
        }

        // Increment view count asynchronously
        $this->incrementViewCount($product);

        return $this->successResponse([
            'data' => new ProductResource($product),
        ]);
    }

    /**
     * Update the specified product with optimized updates
     */
    public function update(UpdateProductRequest $request, int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = $this->productRepository->findOrFail($id);
            $originalData = $product->toArray();

            $updateData = $request->validated();
            $updatedProduct = $this->productRepository->update($id, $updateData);

            // Handle relations if provided
            if ($request->has('relations')) {
                $relations = $request->get('relations');

                if (isset($relations['collections'])) {
                    $updatedProduct->collections()->sync($relations['collections']);
                }

                if (isset($relations['tags'])) {
                    $updatedProduct->tags()->sync($relations['tags']);
                }
            }

            // Clear related cache
            $this->cache->invalidateProduct($id);

            // Update search index
            UpdateProductIndexJob::dispatch($updatedProduct, 'update');

            // Dispatch webhook with changes
            $this->webhooks->dispatch('product.updated', [
                'product' => $updatedProduct->toArray(),
                'changes' => array_diff_assoc($updateData, $originalData),
            ]);

            DB::commit();

            return $this->successResponse([
                'message' => 'Product updated successfully',
                'data' => new ProductResource($updatedProduct->fresh()),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to update product: '.$e->getMessage(), 500);
        }
    }

    /**
     * Remove the specified product with cleanup
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            DB::beginTransaction();

            $product = $this->productRepository->findOrFail($id);
            $productData = $product->toArray();

            // Soft delete the product
            $this->productRepository->delete($id);

            // Clear cache
            $this->cache->invalidateProduct($id);

            // Remove from search index
            UpdateProductIndexJob::dispatch($product, 'delete');

            // Dispatch webhook
            $this->webhooks->dispatch('product.deleted', [
                'product' => $productData,
            ]);

            DB::commit();

            return $this->successResponse([
                'message' => 'Product deleted successfully',
            ], 204);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Failed to delete product: '.$e->getMessage(), 500);
        }
    }

    /**
     * Bulk operations for better performance
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'products' => 'required|array|max:100', // Limit bulk operations
            'products.*.id' => 'required|integer|exists:shopper_products,id',
            'products.*.action' => 'required|in:update,delete,publish,unpublish',
            'products.*.data' => 'array',
        ]);

        try {
            DB::beginTransaction();

            $results = [];

            foreach ($request->products as $item) {
                $productId = $item['id'];
                $action = $item['action'];
                $data = $item['data'] ?? [];

                switch ($action) {
                    case 'update':
                        $product = $this->productRepository->update($productId, $data);
                        $results[] = ['id' => $productId, 'status' => 'updated'];
                        break;

                    case 'delete':
                        $this->productRepository->delete($productId);
                        $results[] = ['id' => $productId, 'status' => 'deleted'];
                        break;

                    case 'publish':
                        $product = $this->productRepository->update($productId, ['status' => 'published']);
                        $results[] = ['id' => $productId, 'status' => 'published'];
                        break;

                    case 'unpublish':
                        $product = $this->productRepository->update($productId, ['status' => 'draft']);
                        $results[] = ['id' => $productId, 'status' => 'unpublished'];
                        break;
                }
            }

            // Clear all product cache
            $this->cache->invalidateProduct();

            DB::commit();

            return $this->successResponse([
                'message' => 'Bulk operation completed',
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return $this->errorResponse('Bulk operation failed: '.$e->getMessage(), 500);
        }
    }

    /**
     * Increment view count asynchronously
     */
    private function incrementViewCount($product): void
    {
        // Use queue to avoid blocking the response
        dispatch(function () use ($product) {
            $product->increment('views_count');
        })->onQueue('analytics');
    }
}
