<?php

namespace LaravelShopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use LaravelShopper\CP\Navigation;
use LaravelShopper\CP\Page;
use LaravelShopper\Data\CustomerDto;
use LaravelShopper\Http\Controllers\Controller;
use LaravelShopper\Models\Customer;

class CustomersController extends Controller
{
    /**
     * Customers index
     */
    public function index(Request $request)
    {
        $page = Page::make('Customers')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Customers')
            ->primaryAction('Add customer', '/cp/customers/create')
            ->secondaryActions([
                ['label' => 'Import', 'url' => '/cp/customers/import'],
                ['label' => 'Export', 'url' => '/cp/customers/export'],
                ['label' => 'Customer groups', 'url' => '/cp/customer-groups'],
            ]);

        $customers = Customer::with(['groups'])
            ->withCount(['orders', 'wishlists'])
            ->latest()
            ->paginate(25);

        return Inertia::render('CP/Customers/Index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customers' => $customers,
        ]);
    }

    /**
     * Create customer page
     */
    public function create()
    {
        $page = Page::make('Add customer')
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Customers', '/cp/customers')
            ->breadcrumb('Add customer')
            ->primaryAction('Save customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return Inertia::render('CP/Customers/Create', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
        ]);
    }

    /**
     * Store customer using DTO
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email|unique:customers,email',
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'password' => 'nullable|string|min:8',
            'is_enabled' => 'boolean',
            'meta' => 'array',
        ]);

        // Create DTO from validated data
        $customerDto = CustomerDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $customerDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Create customer from DTO
        $customer = Customer::create($customerDto->toArray());

        // Handle different save actions
        $action = $request->input('_action', 'save');

        return match ($action) {
            'save_continue' => response()->json([
                'message' => 'Customer created successfully',
                'redirect' => "/cp/customers/{$customer->id}/edit",
            ]),
            'save_add_another' => response()->json([
                'message' => 'Customer created successfully',
                'redirect' => '/cp/customers/create',
            ]),
            default => response()->json([
                'message' => 'Customer created successfully',
                'redirect' => '/cp/customers',
            ])
        };
    }

    /**
     * Show customer
     */
    public function show(Customer $customer)
    {
        $customer->load([
            'groups',
            'addresses',
            'orders' => function ($query) {
                $query->latest()->limit(5);
            },
        ]);

        $page = Page::make($customer->getDisplayName())
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Customers', '/cp/customers')
            ->breadcrumb($customer->getDisplayName())
            ->primaryAction('Edit customer', "/cp/customers/{$customer->id}/edit")
            ->secondaryActions([
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => "/cp/orders/create?customer_id={$customer->id}"],
                ['label' => 'Disable', 'action' => 'disable', 'disabled' => ! $customer->is_enabled],
                ['label' => 'Enable', 'action' => 'enable', 'disabled' => $customer->is_enabled],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return Inertia::render('CP/Customers/Show', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customer' => $customer,
        ]);
    }

    /**
     * Edit customer
     */
    public function edit(Customer $customer)
    {
        $page = Page::make("Edit {$customer->getDisplayName()}")
            ->breadcrumb('Home', '/cp')
            ->breadcrumb('Customers', '/cp/customers')
            ->breadcrumb($customer->getDisplayName(), "/cp/customers/{$customer->id}")
            ->breadcrumb('Edit')
            ->primaryAction('Update customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'View customer', 'url' => "/cp/customers/{$customer->id}"],
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => "/cp/orders/create?customer_id={$customer->id}"],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CustomerGeneralForm'],
                'addresses' => ['label' => 'Addresses', 'component' => 'CustomerAddressesForm'],
                'orders' => ['label' => 'Orders', 'component' => 'CustomerOrdersForm'],
                'groups' => ['label' => 'Groups', 'component' => 'CustomerGroupsForm'],
            ]);

        return Inertia::render('CP/Customers/Edit', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customer' => $customer,
        ]);
    }

    /**
     * Update customer using DTO
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => "required|email|unique:customers,email,{$customer->id}",
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date',
            'gender' => 'nullable|in:male,female,other,prefer_not_to_say',
            'password' => 'nullable|string|min:8',
            'is_enabled' => 'boolean',
            'meta' => 'array',
        ]);

        // Create DTO from validated data (exclude password if empty)
        if (empty($validated['password'])) {
            unset($validated['password']);
        }

        $customerDto = CustomerDto::from($validated);

        // Additional DTO validation
        $dtoErrors = $customerDto->validate();
        if (! empty($dtoErrors)) {
            return response()->json(['errors' => $dtoErrors], 422);
        }

        // Update customer from DTO
        $customer->update($customerDto->toArray());

        return response()->json([
            'message' => 'Customer updated successfully',
            'customer' => $customer->fresh(['groups']),
        ]);
    }

    /**
     * Delete customer
     */
    public function destroy(Customer $customer)
    {
        // Check if customer has orders
        if ($customer->orders()->exists()) {
            return response()->json([
                'error' => 'Cannot delete customer with orders',
            ], 422);
        }

        $customer->delete();

        return response()->json([
            'message' => 'Customer deleted successfully',
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
            return response()->json(['error' => 'No customers selected'], 422);
        }

        $customers = Customer::whereIn('id', $ids);

        return match ($action) {
            'enable' => $this->bulkEnable($customers),
            'disable' => $this->bulkDisable($customers),
            'delete' => $this->bulkDelete($customers),
            'export' => $this->bulkExport($customers),
            'send_email' => $this->bulkSendEmail($customers),
            default => response()->json(['error' => 'Unknown action'], 422)
        };
    }

    /**
     * Bulk enable customers
     */
    protected function bulkEnable($customers)
    {
        $count = $customers->update(['is_enabled' => true]);

        return response()->json(['message' => "Enabled {$count} customers"]);
    }

    /**
     * Bulk disable customers
     */
    protected function bulkDisable($customers)
    {
        $count = $customers->update(['is_enabled' => false]);

        return response()->json(['message' => "Disabled {$count} customers"]);
    }

    /**
     * Bulk delete customers
     */
    protected function bulkDelete($customers)
    {
        $count = 0;
        $errors = [];

        $customers->get()->each(function ($customer) use (&$count, &$errors) {
            if ($customer->orders()->exists()) {
                $errors[] = "Cannot delete '{$customer->getDisplayName()}' - has orders";

                return;
            }

            $customer->delete();
            $count++;
        });

        if (! empty($errors)) {
            return response()->json([
                'message' => "Deleted {$count} customers",
                'errors' => $errors,
            ], 207); // 207 Multi-Status
        }

        return response()->json(['message' => "Deleted {$count} customers"]);
    }

    /**
     * Bulk export customers
     */
    protected function bulkExport($customers)
    {
        $count = $customers->count();

        return response()->json([
            'message' => "Exporting {$count} customers",
            'download_url' => '/cp/customers/export/download/'.uniqid(),
        ]);
    }

    /**
     * Bulk send email
     */
    protected function bulkSendEmail($customers)
    {
        $count = $customers->count();

        // This would queue email jobs in real implementation

        return response()->json([
            'message' => "Email will be sent to {$count} customers",
        ]);
    }
}
