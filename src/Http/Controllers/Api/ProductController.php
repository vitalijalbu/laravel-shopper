<?php

namespace VitaliJalbu\LaravelShopper\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use VitaliJalbu\LaravelShopper\Models\Product;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['brand', 'categories', 'media'])
            ->where('is_active', true);

        // Search
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('sku', 'LIKE', "%{$search}%");
            });
        }

        // Filter by category
        if ($category = $request->get('category')) {
            $query->whereHas('categories', function ($q) use ($category) {
                $q->where('slug', $category);
            });
        }

        // Filter by brand
        if ($brand = $request->get('brand')) {
            $query->whereHas('brand', function ($q) use ($brand) {
                $q->where('slug', $brand);
            });
        }

        // Price range
        if ($minPrice = $request->get('min_price')) {
            $query->where('price', '>=', $minPrice * 100);
        }

        if ($maxPrice = $request->get('max_price')) {
            $query->where('price', '<=', $maxPrice * 100);
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');
        
        $allowedSorts = ['name', 'price', 'created_at'];
        if (in_array($sort, $allowedSorts)) {
            $query->orderBy($sort, $direction);
        }

        $products = $query->paginate($request->get('per_page', 20));

        return response()->json([
            'data' => $products->items(),
            'meta' => [
                'current_page' => $products->currentPage(),
                'last_page' => $products->lastPage(),
                'per_page' => $products->perPage(),
                'total' => $products->total(),
            ],
        ]);
    }

    public function show(Product $product): JsonResponse
    {
        if (!$product->is_active) {
            return response()->json([
                'message' => 'Product not found.'
            ], 404);
        }

        $product->load([
            'brand',
            'categories',
            'variants',
            'media',
        ]);

        return response()->json([
            'data' => $product,
        ]);
    }
}
