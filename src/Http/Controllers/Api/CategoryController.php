<?php

namespace Shopper\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\Controller;
use Shopper\Http\Requests\Api\BulkActionRequest;
use Shopper\Http\Requests\Api\StoreCategoryRequest;
use Shopper\Http\Requests\Api\UpdateCategoryRequest;
use Shopper\Http\Traits\ApiResponseTrait;
use Shopper\Repositories\CategoryRepository;

class CategoryController extends Controller
{
    use ApiResponseTrait;
    public function __construct(
        protected CategoryRepository $categoryRepository
    ) {}

    /**
     * Display a listing of categories
     */
    public function index(Request $request): JsonResponse
    {
        $filters = $request->only(['search', 'status', 'parent_id']);
        $perPage = $request->get('per_page', 25);

        $categories = $this->categoryRepository->getPaginatedWithFilters($filters, $perPage);

        return $this->paginatedResponse($categories);
    }

    /**
     * Store a newly created category
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $category = $this->categoryRepository->create($validated);

            return response()->json([
                'message' => 'Category created successfully',
                'data' => $category
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified category
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = $this->categoryRepository->findWithRelations($id, ['children', 'products']);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the specified category
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|unique:categories,slug,' . $id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'image' => 'nullable|string',
            'is_active' => 'boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        try {
            $category = $this->categoryRepository->update($id, $validated);

            if (!$category) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Category updated successfully',
                'data' => $category
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified category
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $deleted = $this->categoryRepository->delete($id);

            if (!$deleted) {
                return response()->json([
                    'message' => 'Category not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Category deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete category',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get category tree structure
     */
    public function tree(): JsonResponse
    {
        try {
            $tree = $this->categoryRepository->getTree();

            return response()->json([
                'data' => $tree
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to fetch category tree',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk update categories
     */
    public function bulkUpdate(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'integer|exists:categories,id',
            'action' => 'required|string|in:activate,deactivate,delete',
        ]);

        try {
            $result = $this->categoryRepository->bulkUpdate($validated['ids'], $validated['action']);

            return response()->json([
                'message' => "Categories {$validated['action']}d successfully",
                'affected' => $result
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to perform bulk update',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
