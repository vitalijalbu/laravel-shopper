<?php

namespace LaravelShopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use LaravelShopper\Data\CategoryDto;
use LaravelShopper\CP\Navigation;
use LaravelShopper\CP\Page;
use LaravelShopper\Http\Controllers\Controller;
use LaravelShopper\Models\Category;

class CategoriesController extends Controller
{
    /**
     * Categories index
     */
    public function index(Request $request)
    {
        $page = Page::make('Categories')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Categories')
            ->primaryAction('Add category', '/cp/categories/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/categories/import'],
                ['label' => 'Export', 'url' => '/cp/categories/export'],
            ]);

        $categories = Category::with(['parent:id,name'])
            ->withCount(['children', 'products'])
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(50);

        return Inertia::render('CP/Categories/Index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'categories' => $categories,
        ]);
    }

    /**
     * Create category page
     */
    public function create()
    {
        $page = Page::make('Add category')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Categories', '/cp/categories')
            ->breadcrumb('Add category')
            ->primaryAction('Save category', null, ['form' => 'category-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('CP/Categories/Create', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'parentCategories' => Category::whereNull('parent_id')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store category using DTO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:categories',
            'description' => 'nullable|string|max:1000',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'integer|min:0',
            'is_enabled' => 'boolean',
            'seo' => 'array',
            'meta' => 'array',
        ]);

        // Create DTO from validated data
        $categoryDto = CategoryDto::from($validated);
        
        // Additional DTO validation
        $dtoErrors = $categoryDto->validate();
        if (!empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create category from DTO
        $category = Category::create($categoryDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');
        
        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Category created successfully',
                'redirect' => "/cp/categories/{$category->id}/edit"
            ]),
            'save_add_another' => response()->json([
                'message' => 'Category created successfully',
                'redirect' => '/cp/categories/create'
            ]),
            default => response()->json([
                'message' => 'Category created successfully',
                'redirect' => '/cp/categories'
            ])
        };
    }

    /**
     * Show category
     */
    public function show(Category $category)
    {
        $category->load(['parent', 'children', 'products' => function ($query) {
            $query->limit(10)->latest();
        }]);

        $page = Page::make($category->name)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Categories', '/cp/categories')
            ->breadcrumb($category->name)
            ->primaryAction('Edit category', "/cp/categories/{$category->id}/edit")
            ->secondaryActions([
                ['label' => 'View in store', 'url' => "/categories/{$category->slug}", 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('CP/Categories/Show', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'category' => $category,
        ]);
    }

    /**
     * Edit category
     */
    public function edit(Category $category)
    {
        $page = Page::make("Edit {$category->name}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Categories', '/cp/categories')
            ->breadcrumb($category->name, "/cp/categories/{$category->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update category', null, ['form' => 'category-form'])
            ->secondaryActions([
                ['label' => 'View category', 'url' => "/cp/categories/{$category->id}"],
                ['label' => 'View in store', 'url' => "/categories/{$category->slug}", 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CategoryGeneralForm'],
                'seo' => ['label' => 'SEO', 'component' => 'CategorySeoForm'],
                'products' => ['label' => 'Products', 'component' => 'CategoryProductsForm'],
            ]);

        return Inertia::render('CP/Categories/Edit', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'category' => $category,
            'parentCategories' => Category::whereNull('parent_id')
                ->where('id', '!=', $category->id)
                ->orderBy('name')
                ->get(),
        ]);
    }

    /**
     * Update category using DTO
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "nullable|string|max:255|unique:categories,slug,{$category->id}",
            'description' => 'nullable|string|max:1000',
            'parent_id' => "nullable|exists:categories,id|not_in:{$category->id}",
            'sort_order' => 'integer|min:0',
            'is_enabled' => 'boolean',
            'seo' => 'array',
            'meta' => 'array',
        ]);

        // Create DTO from validated data
        $categoryDto = CategoryDto::from($validated);
        
        // Additional DTO validation
        $dtoErrors = $categoryDto->validate();
        if (!empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update category from DTO
        $category->update($categoryDto->toArray());

        return response()->json([
            'message' => 'Category updated successfully',
            'category' => $category->fresh(['parent']),
        ]);
    }

    /**
     * Delete category
     */
    public function destroy(Category $category)
    {
        // Check if category has children or products
        if ($category->children()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with subcategories'
            ], 422);
        }

        if ($category->products()->exists()) {
            return response()->json([
                'error' => 'Cannot delete category with products'
            ], 422);
        }

        $category->delete();
        
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
    }

    /**
     * Handle bulk actions
     */
    public function bulk(Request $request)
    {
        $action = $request->input('action');
        $ids = $request->input('ids', []);
        
        if (empty($ids)) {
            return response()->json(['error' => 'No categories selected'], 422);
        }

        $categories = Category::whereIn('id', $ids);
        
        return match ($action) {
            'enable' => $this->bulkEnable($categories),
            'disable' => $this->bulkDisable($categories),
            'delete' => $this->bulkDelete($categories),
            'export' => $this->bulkExport($categories),
            default => response()->json(['error' => 'Unknown action'], 422)
        };
    }

    /**
     * Bulk enable categories
     */
    protected function bulkEnable($categories)
    {
        $count = $categories->update(['is_enabled' => true]);
        return response()->json(['message' => "Enabled {$count} categories"]);
    }

    /**
     * Bulk disable categories
     */
    protected function bulkDisable($categories)
    {
        $count = $categories->update(['is_enabled' => false]);
        return response()->json(['message' => "Disabled {$count} categories"]);
    }

    /**
     * Bulk delete categories
     */
    protected function bulkDelete($categories)
    {
        $count = 0;
        $errors = [];

        $categories->get()->each(function ($category) use (&$count, &$errors) {
            if ($category->children()->exists() || $category->products()->exists()) {
                $errors[] = "Cannot delete '{$category->name}' - has children or products";
                return;
            }

            $category->delete();
            $count++;
        });

        if (!empty($errors)) {
            return response()->json([
                'message' => "Deleted {$count} categories",
                'errors' => $errors
            ], 207); // 207 Multi-Status
        }

        return response()->json(['message' => "Deleted {$count} categories"]);
    }

    /**
     * Bulk export categories
     */
    protected function bulkExport($categories)
    {
        $count = $categories->count();
        return response()->json([
            'message' => "Exporting {$count} categories",
            'download_url' => '/cp/categories/export/download/' . uniqid(),
        ]);
    }
}
