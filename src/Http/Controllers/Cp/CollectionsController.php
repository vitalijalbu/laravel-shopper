<?php

namespace Cartino\Http\Controllers\CP;

use Cartino\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class CollectionsController extends Controller
{
    public function index(Request $request): Response
    {
        $collections = collect([
            // E-commerce Collections
            [
                'id' => 1,
                'handle' => 'products',
                'title' => 'Products',
                'description' => 'All your store products and variants',
                'icon' => 'box',
                'color' => 'bg-blue-500',
                'is_published' => true,
                'entries_count' => 156,
                'created_at' => now()->subDays(30)->toISOString(),
                'updated_at' => now()->subDays(2)->toISOString(),
                'section' => 'ecommerce',
            ],
            [
                'id' => 2,
                'handle' => 'categories',
                'title' => 'Categories',
                'description' => 'Product categories and classifications',
                'icon' => 'folder',
                'color' => 'bg-green-500',
                'is_published' => true,
                'entries_count' => 24,
                'created_at' => now()->subDays(25)->toISOString(),
                'updated_at' => now()->subDays(5)->toISOString(),
                'section' => 'ecommerce',
            ],
            [
                'id' => 3,
                'handle' => 'brands',
                'title' => 'Brands',
                'description' => 'Product brands and manufacturers',
                'icon' => 'collection',
                'color' => 'bg-purple-500',
                'is_published' => true,
                'entries_count' => 12,
                'created_at' => now()->subDays(20)->toISOString(),
                'updated_at' => now()->subDays(7)->toISOString(),
                'section' => 'ecommerce',
            ],
            [
                'id' => 4,
                'handle' => 'orders',
                'title' => 'Orders',
                'description' => 'Customer orders and transactions',
                'icon' => 'shopping-cart',
                'color' => 'bg-yellow-500',
                'is_published' => true,
                'entries_count' => 89,
                'created_at' => now()->subDays(35)->toISOString(),
                'updated_at' => now()->subHours(2)->toISOString(),
                'section' => 'ecommerce',
            ],
            [
                'id' => 5,
                'handle' => 'customers',
                'title' => 'Customers',
                'description' => 'Customer profiles and accounts',
                'icon' => 'users',
                'color' => 'bg-indigo-500',
                'is_published' => true,
                'entries_count' => 342,
                'created_at' => now()->subDays(40)->toISOString(),
                'updated_at' => now()->subDays(1)->toISOString(),
                'section' => 'ecommerce',
            ],

            // Content Collections
            [
                'id' => 6,
                'handle' => 'pages',
                'title' => 'Pages',
                'description' => 'Website pages and static content',
                'icon' => 'document-text',
                'color' => 'bg-gray-500',
                'is_published' => true,
                'entries_count' => 18,
                'created_at' => now()->subDays(45)->toISOString(),
                'updated_at' => now()->subDays(3)->toISOString(),
                'section' => 'content',
            ],
            [
                'id' => 7,
                'handle' => 'blog-posts',
                'title' => 'Blog Posts',
                'description' => 'Blog articles and news updates',
                'icon' => 'document-text',
                'color' => 'bg-red-500',
                'is_published' => true,
                'entries_count' => 67,
                'created_at' => now()->subDays(50)->toISOString(),
                'updated_at' => now()->subHours(6)->toISOString(),
                'section' => 'content',
            ],
            [
                'id' => 8,
                'handle' => 'testimonials',
                'title' => 'Testimonials',
                'description' => 'Customer reviews and testimonials',
                'icon' => 'collection',
                'color' => 'bg-pink-500',
                'is_published' => true,
                'entries_count' => 23,
                'created_at' => now()->subDays(15)->toISOString(),
                'updated_at' => now()->subDays(4)->toISOString(),
                'section' => 'content',
            ],

            // Custom Collections
            [
                'id' => 9,
                'handle' => 'events',
                'title' => 'Events',
                'description' => 'Upcoming events and workshops',
                'icon' => 'collection',
                'color' => 'bg-teal-500',
                'is_published' => false,
                'entries_count' => 8,
                'created_at' => now()->subDays(10)->toISOString(),
                'updated_at' => now()->subDays(2)->toISOString(),
                'section' => 'custom',
            ],
            [
                'id' => 10,
                'handle' => 'faqs',
                'title' => 'FAQs',
                'description' => 'Frequently asked questions',
                'icon' => 'document-text',
                'color' => 'bg-orange-500',
                'is_published' => true,
                'entries_count' => 31,
                'created_at' => now()->subDays(22)->toISOString(),
                'updated_at' => now()->subDays(8)->toISOString(),
                'section' => 'custom',
            ],
        ]);

        // Apply filters
        if ($request->filled('section')) {
            $collections = $collections->where('section', $request->get('section'));
        }

        if ($request->filled('status')) {
            $isPublished = $request->get('status') === 'published';
            $collections = $collections->where('is_published', $isPublished);
        }

        if ($request->filled('search')) {
            $search = strtolower($request->get('search'));
            $collections = $collections->filter(function ($collection) use ($search) {
                return str_contains(strtolower($collection['title']), $search) ||
                       str_contains(strtolower($collection['handle']), $search) ||
                       str_contains(strtolower($collection['description']), $search);
            });
        }

        // Sort
        $sortBy = $request->get('sort_by', 'title');
        $sortDirection = $request->get('sort_direction', 'asc');

        $collections = $collections->sortBy($sortBy, SORT_REGULAR, $sortDirection === 'desc');

        // Group by section for display
        $grouped = $collections->groupBy('section');

        return Inertia::render('collections/index', [
            'collections' => $collections->values(),
            'grouped' => $grouped,
            'sections' => [
                'ecommerce' => [
                    'title' => 'E-commerce',
                    'description' => 'Product catalog and sales management',
                    'count' => $grouped->get('ecommerce', collect())->count(),
                ],
                'content' => [
                    'title' => 'Content',
                    'description' => 'Website content and media',
                    'count' => $grouped->get('content', collect())->count(),
                ],
                'custom' => [
                    'title' => 'Custom',
                    'description' => 'Custom content types',
                    'count' => $grouped->get('custom', collect())->count(),
                ],
            ],
            // Props per il layout
            'user' => Auth::user(),
            'navigation' => $this->getNavigationItems(),
            'sites' => $this->getSites(),
            'breadcrumbs' => [
                ['title' => 'Collections', 'url' => null],
            ],
        ]);
    }

    public function show(string $handle): JsonResponse
    {
        // Mock collection data - in real app would fetch from database
        $collection = [
            'id' => 1,
            'handle' => $handle,
            'title' => 'Products',
            'description' => 'All your store products and variants',
            'icon' => 'box',
            'color' => 'bg-blue-500',
            'is_published' => true,
            'entries_count' => 156,
            'created_at' => now()->subDays(30)->toISOString(),
            'updated_at' => now()->subDays(2)->toISOString(),
            'section' => 'ecommerce',
            'fields' => [
                [
                    'handle' => 'title',
                    'type' => 'text',
                    'display_name' => 'Title',
                    'required' => true,
                ],
                [
                    'handle' => 'description',
                    'type' => 'textarea',
                    'display_name' => 'Description',
                    'required' => false,
                ],
                [
                    'handle' => 'price',
                    'type' => 'number',
                    'display_name' => 'Price',
                    'required' => true,
                ],
                [
                    'handle' => 'stock_quantity',
                    'type' => 'number',
                    'display_name' => 'Stock Quantity',
                    'required' => true,
                ],
                [
                    'handle' => 'featured_image',
                    'type' => 'asset',
                    'display_name' => 'Featured Image',
                    'required' => false,
                ],
            ],
        ];

        return response()->json(['collection' => $collection]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'handle' => 'required|string|max:255|unique:collections,handle',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'section' => 'required|in:ecommerce,content,custom',
        ]);

        // Mock creation - in real app would create in database
        $collection = [
            'id' => rand(1000, 9999),
            'handle' => $request->get('handle'),
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'icon' => $request->get('icon', 'collection'),
            'color' => $request->get('color', 'bg-blue-500'),
            'is_published' => false,
            'entries_count' => 0,
            'section' => $request->get('section'),
            'created_at' => now()->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        return response()->json([
            'collection' => $collection,
            'message' => 'Category created successfully',
        ], 201);
    }

    public function update(Request $request, string $handle): JsonResponse
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string',
            'color' => 'nullable|string',
            'is_published' => 'boolean',
        ]);

        // Mock update - in real app would update in database
        $collection = [
            'id' => 1,
            'handle' => $handle,
            'title' => $request->get('title'),
            'description' => $request->get('description'),
            'icon' => $request->get('icon', 'collection'),
            'color' => $request->get('color', 'bg-blue-500'),
            'is_published' => $request->get('is_published', false),
            'entries_count' => 156,
            'section' => 'ecommerce',
            'created_at' => now()->subDays(30)->toISOString(),
            'updated_at' => now()->toISOString(),
        ];

        return response()->json([
            'collection' => $collection,
            'message' => 'Category updated successfully',
        ]);
    }

    public function destroy(string $handle): JsonResponse
    {
        // Mock deletion - in real app would delete from database
        return response()->json([
            'message' => 'Category deleted successfully',
        ]);
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
                'name' => config('app.name', 'Cartino'),
                'url' => config('app.url'),
                'is_current' => true,
            ],
        ];
    }
}
