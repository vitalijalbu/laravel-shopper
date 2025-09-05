<?php

namespace Shopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the control panel dashboard.
     */
    public function index(Request $request): Response
    {
        try {
            \Illuminate\Support\Facades\Log::info('DashboardController - Starting index method');

            $user = Auth::user();
            \Illuminate\Support\Facades\Log::info('DashboardController - Got user', ['user_id' => $user->id]);

            // Gather dashboard statistics
            $stats = $this->getDashboardStats();
            \Illuminate\Support\Facades\Log::info('DashboardController - Got stats');

            // Recent activities
            $recentActivities = $this->getRecentActivities();
            \Illuminate\Support\Facades\Log::info('DashboardController - Got activities');

            // System notifications
            $notifications = $this->getSystemNotifications();
            \Illuminate\Support\Facades\Log::info('DashboardController - Got notifications');

            // Simplified navigation to avoid timeout
            $nav = $this->getSimpleNavigationItems();
            \Illuminate\Support\Facades\Log::info('DashboardController - Got navigation');

            \Illuminate\Support\Facades\Log::info('DashboardController - About to render Inertia');

            return Inertia::render('index', [
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name ?? (($user->first_name ?? '').' '.($user->last_name ?? '')),
                    'email' => $user->email,
                    'avatar_url' => $user->avatar_url ?? null,
                    'roles' => [],
                    'permissions' => [],
                ],
                'navigation' => $nav,
                'sites' => $this->getSites(),
                'breadcrumbs' => [
                    ['title' => 'Dashboard', 'url' => null],
                ],
                'stats' => $stats,
                'recentActivities' => $recentActivities,
                'notifications' => $notifications,
                'app_name' => config('app.name'),
                'cp_name' => config('shopper.cp.name', 'Control Panel'),
                'locale' => app()->getLocale(),
                'locales' => config('shopper.locales', ['en', 'it']),
                'branding' => [
                    'logo' => config('shopper.cp.branding.logo'),
                    'logo_dark' => config('shopper.cp.branding.logo_dark'),
                    'favicon' => config('shopper.cp.branding.favicon'),
                ],
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('DashboardController index error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            throw $e;
        }
    }

    /**
     * Get dashboard statistics.
     */
    protected function getDashboardStats(): array
    {
        return [
            'total_products' => rand(50, 200),
            'total_orders' => rand(100, 500),
            'total_customers' => rand(200, 1000),
            'total_revenue' => number_format(rand(10000, 100000), 2),
            'orders_today' => rand(5, 25),
            'revenue_today' => number_format(rand(1000, 5000), 2),
        ];
    }

    /**
     * Get recent activities.
     */
    protected function getRecentActivities(): array
    {
        return [
            [
                'id' => 1,
                'type' => 'order.created',
                'description' => 'Nuovo ordine #1001 da John Doe',
                'user' => 'John Doe',
                'created_at' => now()->subMinutes(5)->toISOString(),
            ],
            [
                'id' => 2,
                'type' => 'product.updated',
                'description' => 'Prodotto "MacBook Pro" aggiornato',
                'user' => 'Admin',
                'created_at' => now()->subMinutes(15)->toISOString(),
            ],
            [
                'id' => 3,
                'type' => 'user.registered',
                'description' => 'Nuovo cliente: Jane Smith',
                'user' => 'Jane Smith',
                'created_at' => now()->subMinutes(30)->toISOString(),
            ],
        ];
    }

    /**
     * Get system notifications.
     */
    protected function getSystemNotifications(): array
    {
        return [
            [
                'id' => 1,
                'type' => 'update',
                'title' => 'Aggiornamento Sistema Disponibile',
                'message' => 'È disponibile una nuova versione di Laravel Shopper.',
                'action_text' => 'Aggiorna Ora',
                'action_url' => '#',
                'created_at' => now()->subHours(2)->toISOString(),
            ],
            [
                'id' => 2,
                'type' => 'warning',
                'title' => 'Allarme Scorte Basse',
                'message' => '5 prodotti hanno scorte in esaurimento.',
                'action_text' => 'Visualizza Prodotti',
                'action_url' => '/cp/products',
                'created_at' => now()->subHours(4)->toISOString(),
            ],
        ];
    }

    /**
     * Get navigation items for the CP.
     */
    protected function getNavigationItems(): array
    {
        return [
            [
                'name' => 'Dashboard',
                'icon' => 'dashboard',
                'route' => 'shopper.cp.dashboard',
                'active' => request()->routeIs('shopper.cp.dashboard*'),
            ],
            [
                'name' => 'Prodotti',
                'icon' => 'cube',
                'route' => '/cp/products',
                'active' => request()->routeIs('shopper.cp.products*'),
                'children' => [
                    ['name' => 'Tutti i Prodotti', 'route' => '/cp/products'],
                    ['name' => 'Categorie', 'route' => '/cp/products/categories'],
                    ['name' => 'Marchi', 'route' => '/cp/products/brands'],
                ],
            ],
            [
                'name' => 'Ordini',
                'icon' => 'shopping-bag',
                'route' => '/cp/orders',
                'active' => request()->routeIs('shopper.cp.orders*'),
                'children' => [
                    ['name' => 'Tutti gli Ordini', 'route' => '/cp/orders'],
                    ['name' => 'Clienti', 'route' => '/cp/orders/customers'],
                ],
            ],
            [
                'name' => 'Analytics',
                'icon' => 'chart-bar',
                'route' => '/cp/analytics',
                'active' => request()->routeIs('shopper.cp.analytics*'),
            ],
            [
                'name' => 'Utenti',
                'icon' => 'users',
                'route' => '/cp/users',
                'active' => request()->routeIs('shopper.cp.users*'),
            ],
            [
                'name' => 'Impostazioni',
                'icon' => 'cog',
                'route' => '/cp/settings',
                'active' => request()->routeIs('shopper.cp.settings*'),
            ],
        ];
    }

    /**
     * Get simplified navigation items to avoid timeout issues
     */
    protected function getSimpleNavigationItems(): array
    {
        return [
            'dashboard' => [
                'display' => 'Dashboard',
                'url' => '/cp',
                'icon' => 'home',
                'children' => [],
            ],
            'collections' => [
                'display' => 'Collections',
                'url' => '/cp/collections',
                'icon' => 'folder',
                'children' => [],
            ],
            'products' => [
                'display' => 'Products',
                'url' => '/cp/products',
                'icon' => 'package',
                'children' => [],
            ],
            'orders' => [
                'display' => 'Orders',
                'url' => '/cp/orders',
                'icon' => 'shopping-bag',
                'children' => [],
            ],
            'customers' => [
                'display' => 'Customers',
                'url' => '/cp/customers',
                'icon' => 'users',
                'children' => [],
            ],
            'settings' => [
                'display' => 'Settings',
                'url' => '/cp/settings',
                'icon' => 'settings',
                'children' => [],
            ],
        ];
    }

    /**
     * Get available sites for multisite support.
     */
    protected function getSites(): array
    {
        // For now, return a default site
        // This can be expanded for multisite functionality
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
