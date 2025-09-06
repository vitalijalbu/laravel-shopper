<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Resources\ProductResource;
use Shopper\Models\Product;
use Shopper\Repositories\ProductRepository;

class ProductController extends ApiController
{
    public function __construct(
        private readonly ProductRepository $productRepository
    ) {}

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category', 'brand', 'min_price', 'max_price',
            'on_sale', 'status', 'is_visible', 'sort'
        ]);
        $perPage = $request->get('per_page', 20);
        
        $products = $this->productRepository->searchPaginated($filters, $perPage);
        
        return $this->paginatedResponse($products);
    }

    public function show(Product $product): JsonResponse
    {
        $productData = $this->productRepository->findWithRelations($product->id, ['brand', 'categories', 'media']);
        
        return $this->successResponse(new ProductResource($productData));
    }

    public function featured(Request $request): JsonResponse
    {
        $products = $this->productRepository->getFeatured();
        
        return $this->successResponse($products);
    }

    public function popular(Request $request): JsonResponse
    {
        $limit = $request->get('limit', 10);
        $products = $this->productRepository->getPopular($limit);
        
        return $this->successResponse($products);
    }

    public function onSale(Request $request): JsonResponse
    {
        $products = $this->productRepository->getOnSale();
        
        return $this->successResponse($products);
    }

    public function related(Product $product, Request $request): JsonResponse
    {
        $limit = $request->get('limit', 4);
        $products = $this->productRepository->getRelated($product, $limit);
        
        return $this->successResponse($products);
    }
}
