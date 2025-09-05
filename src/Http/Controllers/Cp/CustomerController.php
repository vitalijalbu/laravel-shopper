<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Http\Controllers\Controller;
use Shopper\Models\Customer;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): Response
    {
        $query = Customer::query();

        // Search functionality
        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Status filter
        if ($status = $request->get('status')) {
            if ($status === 'active') {
                $query->where('status', 'active');
            } elseif ($status === 'inactive') {
                $query->where('status', 'inactive');
            } elseif ($status === 'verified') {
                $query->whereNotNull('email_verified_at');
            } elseif ($status === 'unverified') {
                $query->whereNull('email_verified_at');
            }
        }

        // Date filter
        if ($dateFrom = $request->get('date_from')) {
            $query->whereDate('created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->get('date_to')) {
            $query->whereDate('created_at', '<=', $dateTo);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');
        $query->orderBy($sortBy, $sortDirection);

        // Pagination
        $customers = $query->with(['addresses', 'fidelityCard'])
            ->paginate($request->get('per_page', 15))
            ->withQueryString();

        return Inertia::render('customers/index', [
            'customers' => $customers,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to', 'sort_by', 'sort_direction']),
            'stats' => [
                'total' => Customer::count(),
                'verified' => Customer::whereNotNull('email_verified_at')->count(),
                'new_this_month' => Customer::whereMonth('created_at', now()->month)->count(),
            ],
            // Layout props
            'user' => Auth::user(),
            'navigation' => $this->getNavigationItems(),
            'sites' => $this->getSites(),
            'breadcrumbs' => [
                ['title' => 'Customers', 'url' => null],
            ],
        ]);
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create(): Response
    {
        return Inertia::render('customers/create', [
            'customer' => new Customer,
            // Layout props
            'user' => Auth::user(),
            'navigation' => $this->getNavigationItems(),
            'sites' => $this->getSites(),
            'breadcrumbs' => [
                ['title' => 'Customers', 'url' => route('cp.customers.index')],
                ['title' => 'Create Customer', 'url' => null],
            ],
        ]);
    }

    /**
     * Store a newly created customer.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['string', 'in:active,inactive'],
            'meta' => ['nullable', 'array'],
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        }

        $customer = Customer::create($validated);

        return redirect()
            ->route('cp.customers.show', $customer)
            ->with('success', 'Customer created successfully.');
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer): Response
    {
        $customer->load([
            'addresses',
            'orders' => function ($query) {
                $query->latest()->limit(10);
            },
            'fidelityCard.transactions' => function ($query) {
                $query->latest()->limit(5);
            },
        ]);

        return Inertia::render('customers/show', [
            'customer' => $customer,
            'stats' => [
                'total_orders' => $customer->orders()->count(),
                'total_spent' => $customer->orders()->sum('total'),
                'fidelity_points' => $customer->fidelityCard?->points ?? 0,
                'average_order' => $customer->orders()->avg('total') ?? 0,
            ],
            // Layout props
            'user' => Auth::user(),
            'navigation' => $this->getNavigationItems(),
            'sites' => $this->getSites(),
            'breadcrumbs' => [
                ['title' => 'Customers', 'url' => route('cp.customers.index')],
                ['title' => $customer->full_name, 'url' => null],
            ],
        ]);
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer): Response
    {
        return Inertia::render('customers/edit', [
            'customer' => $customer,
            // Layout props
            'user' => Auth::user(),
            'navigation' => $this->getNavigationItems(),
            'sites' => $this->getSites(),
            'breadcrumbs' => [
                ['title' => 'Customers', 'url' => route('cp.customers.index')],
                ['title' => $customer->full_name, 'url' => route('cp.customers.show', $customer)],
                ['title' => 'Edit', 'url' => null],
            ],
        ]);
    }

    /**
     * Update the specified customer.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('customers', 'email')->ignore($customer->id)],
            'phone' => ['nullable', 'string', 'max:20'],
            'date_of_birth' => ['nullable', 'date'],
            'gender' => ['nullable', 'in:male,female,other'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'status' => ['string', 'in:active,inactive'],
            'meta' => ['nullable', 'array'],
        ]);

        if (isset($validated['password']) && $validated['password']) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $customer->update($validated);

        return redirect()
            ->route('cp.customers.show', $customer)
            ->with('success', 'Customer updated successfully.');
    }

    /**
     * Remove the specified customer.
     */
    public function destroy(Customer $customer)
    {
        $customer->delete();

        return redirect()
            ->route('cp.customers.index')
            ->with('success', 'Customer deleted successfully.');
    }

    /**
     * Restore a soft-deleted customer.
     */
    public function restore($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->restore();

        return redirect()
            ->route('cp.customers.show', $customer)
            ->with('success', 'Customer restored successfully.');
    }

    /**
     * Permanently delete a customer.
     */
    public function forceDelete($id)
    {
        $customer = Customer::withTrashed()->findOrFail($id);
        $customer->forceDelete();

        return redirect()
            ->route('cp.customers.index')
            ->with('success', 'Customer permanently deleted.');
    }

    /**
     * Bulk actions for customers.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'action' => ['required', 'in:delete,enable,disable,verify'],
            'customers' => ['required', 'array'],
            'customers.*' => ['exists:customers,id'],
        ]);

        $customers = Customer::whereIn('id', $request->customers);

        switch ($request->action) {
            case 'delete':
                $customers->delete();
                $message = 'Selected customers deleted successfully.';
                break;
            case 'enable':
                $customers->update(['status' => 'active']);
                $message = 'Selected customers enabled successfully.';
                break;
            case 'disable':
                $customers->update(['status' => 'inactive']);
                $message = 'Selected customers disabled successfully.';
                break;
            case 'verify':
                $customers->update(['email_verified_at' => now()]);
                $message = 'Selected customers verified successfully.';
                break;
        }

        return redirect()
            ->route('cp.customers.index')
            ->with('success', $message);
    }

    /**
     * Get navigation items for the CP.
     */
    protected function getNavigationItems(): array
    {
        return [
            'dashboard' => [
                'display' => 'Dashboard',
                'url' => '/cp',
                'icon' => 'dashboard',
                'children' => [],
            ],
            'collections' => [
                'display' => 'Collections',
                'url' => '/cp/collections',
                'icon' => 'collection',
                'children' => [],
            ],
            'products' => [
                'display' => 'Products',
                'url' => '/cp/products',
                'icon' => 'box',
                'children' => [],
            ],
            'customers' => [
                'display' => 'Customers',
                'url' => '/cp/customers',
                'icon' => 'users',
                'children' => [],
            ],
            'orders' => [
                'display' => 'Orders',
                'url' => '/cp/orders',
                'icon' => 'shopping-cart',
                'children' => [],
            ],
        ];
    }

    /**
     * Get available sites for multisite support.
     */
    protected function getSites(): array
    {
        return [
            [
                'id' => 'default',
                'name' => config('app.name', 'Laravel Shopper'),
                'url' => config('app.url'),
                'is_current' => true,
            ],
        ];
    }
}
