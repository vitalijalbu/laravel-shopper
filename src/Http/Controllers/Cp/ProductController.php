<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Shopper\CP\Navigation;
use Shopper\CP\Page;
use Shopper\Http\Requests\CP\StoreProductRequest;
use Shopper\Http\Resources\CP\ProductResource;
use Shopper\Models\Brand;
use Shopper\Models\Collection;
use Shopper\Models\Product;
use Shopper\Repositories\ProductRepository;

class ProductController extends BaseController
{
    public function __construct(
        protected ProductRepository $productRepository
    ) {
        $this->middleware('can:browse_products')->only(['index', 'show']);
        $this->middleware('can:create_products')->only(['create', 'store']);
        $this->middleware('can:update_products')->only(['edit', 'update']);
        $this->middleware('can:delete_products')->only(['destroy']);
    }

    /**
     * Display products listing.
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Products');

        $filters = $this->getFilters([
            'search',
            'status',
            'brand_id',
            'collection_id',
            'type',
            'price_min',
            'price_max',
            'stock_status',
            'created_at',
        ]);

        $products = $this->productRepository->searchPaginated(
            $filters,
            request('per_page', 15)
        );

        $page = Page::make('Products')
            ->primaryAction('Add product', route('shopper.products.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => route('shopper.products.import')],
                ['label' => 'Export', 'url' => route('shopper.products.export')],
                ['label' => 'Collections', 'url' => route('shopper.collections.index')],
                ['label' => 'Brands', 'url' => route('shopper.brands.index')],
            ]);

        return $this->inertiaResponse('products/Index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'products' => $products->through(fn ($product) => new ProductResource($product)),
            'filters' => $filters,
            'brands' => Brand::select('id', 'name')->get(),
            'collections' => Collection::select('id', 'name')->get(),
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Products', 'shopper.products.index')
            ->addBreadcrumb('Add product');

        $page = Page::make('Add product')
            ->primaryAction('Save product', null, ['form' => 'product-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
                ['label' => 'Save as draft', 'action' => 'save_draft'],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'ProductGeneralForm'],
                'media' => ['label' => 'Media', 'component' => 'ProductMediaForm'],
                'pricing' => ['label' => 'Pricing', 'component' => 'ProductPricingForm'],
                'inventory' => ['label' => 'Inventory', 'component' => 'ProductInventoryForm'],
                'shipping' => ['label' => 'Shipping', 'component' => 'ProductShippingForm'],
                'seo' => ['label' => 'SEO', 'component' => 'ProductSeoForm'],
                'variants' => ['label' => 'Variants', 'component' => 'ProductVariantsForm'],
            ]);

        return $this->inertiaResponse('products/Create', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'brands' => Brand::select('id', 'name')->where('status', 'active')->orderBy('name')->get(),
            'collections' => Collection::select('id', 'name')->where('status', 'active')->orderBy('name')->get(),
        ]);
    }

    /**
     * Store new product.
     */
    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productRepository->create($request->validated());

        // Sync collections
        if ($request->has('collection_ids')) {
            $product->collections()->sync($request->input('collection_ids'));
        }

