<?php

namespace LaravelShopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;
use LaravelShopper\Http\Controllers\Controller;

class DashboardController extends Controller
{
    /**
     * Display the control panel dashboard.
     */
    public function index(Request $request): Response
    {
        $user = Auth::user();

        // Gather dashboard statistics
        $stats = $this->getDashboardStats();

        // Recent activities
        $recentActivities = $this->getRecentActivities();

        // System notifications
        $notifications = $this->getSystemNotifications();

        return Inertia::render('Cp/Dashboard/Index', [
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'avatar_url' => $user->avatar_url ?? null,
                'roles' => method_exists($user, 'getRoleNames') ? $user->getRoleNames() : [],
                'permissions' => method_exists($user, 'getAllPermissions') ? $user->getAllPermissions() : [],
            ],
            'stats' => $stats,
            'recentActivities' => $recentActivities,
            'notifications' => $notifications,
            'app_name' => config('app.name'),
            'cp_name' => config('shopper.cp.name', 'Control Panel'),
            'locale' => app()->getLocale(),
            'locales' => config('shopper.locales', ['en', 'it']),
            'nav' => $this->getNavigationItems(),
            'branding' => [
                'logo' => config('shopper.cp.branding.logo'),
                'logo_dark' => config('shopper.cp.branding.logo_dark'),
                'favicon' => config('shopper.cp.branding.favicon'),
            ],
        ]);
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
}
