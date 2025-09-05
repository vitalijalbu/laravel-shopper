<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Shopper\CP\Page;
use Shopper\Data\ProductDto;
use Shopper\DataTable\ProductDataTable;
use Shopper\Http\Controllers\Controller;
use Shopper\Http\Resources\ProductCollection;
use Shopper\Http\Resources\ProductResource;
use Shopper\Models\Brand;
use Shopper\Models\Category;
use Shopper\Models\Product;
use Shopper\Schema\SchemaRepository;

class ProductsController extends Controller
{
    protected SchemaRepository $schemas;

    public function __construct(SchemaRepository $schemas)
    {
        $this->schemas = $schemas;
    }

    /**
     * Products index page with DataTable
     */
    public function index(Request $request)
    {
        $dataTable = new ProductDataTable($request);

        // If AJAX request, return data for DataTable
        if ($request->expectsJson()) {
            $products = $dataTable->process();

            return new ProductCollection($products);
        }

        $page = Page::make('Products')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Products')
            ->primaryAction('Add product', '/cp/products/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/products/import'],
                ['label' => 'Export', 'url' => '/cp/products/export'],
            ]);

        return Inertia::render('products/index', [
            'page' => $page->compile(),

            'dataTable' => $dataTable->getConfig(),
            'bulkActions' => $dataTable->getBulkActions(),
        ]);
    }

    /**
     * Create product page
     */
    public function create()
    {
        // Load schema from JSON file
        $schema = $this->schemas->getCollection('products');

        if (! $schema) {
            abort(404, 'Product schema not found');
        }

        $page = Page::make('Add product')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Products', '/cp/products')
            ->breadcrumb('Add product')
            ->primaryAction('Save product', null, ['form' => 'product-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        // Product form tabs
        $page->tabs([
            'general' => ['label' => 'General', 'component' => 'ProductGeneralForm'],
            'inventory' => ['label' => 'Inventory', 'component' => 'ProductInventoryForm'],
            'shipping' => ['label' => 'Shipping', 'component' => 'ProductShippingForm'],
            'seo' => ['label' => 'SEO', 'component' => 'ProductSeoForm'],
            'variants' => ['label' => 'Variants', 'component' => 'ProductVariantsForm'],
        ]);

        return Inertia::render('products/Create', [
            'page' => $page->compile(),

            'schema' => $schema->toArray(),
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }

    /**
     * Store product using DTO
     */
    public function store(Request $request)
    {
        // Load schema and build validation rules
        $schema = $this->schemas->getCollection('products');
        $validationRules = $this->buildValidationRules($schema);

        $validated = $request->validate($validationRules);

        // Create DTO from validated data
        $productDto = ProductDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $productDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create product from DTO
        $product = Product::create($productDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Product created successfully',
                'redirect' => "/cp/products/{$product->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => 'Product created successfully',
                'redirect' => '/cp/products/create',
            ]),
            default => response()->json([
                'message' => 'Product created successfully',
                'redirect' => '/cp/products',
            ])
        };
    }

    /**
     * Show single product
     */
    public function show(Product $product)
    {
        $product->load(['category', 'brand', 'variants', 'media']);

        if (request()->expectsJson()) {
            return new ProductResource($product);
        }

        $page = Page::make($product->name)
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Products', '/cp/products')
            ->breadcrumb($product->name)
            ->primaryAction('Edit product', "/cp/products/{$product->id}/edit")
            ->secondaryActions([
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'View in store', 'url' => "/products/{$product->handle}", 'target' => '_blank'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('products/Show', [
            'page' => $page->compile(),

            'product' => new ProductResource($product),
        ]);
    }

    /**
     * Edit product page
     */
    public function edit(Product $product)
    {
        // Load schema from JSON file
        $schema = $this->schemas->getCollection('products');

        if (! $schema) {
            abort(404, 'Product schema not found');
        }

        $product->load(['category', 'brand', 'variants', 'media']);

        $page = Page::make("Edit {$product->name}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Products', '/cp/products')
            ->breadcrumb($product->name, "/cp/products/{$product->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update product', null, ['form' => 'product-form'])
            ->secondaryActions([
                ['label' => 'View product', 'url' => "/cp/products/{$product->id}"],
                ['label' => 'View in store', 'url' => "/products/{$product->handle}", 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        // Product form tabs
        $page->tabs([
            'general' => ['label' => 'General', 'component' => 'ProductGeneralForm'],
            'inventory' => ['label' => 'Inventory', 'component' => 'ProductInventoryForm'],
            'shipping' => ['label' => 'Shipping', 'component' => 'ProductShippingForm'],
            'seo' => ['label' => 'SEO', 'component' => 'ProductSeoForm'],
            'variants' => ['label' => 'Variants', 'component' => 'ProductVariantsForm'],
        ]);

        return Inertia::render('products/Edit', [
            'page' => $page->compile(),

            'schema' => $schema->toArray(),
            'product' => new ProductResource($product),
            'categories' => Category::select('id', 'name')->get(),
            'brands' => Brand::select('id', 'name')->get(),
        ]);
    }

    /**
     * Update product using DTO
     */
    public function update(Request $request, Product $product)
    {
        // Load schema and build validation rules
        $schema = $this->schemas->getCollection('products');
        $validationRules = $this->buildValidationRules($schema);

        // Make handle unique validation exclude current product
        if (isset($validationRules['handle'])) {
            $validationRules['handle'][] = 'unique:products,handle,'.$product->id;
        }

        $validated = $request->validate($validationRules);

        // Create DTO from validated data
        $productDto = ProductDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $productDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update product from DTO
        $product->update($productDto->toArray());

        return response()->json([
            'message' => 'Product updated successfully',
            'product' => new ProductResource($product->fresh(['category', 'brand'])),
        ]);
    }

    /**
     * Delete product
     */
    public function destroy(Product $product)
    {
        $product->delete();

        return response()->json([
            'message' => 'Product deleted successfully',
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
            return response()->json(['error' => 'No items selected'], 422);
        }

        $products = Product::whereIn('id', $ids);

        return match ($action) {
            'activate' => $this->bulkActivate($products),
            'draft' => $this->bulkDraft($products),
            'archive' => $this->bulkArchive($products),
            'delete' => $this->bulkDelete($products),
            'export' => $this->bulkExport($products),
            default => response()->json(['error' => 'Unknown action'], 422)
        };
    }

    /**
     * Build validation rules from schema
     */
    protected function buildValidationRules($schema): array
    {
        if (! $schema || ! isset($schema['fields'])) {
            return [
                'name' => 'required|string|max:255',
                'handle' => 'required|string|max:255|unique:products',
                'price' => 'required|numeric|min:0',
                'status' => 'required|in:active,draft,archived',
            ];
        }

        $rules = [];

        foreach ($schema['fields'] as $field => $config) {
            if (! empty($config['validate'])) {
                $rules[$field] = is_array($config['validate'])
                    ? $config['validate']
                    : explode('|', $config['validate']);
            } elseif ($config['required'] ?? false) {
                $rules[$field] = ['required'];
            }
        }

        return $rules;
    }

    /**
     * Bulk activate products
     */
    protected function bulkActivate($products)
    {
        $count = $products->update(['status' => 'active']);

        return response()->json(['message' => "Activated {$count} products"]);
    }

    /**
     * Bulk set as draft
     */
    protected function bulkDraft($products)
    {
        $count = $products->update(['status' => 'draft']);

        return response()->json(['message' => "Set {$count} products as draft"]);
    }

    /**
     * Bulk archive products
     */
    protected function bulkArchive($products)
    {
        $count = $products->update(['status' => 'archived']);

        return response()->json(['message' => "Archived {$count} products"]);
    }

    /**
     * Bulk delete products
     */
    protected function bulkDelete($products)
    {
        $count = $products->count();
        $products->delete();

        return response()->json(['message' => "Deleted {$count} products"]);
    }

    /**
     * Bulk export products
     */
    protected function bulkExport($products)
    {
        // TODO: Implement export logic
        $count = $products->count();

        return response()->json([
            'message' => "Exporting {$count} products",
            'download_url' => '/cp/products/export/download/'.uniqid(),
        ]);
    }
}
