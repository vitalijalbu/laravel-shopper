<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Shopper\CP\Navigation;
use Shopper\CP\Page;
use Shopper\Data\BrandDto;
use Shopper\Http\Controllers\Controller;
use Shopper\Models\Brand;

class BrandController extends Controller
{
    /**
     * Brands index
     */
    public function index(Request $request)
    {
        $page = Page::make('Brands')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Brands')
            ->primaryAction('Add brand', '/cp/brands/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/brands/import'],
                ['label' => 'Export', 'url' => '/cp/brands/export'],
            ]);

        $brands = Brand::withCount('products')
            ->orderBy('name')
            ->paginate(50);

        return Inertia::render('brands/index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'brands' => $brands,
        ]);
    }

    /**
     * Create brand page
     */
    public function create()
    {
        $page = Page::make('Add brand')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Brands', '/cp/brands')
            ->breadcrumb('Add brand')
            ->primaryAction('Save brand', null, ['form' => 'brand-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('brands/Create', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
        ]);
    }

    /**
     * Store brand using DTO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:brands',
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'is_enabled' => 'boolean',
            'seo' => 'array',
            'meta' => 'array',
        ]);

        // Create DTO from validated data
        $brandDto = BrandDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $brandDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create brand from DTO
        $brand = Brand::create($brandDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Brand created successfully',
                'redirect' => "/cp/brands/{$brand->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => 'Brand created successfully',
                'redirect' => '/cp/brands/create',
            ]),
            default => response()->json([
                'message' => 'Brand created successfully',
                'redirect' => '/cp/brands',
            ])
        };
    }

    /**
     * Show brand
     */
    public function show(Brand $brand)
    {
        $brand->load(['products' => function ($query) {
            $query->limit(10)->latest();
        }]);

        $page = Page::make($brand->name)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Brands', '/cp/brands')
            ->breadcrumb($brand->name)
            ->primaryAction('Edit brand', "/cp/brands/{$brand->id}/edit")
            ->secondaryActions([
                ['label' => 'View in store', 'url' => "/brands/{$brand->slug}", 'target' => '_blank'],
                ['label' => 'Visit website', 'url' => $brand->website, 'target' => '_blank', 'disabled' => ! $brand->hasWebsite()],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('brands/Show', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'brand' => $brand,
        ]);
    }

    /**
     * Edit brand
     */
    public function edit(Brand $brand)
    {
        $page = Page::make("Edit {$brand->name}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Catalog', '/cp/catalog')
            ->breadcrumb('Brands', '/cp/brands')
            ->breadcrumb($brand->name, "/cp/brands/{$brand->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update brand', null, ['form' => 'brand-form'])
            ->secondaryActions([
                ['label' => 'View brand', 'url' => "/cp/brands/{$brand->id}"],
                ['label' => 'View in store', 'url' => "/brands/{$brand->slug}", 'target' => '_blank'],
                ['label' => 'Visit website', 'url' => $brand->website, 'target' => '_blank', 'disabled' => ! $brand->hasWebsite()],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'BrandGeneralForm'],
                'seo' => ['label' => 'SEO', 'component' => 'BrandSeoForm'],
                'products' => ['label' => 'Products', 'component' => 'BrandProductsForm'],
            ]);

        return Inertia::render('brands/Edit', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'brand' => $brand,
        ]);
    }

    /**
     * Update brand using DTO
     */
    public function update(Request $request, Brand $brand)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => "nullable|string|max:255|unique:brands,slug,{$brand->id}",
            'description' => 'nullable|string|max:1000',
            'website' => 'nullable|url|max:255',
            'is_enabled' => 'boolean',
            'seo' => 'array',
            'meta' => 'array',
        ]);

        // Create DTO from validated data
        $brandDto = BrandDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $brandDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update brand from DTO
        $brand->update($brandDto->toArray());

        return response()->json([
            'message' => 'Brand updated successfully',
            'brand' => $brand->fresh(),
        ]);
    }

    /**
     * Delete brand
     */
    public function destroy(Brand $brand)
    {
        // Check if brand has products
        if ($brand->products()->exists()) {
            return response()->json([
                'error' => 'Cannot delete brand with products',
            ], 422);
        }

        $brand->delete();

        return response()->json([
            'message' => 'Brand deleted successfully',
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
            return response()->json(['error' => 'No brands selected'], 422);
        }

        $brands = Brand::whereIn('id', $ids);

        return match ($action) {
            'enable' => $this->bulkEnable($brands),
            'disable' => $this->bulkDisable($brands),
            'delete' => $this->bulkDelete($brands),
            'export' => $this->bulkExport($brands),
            default => response()->json(['error' => 'Unknown action'], 422)
        };
    }

    /**
     * Bulk enable brands
     */
    protected function bulkEnable($brands)
    {
        $count = $brands->update(['is_enabled' => true]);

        return response()->json(['message' => "Enabled {$count} brands"]);
    }

    /**
     * Bulk disable brands
     */
    protected function bulkDisable($brands)
    {
        $count = $brands->update(['is_enabled' => false]);

        return response()->json(['message' => "Disabled {$count} brands"]);
    }

    /**
     * Bulk delete brands
     */
    protected function bulkDelete($brands)
    {
        $count = 0;
        $errors = [];

        $brands->get()->each(function ($brand) use (&$count, &$errors) {
            if ($brand->products()->exists()) {
                $errors[] = "Cannot delete '{$brand->name}' - has products";

                return;
            }

            $brand->delete();
            $count++;
        });

        if (! empty($errors)) {
            return response()->json([
                'message' => "Deleted {$count} brands",
                'errors' => $errors,
            ], 207); // 207 Multi-Status
        }

        return response()->json(['message' => "Deleted {$count} brands"]);
    }

    /**
     * Bulk export brands
     */
    protected function bulkExport($brands)
    {
        $count = $brands->count();

        return response()->json([
            'message' => "Exporting {$count} brands",
            'download_url' => '/cp/brands/export/download/'.uniqid(),
        ]);
    }
}
