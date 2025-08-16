<?php

namespace LaravelShopper\DataTable;

use Illuminate\Database\Eloquent\Builder;
use LaravelShopper\Models\Product;

class ProductDataTable extends BaseDataTable
{
    /**
     * Get the base query for products.
     */
    protected function query(): Builder
    {
        return Product::query()
            ->with(['category', 'brand', 'media'])
            ->select([
                'id', 'name', 'handle', 'status', 'visibility',
                'price', 'inventory_quantity', 'sku',
                'category_id', 'brand_id', 'created_at', 'updated_at',
            ]);
    }

    /**
     * Setup filters for products.
     */
    protected function setupFilters(): void
    {
        $this->addFilter(
            (new SelectFilter('status', 'Status'))
                ->options([
                    ['value' => 'active', 'label' => 'Active'],
                    ['value' => 'draft', 'label' => 'Draft'],
                    ['value' => 'archived', 'label' => 'Archived'],
                ])
        );

        $this->addFilter(
            (new SelectFilter('visibility', 'Visibility'))
                ->options([
                    ['value' => 'public', 'label' => 'Public'],
                    ['value' => 'hidden', 'label' => 'Hidden'],
                    ['value' => 'private', 'label' => 'Private'],
                ])
        );

        $this->addFilter(
            (new SelectFilter('category_id', 'Category'))
                ->optionsUrl('/cp/api/categories/options')
        );

        $this->addFilter(
            (new SelectFilter('brand_id', 'Brand'))
                ->optionsUrl('/cp/api/brands/options')
        );

        $this->addFilter(
            (new NumberRangeFilter('price', 'Price Range'))
                ->min(0)
                ->max(100000)
        );

        $this->addFilter(
            new CustomFilter('inventory_status', 'Inventory', function (Builder $query, $value) {
                match ($value) {
                    'in_stock' => $query->where('inventory_quantity', '>', 0),
                    'low_stock' => $query->whereBetween('inventory_quantity', [1, 10]),
                    'out_of_stock' => $query->where('inventory_quantity', '<=', 0),
                    default => null
                };
            })
        );

        // Tagging filter (if using spatie/laravel-tags)
        $this->addFilter(
            new CustomFilter('tags', 'Tags', function (Builder $query, $tags) {
                if (is_array($tags)) {
                    $query->withAnyTags($tags);
                }
            })
        );

        // Created date range
        $this->addFilter(
            new DateRangeFilter('created_at', 'Created Date')
        );
    }

    /**
     * Setup columns for products.
     */
    protected function setupColumns(): void
    {
        $this->addColumn('image', 'Image', [
            'sortable' => false,
            'type' => 'image',
        ]);

        $this->addColumn('name', 'Product', [
            'sortable' => true,
            'searchable' => true,
            'type' => 'text',
        ]);

        $this->addColumn('status', 'Status', [
            'sortable' => true,
            'type' => 'badge',
            'variants' => [
                'active' => 'success',
                'draft' => 'warning',
                'archived' => 'danger',
            ],
        ]);

        $this->addColumn('inventory_quantity', 'Inventory', [
            'sortable' => true,
            'type' => 'number',
        ]);

        $this->addColumn('price', 'Price', [
            'sortable' => true,
            'type' => 'money',
        ]);

        $this->addColumn('category.name', 'Category', [
            'sortable' => false,
            'type' => 'text',
        ]);

        $this->addColumn('brand.name', 'Brand', [
            'sortable' => false,
            'type' => 'text',
        ]);

        $this->addColumn('created_at', 'Created', [
            'sortable' => true,
            'type' => 'date',
        ]);

        $this->addColumn('actions', 'Actions', [
            'sortable' => false,
            'type' => 'actions',
            'actions' => [
                [
                    'label' => 'Edit',
                    'icon' => 'edit',
                    'url' => '/cp/products/{id}/edit',
                ],
                [
                    'label' => 'View',
                    'icon' => 'eye',
                    'url' => '/cp/products/{id}',
                ],
                [
                    'label' => 'Duplicate',
                    'icon' => 'copy',
                    'action' => 'duplicate',
                ],
                [
                    'label' => 'Delete',
                    'icon' => 'trash',
                    'action' => 'delete',
                    'destructive' => true,
                ],
            ],
        ]);
    }

    /**
     * Get bulk actions for products.
     */
    public function getBulkActions(): array
    {
        return [
            [
                'key' => 'activate',
                'label' => 'Set as Active',
                'icon' => 'check-circle',
                'destructive' => false,
            ],
            [
                'key' => 'draft',
                'label' => 'Set as Draft',
                'icon' => 'edit',
                'destructive' => false,
            ],
            [
                'key' => 'archive',
                'label' => 'Archive',
                'icon' => 'archive',
                'destructive' => false,
            ],
            [
                'key' => 'delete',
                'label' => 'Delete',
                'icon' => 'trash',
                'destructive' => true,
                'confirmation' => [
                    'title' => 'Delete Products',
                    'message' => 'Are you sure you want to delete these products? This action cannot be undone.',
                    'confirm_button' => 'Delete',
                    'cancel_button' => 'Cancel',
                ],
            ],
            [
                'key' => 'export',
                'label' => 'Export',
                'icon' => 'download',
                'destructive' => false,
            ],
        ];
    }

    /**
     * Override default settings.
     */
    protected string $defaultSort = 'created_at';

    protected string $defaultDirection = 'desc';

    protected int $perPage = 25;

    protected array $searchableColumns = ['name', 'handle', 'sku', 'description'];
}
