<?php

namespace LaravelShopper\Http\Controllers\Cp;

use Illuminate\Http\Request;
use Inertia\Inertia;
use LaravelShopper\CP\Dashboard;
use LaravelShopper\CP\Navigation;
use LaravelShopper\CP\Page;
use LaravelShopper\Http\Controllers\Controller;

class DashboardController extends Controller
{
    public function index()
    {
        $page = Page::make('Dashboard')
            ->breadcrumb('Home', '/cp')
            ->headerAction('QuickActions', ['actions' => Dashboard::quickActions()]);

        // Sales Overview Card
        $page->card('Sales Overview')
            ->content('SalesChart', ['period' => '30d'])
            ->action('View reports', '/cp/analytics');

        // Layout with metrics and recent orders
        $layout = $page->layout();

        // Primary column - Recent orders
        $layout->oneColumn()
            ->primary('RecentOrders', [
                'orders' => Dashboard::data()['recent_orders'],
            ]);

        // Metrics cards
        $layout->twoColumns(2, 1)
            ->primary('MetricsGrid', [
                'metrics' => Dashboard::metrics(),
            ])
            ->secondary('QuickStats', [
                'stats' => Dashboard::data()['sales_stats'],
            ]);

        return Inertia::render('CP/Dashboard/Index', [
            'page' => $page->compile(),
            'navigation' => Navigation::tree(),
        ]);
    }
}
