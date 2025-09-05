<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Response;
use Shopper\CP\Navigation;
use Shopper\CP\Page;
use Shopper\Http\Requests\CP\StoreCustomerRequest;
use Shopper\Http\Resources\CP\CustomerResource;
use Shopper\Models\Customer;
use Shopper\Repositories\CustomerRepository;

class CustomerController extends BaseController
{
    public function __construct(
        protected CustomerRepository $customerRepository
    ) {
        $this->middleware('can:browse_customers')->only(['index', 'show']);
        $this->middleware('can:create_customers')->only(['create', 'store']);
        $this->middleware('can:update_customers')->only(['edit', 'update']);
        $this->middleware('can:delete_customers')->only(['destroy']);
    }

    /**
     * Display customers listing.
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers');

        $filters = $this->getFilters(['search', 'status', 'customer_group_id', 'created_at']);
        
        $customers = $this->customerRepository->getPaginatedWithFilters(
            $filters,
            request('per_page', 15)
        );

        $page = Page::make('Customers')
            ->primaryAction('Add customer', route('shopper.customers.create'))
            ->secondaryActions([
                ['label' => 'Import', 'url' => route('shopper.customers.import')],
                ['label' => 'Export', 'url' => route('shopper.customers.export')],
                ['label' => 'Customer groups', 'url' => route('shopper.customer-groups.index')],
            ]);

        return $this->inertiaResponse('customers/Index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customers' => $customers->through(fn ($customer) => new CustomerResource($customer)),
            'filters' => $filters,
        ]);
    }

    /**
     * Show create form.
     */
    public function create(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'shopper.customers.index')
            ->addBreadcrumb('Add customer');

        $page = Page::make('Add customer')
            ->primaryAction('Save customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'Save & continue editing', 'action' => 'save_continue'],
                ['label' => 'Save & add another', 'action' => 'save_add_another'],
            ]);

        return $this->inertiaResponse('customers/Create', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
        ]);
    }

    /**
     * Store new customer.
     */
    public function store(StoreCustomerRequest $request): JsonResponse
    {
        $customer = $this->customerRepository->create($request->validated());

        $action = $request->input('_action', 'save');

        $redirectUrl = match ($action) {
            'save_continue' => route('shopper.customers.edit', $customer),
            'save_add_another' => route('shopper.customers.create'),
            default => route('shopper.customers.index'),
        };

        return $this->successResponse('Customer created successfully', [
            'customer' => new CustomerResource($customer),
            'redirect' => $redirectUrl,
        ]);
    }

    /**
     * Display customer details.
     */
    public function show(Customer $customer): Response
    {
        $customer = $this->customerRepository->findWithRelations($customer->id, [
            'addresses', 'orders', 'customerGroup'
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'shopper.customers.index')
            ->addBreadcrumb($customer->full_name);

        $page = Page::make($customer->full_name)
            ->primaryAction('Edit customer', route('shopper.customers.edit', $customer))
            ->secondaryActions([
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => route('shopper.orders.create', ['customer' => $customer->id])],
                ['label' => 'View orders', 'url' => route('shopper.orders.index', ['customer' => $customer->id])],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ]);

        return $this->inertiaResponse('customers/Show', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Show edit form.
     */
    public function edit(Customer $customer): Response
    {
        $customer = $this->customerRepository->findWithRelations($customer->id, [
            'addresses', 'customerGroup'
        ]);

        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Customers', 'shopper.customers.index')
            ->addBreadcrumb($customer->full_name, route('shopper.customers.show', $customer))
            ->addBreadcrumb('Edit');

        $page = Page::make("Edit {$customer->full_name}")
            ->primaryAction('Update customer', null, ['form' => 'customer-form'])
            ->secondaryActions([
                ['label' => 'View customer', 'url' => route('shopper.customers.show', $customer)],
                ['label' => 'Send email', 'action' => 'send_email'],
                ['label' => 'Create order', 'url' => route('shopper.orders.create', ['customer' => $customer->id])],
                ['label' => 'Delete', 'action' => 'delete', 'destructive' => true],
            ])
            ->tabs([
                'general' => ['label' => 'General', 'component' => 'CustomerGeneralForm'],
                'addresses' => ['label' => 'Addresses', 'component' => 'CustomerAddressesForm'],
                'orders' => ['label' => 'Orders', 'component' => 'CustomerOrdersForm'],
                'notes' => ['label' => 'Notes', 'component' => 'CustomerNotesForm'],
            ]);

        return $this->inertiaResponse('customers/Edit', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Update customer.
     */
    public function update(StoreCustomerRequest $request, Customer $customer): JsonResponse
    {
        $customer = $this->customerRepository->update($customer->id, $request->validated());

        return $this->successResponse('Customer updated successfully', [
            'customer' => new CustomerResource($customer),
        ]);
    }

    /**
     * Delete customer.
     */
    public function destroy(Customer $customer): JsonResponse
    {
        if (!$this->customerRepository->canDelete($customer->id)) {
            return $this->errorResponse('Cannot delete customer with orders');
        }

        $this->customerRepository->delete($customer->id);

        return $this->successResponse('Customer deleted successfully');
    }

    /**
     * Handle bulk operations.
     */
    public function bulk(Request $request): JsonResponse
    {
        $request->validate([
            'action' => 'required|string|in:activate,deactivate,delete,export',
            'ids' => 'required|array|min:1',
            'ids.*' => 'exists:customers,id',
        ]);

        $action = $request->input('action');
        $ids = $request->input('ids');

        return $this->handleBulkOperation($action, $ids, function ($action, $ids) {
            return match ($action) {
                'activate' => $this->customerRepository->bulkUpdate($ids, ['status' => 'active']),
                'deactivate' => $this->customerRepository->bulkUpdate($ids, ['status' => 'inactive']),
                'delete' => $this->customerRepository->bulkDelete($ids),
                'export' => $this->customerRepository->bulkExport($ids),
            };
        });
    }
}
