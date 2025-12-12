<?php

declare(strict_types=1);

namespace Cartino\Http\Controllers\CP;

use Cartino\CP\Page;
use Cartino\Http\Requests\CP\StoreCollectionRequest;
use Cartino\Http\Requests\CP\UpdateCollectionRequest;
use Cartino\Http\Resources\CP\CollectionResource;
use Cartino\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;

class CategoriesController extends BaseController
{
    public function __construct()
    {
        $this->middleware('can:browse_collections')->only(['index', 'show']);
        $this->middleware('can:create_collections')->only(['create', 'store']);
        $this->middleware('can:update_collections')->only(['edit', 'update']);
        $this->middleware('can:delete_collections')->only(['destroy']);
    }

    /**
     * Display collections listing.
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cartino.catalog')
            ->addBreadcrumb('Collections');

        $filters = $this->getFilters(['search', 'status', 'collection_type', 'created_at']);

        $collections = Category::query()
            ->withCount('products')
            ->when($filters, fn ($query) => $this->applyFilters($query, $filters))
            ->orderBy('title')
            ->paginate(request('per_page', 15));

        $page = Page::make('Collections')
            ->primaryAction('Add collection', route('cartino.collections.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => route('cartino.collections.import')],
                ['label' => 'Export', 'url' => route('cartino.collections.export')],
            ]);

        return $this->inertiaResponse('collections/Index', [
            'page' => $page->compile(),

            'collections' => $collections->through(fn ($collection) => new CollectionResource($collection)),
            'filters' => $filters,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cartino.catalog')
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb('Add collection');

        $page = Page::make('Add collection')
            ->primaryAction('Save collection', null, ['form' => 'collection-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return $this->inertiaResponse('collections/Create', [
            'page' => $page->compile(),

        ]);
    }

    /**
     * Store new collection.
     */
    public function store(StoreCollectionRequest $request): JsonResponse
    {
        $collection = Category::create($request->validated());

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('cartino.collections.edit', $collection),
            'save_add_another' => route('cartino.collections.create'),
            default => route('cartino.collections.index'),
        };

        return $this->successResponse('Category created successfully', [
            'collection' => new CollectionResource($collection),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display collection details.
     */
    public function show(Category $collection): Response
    {
        $collection->load(['products' => fn ($query) => $query->limit(10)->latest()]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cartino.catalog')
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb($collection->title);

        $page = Page::make($collection->title)
            ->primaryAction('Edit collection', route('cartino.collections.edit', $collection))
            ->secondaryActions([
                ['label' => 'View in store', 'url' => $collection->url, 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return $this->inertiaResponse('collections/Show', [
            'page' => $page->compile(),

            'collection' => new CollectionResource($collection),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Category $collection): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Catalog', 'cartino.catalog')
            ->addBreadcrumb('Collections', 'cartino.collections.index')
            ->addBreadcrumb($collection->title, route('cartino.collections.show', $collection))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$collection->title}")
            ->primaryAction('Update collection', null, ['form' => 'collection-form'])
            ->secondaryActions([
                ['label' => 'View collection', 'url' => route('cartino.collections.show', $collection)],
                ['label' => 'View in store', 'url' => $collection->url, 'target' => '_blank'],
                ['label' => 'Duplicate', 'action' => 'duplicate'],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CollectionGeneralForm'],
                'products' => ['label' => 'Products', 'component' => 'CollectionProductsForm'],
                'conditions' => ['label' => 'Conditions', 'component' => 'CollectionConditionsForm', 'show_if' => 'collection_type === "smart"'],
                'seo' => ['label' => 'SEO', 'component' => 'CollectionSeoForm'],
            ]);

        return $this->inertiaResponse('collections/Edit', [
            'page' => $page->compile(),

            'collection' => new CollectionResource($collection),
        ]);
    }

    /**
     * Update collection.
     */
    public function update(UpdateCollectionRequest $request, Category $collection): JsonResponse
    {
        $collection->update($request->validated());

        return $this->successResponse('Category updated successfully', [
            'collection' => new CollectionResource($collection->fresh()),
        ]);
    }

    /**
     * Delete collection.
     */
    public function destroy(Category $collection): JsonResponse
    {
        $collection->delete();

        return $this->successResponse('Category deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:publish,unpublish,delete,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:collections,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            $collections = Category::whereIn('id', $ids);

            return match ($action) {
                'publish' => $collections->update(['status' => 'published']),
                'unpublish' => $collections->update(['status' => 'draft']),
                'delete' => $collections->delete(),
                'export' => $this->handleBulkExport($collections),
            };
        });
    }

    /**
     * Add products to collection.
     */
    public function addProducts(Request $request, Category $collection): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        foreach ($request->product_ids as $productId) {
            $collection->products()->syncWithoutDetaching([$productId => [
                'position' => $collection->products()->count() + 1,
            ]]);
        }

        return $this->successResponse('Products added to collection successfully');
    }

    /**
     * Remove products from collection.
     */
    public function removeProducts(Request $request, Category $collection): JsonResponse
    {
        $request->validate([
            'product_ids' => 'required|array|min:1',
            'product_ids.*' => 'exists:products,id',
        ]);

        $collection->products()->detach($request->product_ids);

        return $this->successResponse('Products removed from collection successfully');
    }

    /**
     * Apply search filter for collections.
     */
    protected function applySearchFilter($query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('handle', 'like', "%{$search}%");
        });
    }

    /**
     * Apply custom filters.
     */
    protected function applyCustomFilter($query, string $key, $value): void
    {
        match ($key) {
            'collection_type' => $query->where('collection_type', $value),
            default => parent::applyCustomFilter($query, $key, $value),
        };
    }

    /**
     * Handle bulk export.
     */
    private function handleBulkExport($collections): int
    {
        $count = $collections->count();

        // TODO: Implement actual export logic
        return $count;
    }
}