        // Handle media uploads
        if ($request->has('media')) {
            $this->handleMediaUploads($product, $request->input('media'));
        }

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('shopper.products.edit', $product),
            'save_add_another' => route('shopper.products.create'),
            'save_draft' => route('shopper.products.edit', $product),
            default => route('shopper.products.index'),
        };

        return $this->successResponse('Product created successfully', [
            'product' => new ProductResource($product),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display product details.
     */
    public function show(Product $product): Response
    {
        $product = $this->productRepository->findWithRelations($product->id, [
            'brand', 'collections', 'variants.media', 'media', 'orders',
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Products', 'shopper.products.index')
            ->addBreadcrumb($product->name);

        $page = Page::make($product->name)
            ->primaryAction('Edit product', route('shopper.products.edit', $product))
            ->secondaryActions([
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'View in storefront', 'url' => $product->url, 'external' => true],
                ['label' => 'Create variant', 'url' => route('shopper.product-variants.create', $product)],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'overview' => ['label' => 'Overview', 'component' => 'ProductOverview'],
                'variants' => ['label' => 'Variants', 'component' => 'ProductVariants'],
                'orders' => ['label' => 'Orders', 'component' => 'ProductOrders'],
                'analytics' => ['label' => 'Analytics', 'component' => 'ProductAnalytics'],
            ]);

        return $this->inertiaResponse('products/Show', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'product' => new ProductResource($product),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Product $product): Response
    {
        $product = $this->productRepository->findWithRelations($product->id, [
            'brand', 'collections', 'variants', 'media',
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Products', 'shopper.products.index')
            ->addBreadcrumb($product->name, route('shopper.products.show', $product))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$product->name}")
            ->primaryAction('Update product', null, ['form' => 'product-form'])
            ->secondaryActions([
                ['label' => 'View product', 'url' => route('shopper.products.show', $product)],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'View in storefront', 'url' => $product->url, 'external' => true],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'ProductGeneralForm'],
                'media' => ['label' => 'Media', 'component' => 'ProductMediaForm'],
                'pricing' => ['label' => 'Pricing', 'component' => 'ProductPricingForm'],
                'inventory' => ['label' => 'Inventory', 'component' => 'ProductInventoryForm'],
                'shipping' => ['label' => 'Shipping', 'component' => 'ProductShippingForm'],
                'seo' => ['label' => 'SEO', 'component' => 'ProductSeoForm'],
                'variants' => ['label' => 'Variants', 'component' => 'ProductVariantsForm'],
            ]);

        return $this->inertiaResponse('products/Edit', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'product' => new ProductResource($product),
            'brands' => Brand::select('id', 'name')->orderBy('name')->get(),
            'collections' => Collection::select('id', 'name')->orderBy('name')->get(),
        ]);
    }

    /**
     * Update product.
     */
    public function update(StoreProductRequest $request, Product $product): JsonResponse
    {
        $product = $this->productRepository->update($product->id, $request->validated());

        // Sync collections
        if ($request->has('collection_ids')) {
            $product->collections()->sync($request->input('collection_ids'));
        }

        // Handle media uploads
        if ($request->has('media')) {
            $this->handleMediaUploads($product, $request->input('media'));
        }

        return $this->successResponse('Product updated successfully', [
            'product' => new ProductResource($this->productRepository->findWithRelations($product->id, ['brand', 'collections', 'media'])),
        ]);
    }

    /**
     * Delete product.
     */
    public function destroy(Product $product): JsonResponse
    {
        if (! $this->productRepository->canDelete($product->id)) {
            return $this->errorResponse('Cannot delete product with existing orders or variants');
        }

        $this->productRepository->delete($product->id);

        return $this->successResponse('Product deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:publish,unpublish,delete,export,duplicate',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:products,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            return match ($action) {
                'publish' => $this->productRepository->bulkUpdate($ids, ['status' => 'published']),
                'unpublish' => $this->productRepository->bulkUpdate($ids, ['status' => 'draft']),
                'delete' => $this->productRepository->bulkDelete($ids),
                'export' => $this->productRepository->bulkExport($ids),
                'duplicate' => $this->handleBulkDuplicate($ids),
            };
        });
    }

    /**
     * Duplicate product.
     */
    public function duplicate(Product $product): JsonResponse
    {
        $originalProduct = $this->productRepository->findWithRelations($product->id, ['collections']);

        $duplicateData = $originalProduct->toArray();
        unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);

        $duplicateData['name'] = $originalProduct->name.' (Copy)';
        $duplicateData['slug'] = $originalProduct->slug.'-copy';
        $duplicateData['status'] = 'draft';

        $duplicate = $this->productRepository->create($duplicateData);

        // Copy collections
        $duplicate->collections()->sync($originalProduct->collections->pluck('id'));

        return $this->successResponse('Product duplicated successfully', [
            'product' => new ProductResource($duplicate),
            'redirect' => route('shopper.products.edit', $duplicate),
        ]);
    }

    /**
     * Apply search filter for products.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%");
        });
    }

    /**
     * Apply custom filters.
     */
    protected function applyCustomFilter($query, string $key, $value): void
    {
        match ($key) {
            'brand_id' => $query->where('brand_id', $value),
            'collection_id' => $query->whereHas('collections', fn ($q) => $q->where('collections.id', $value)),
            'type' => $query->where('type', $value),
            'price_min' => $query->where('price', '>=', $value),
            'price_max' => $query->where('price', '<=', $value),
            'stock_status' => $this->applyStockStatusFilter($query, $value),
            default => parent::applyCustomFilter($query, $key, $value),
        };
    }

    /**
     * Apply stock status filter.
     */
    private function applyStockStatusFilter($query, string $status): void
    {
        match ($status) {
            'in_stock' => $query->where('stock_quantity', '>', 0),
            'low_stock' => $query->where('stock_quantity', '>', 0)->where('stock_quantity', '<=', 10),
            'out_of_stock' => $query->where('stock_quantity', '<=', 0),
        };
    }

    /**
     * Handle media uploads.
     */
    private function handleMediaUploads(Product $product, array $media): void
    {
        foreach ($media as $mediaData) {
            // Handle media upload logic here
            // This would typically involve file upload and media library integration
        }
    }

    /**
     * Handle bulk duplicate.
     */
    private function handleBulkDuplicate(array $ids): int
    {
        $count = 0;

        foreach ($ids as $id) {
            $product = $this->productRepository->findWithRelations($id, ['collections']);

            if ($product) {
                $duplicateData = $product->toArray();
                unset($duplicateData['id'], $duplicateData['created_at'], $duplicateData['updated_at']);

                $duplicateData['name'] = $product->name.' (Copy)';
                $duplicateData['slug'] = $product->slug.'-copy-'.time().'-'.$count;
                $duplicateData['status'] = 'draft';

                $duplicate = $this->productRepository->create($duplicateData);
                $duplicate->collections()->sync($product->collections->pluck('id'));
                $count++;
            }
        }

        return $count;
    }
}
