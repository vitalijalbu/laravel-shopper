<?php

namespace Shopper\DataTable;

use Illuminate\Database\Eloquent\Builder;
use Shopper\Models\Customer;

class CustomerDataTable extends BaseDataTable
{
    /**
     * Get the base query for customers.
     */
    protected function query(): Builder
    {
        return Customer::query()
            ->with(['groups'])
            ->withCount(['orders', 'wishlists'])
            ->select([
                'id', 'first_name', 'last_name', 'email', 'phone',
                'is_enabled', 'last_login_at', 'created_at', 'updated_at',
            ]);
    }

    /**
     * Setup filters for customers.
     */
    protected function setupFilters(): void
    {
        $this->addFilter(
            (new SelectFilter('is_enabled', 'Status'))
                ->options([
                    ['value' => 1, 'label' => 'Enabled'],
                    ['value' => 0, 'label' => 'Disabled'],
                ])
        );

        $this->addFilter(
            (new SelectFilter('has_orders', 'Order Status'))
                ->options([
                    ['value' => 'has_orders', 'label' => 'Has Orders'],
                    ['value' => 'no_orders', 'label' => 'No Orders'],
                ])
                ->callback(function (Builder $query, $value) {
                    if ($value === 'has_orders') {
                        $query->has('orders');
                    } elseif ($value === 'no_orders') {
                        $query->doesntHave('orders');
                    }
                })
        );

        $this->addFilter(
            new DateRangeFilter('created_at', 'Registration Date')
        );

        $this->addFilter(
            new DateRangeFilter('last_login_at', 'Last Login')
        );
    }

    /**
     * Setup columns for customers.
     */
    protected function setupColumns(): void
    {
        $this->addColumn('full_name', 'Customer', [
            'sortable' => false,
            'searchable' => true,
            'type' => 'custom',
            'accessor' => function ($customer) {
                return [
                    'name' => $customer->getFullNameAttribute(),
                    'email' => $customer->email,
                    'avatar' => $customer->avatar,
                ];
            },
        ]);

        $this->addColumn('email', 'Email', [
            'sortable' => true,
            'searchable' => true,
            'type' => 'email',
        ]);

        $this->addColumn('phone', 'Phone', [
            'sortable' => true,
            'searchable' => true,
            'type' => 'text',
        ]);

        $this->addColumn('orders_count', 'Orders', [
            'sortable' => true,
            'type' => 'number',
        ]);

        $this->addColumn('total_spent', 'Total Spent', [
            'sortable' => false,
            'type' => 'money',
            'accessor' => function ($customer) {
                return $customer->orders()->sum('total');
            },
        ]);

        $this->addColumn('is_enabled', 'Status', [
            'sortable' => true,
            'type' => 'badge',
            'variants' => [
                1 => 'success',
                0 => 'danger',
            ],
            'labels' => [
                1 => 'Enabled',
                0 => 'Disabled',
            ],
        ]);

        $this->addColumn('last_login_at', 'Last Login', [
            'sortable' => true,
            'type' => 'date',
        ]);

        $this->addColumn('created_at', 'Registered', [
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
                    'url' => '/pages/customers/{id}/edit',
                ],
                [
                    'label' => 'View',
                    'icon' => 'eye',
                    'url' => '/pages/customers/{id}',
                ],
                [
                    'label' => 'Create Order',
                    'icon' => 'plus',
                    'url' => '/pages/orders/create?customer_id={id}',
                ],
                [
                    'label' => 'Disable',
                    'icon' => 'ban',
                    'action' => 'disable',
                    'condition' => function ($customer) {
                        return $customer->is_enabled;
                    },
                ],
                [
                    'label' => 'Enable',
                    'icon' => 'check',
                    'action' => 'enable',
                    'condition' => function ($customer) {
                        return !$customer->is_enabled;
                    },
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
     * Get bulk actions for customers.
     */
    public function getBulkActions(): array
    {
        return [
            [
                'key' => 'enable',
                'label' => 'Enable',
                'icon' => 'check-circle',
                'destructive' => false,
            ],
            [
                'key' => 'disable',
                'label' => 'Disable',
                'icon' => 'ban',
                'destructive' => false,
            ],
            [
                'key' => 'delete',
                'label' => 'Delete',
                'icon' => 'trash',
                'destructive' => true,
                'confirmation' => [
                    'title' => 'Delete Customers',
                    'message' => 'Are you sure you want to delete these customers? This action cannot be undone.',
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
    protected array $searchableColumns = ['first_name', 'last_name', 'email', 'phone'];
}
