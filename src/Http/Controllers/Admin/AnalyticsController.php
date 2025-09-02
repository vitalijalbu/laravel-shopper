<?php

namespace Shopper\Http\Controllers\Admin;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Shopper\Models\AnalyticsEvent;
use Shopper\Models\UserPreference;

class AnalyticsController extends Controller
{
    public function dashboard(Request $request): Response
    {
        $period = $request->get('period', '30'); // days
        $dateFrom = now()->subDays((int) $period);
        $dateTo = now();

        // Get user preferences for dashboard widgets
        $userId = auth()->id();
        $widgets = UserPreference::getDashboardWidgets($userId, [
            'revenue_chart',
            'orders_chart',
            'visitors_chart',
            'top_products',
            'recent_orders',
        ]);

        // Revenue data
        $revenueData = $this->getRevenueData($dateFrom, $dateTo);

        // Orders data
        $ordersData = $this->getOrdersData($dateFrom, $dateTo);

        // Visitors data
        $visitorsData = $this->getVisitorsData($dateFrom, $dateTo);

        // Top products
        $topProducts = $this->getTopProducts($dateFrom, $dateTo);

        // Recent orders
        $recentOrders = $this->getRecentOrders();

        // Summary stats
        $stats = [
            'total_revenue' => $revenueData['total'] ?? 0,
            'total_orders' => $ordersData['total'] ?? 0,
            'total_visitors' => $visitorsData['total'] ?? 0,
            'conversion_rate' => $this->getConversionRate($dateFrom, $dateTo),
        ];

        return Inertia::render('Analytics/analytics-dashboard', [
            'stats' => $stats,
            'revenue_data' => $revenueData,
            'orders_data' => $ordersData,
            'visitors_data' => $visitorsData,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
            'widgets' => $widgets,
            'period' => $period,
        ]);
    }

    public function events(Request $request): Response
    {
        $query = AnalyticsEvent::query()->latest('occurred_at');

        // Filter by event type
        if ($eventType = $request->get('event_type')) {
            $query->byEventType($eventType);
        }

        // Filter by date range
        if ($dateFrom = $request->get('date_from')) {
            $query->where('occurred_at', '>=', Carbon::parse($dateFrom));
        }

        if ($dateTo = $request->get('date_to')) {
            $query->where('occurred_at', '<=', Carbon::parse($dateTo));
        }

        // Filter by user
        if ($userId = $request->get('user_id')) {
            $query->byUser($userId);
        }

        $events = $query->paginate(50)->withQueryString();

        // Get available event types for filter
        $eventTypes = AnalyticsEvent::distinct('event_type')
            ->pluck('event_type')
            ->sort()
            ->values();

        return Inertia::render('Analytics/events-index', [
            'events' => $events,
            'event_types' => $eventTypes,
            'filters' => $request->only(['event_type', 'date_from', 'date_to', 'user_id']),
        ]);
    }

    public function reports(Request $request): Response
    {
        $reportType = $request->get('type', 'overview');
        $period = $request->get('period', '30');
        $dateFrom = now()->subDays((int) $period);
        $dateTo = now();

        $data = match ($reportType) {
            'traffic' => $this->getTrafficReport($dateFrom, $dateTo),
            'sales' => $this->getSalesReport($dateFrom, $dateTo),
            'products' => $this->getProductsReport($dateFrom, $dateTo),
            'customers' => $this->getCustomersReport($dateFrom, $dateTo),
            default => $this->getOverviewReport($dateFrom, $dateTo),
        };

        return Inertia::render('Analytics/analytics-reports', [
            'report_type' => $reportType,
            'period' => $period,
            'data' => $data,
        ]);
    }

    public function saveUserPreferences(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|string',
            'key' => 'required|string',
            'value' => 'required|array',
        ]);

        UserPreference::setForUser(
            auth()->id(),
            $validated['type'],
            $validated['key'],
            $validated['value']
        );

        return response()->json([
            'message' => 'Preferences saved successfully',
        ]);
    }

    private function getRevenueData(Carbon $from, Carbon $to): array
    {
        // This would typically query your orders table
        // For now, return mock data
        return [
            'total' => 125000,
            'chart_data' => [
                ['date' => '2024-01-01', 'value' => 5000],
                ['date' => '2024-01-02', 'value' => 7500],
                // ... more data points
            ],
        ];
    }

    private function getOrdersData(Carbon $from, Carbon $to): array
    {
        return [
            'total' => 456,
            'chart_data' => [
                ['date' => '2024-01-01', 'value' => 15],
                ['date' => '2024-01-02', 'value' => 23],
                // ... more data points
            ],
        ];
    }

    private function getVisitorsData(Carbon $from, Carbon $to): array
    {
        $events = AnalyticsEvent::byEventType('page_view')
            ->inDateRange($from, $to)
            ->selectRaw('DATE(occurred_at) as date, COUNT(DISTINCT session_id) as visitors')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return [
            'total' => $events->sum('visitors'),
            'chart_data' => $events->map(function ($item) {
                return [
                    'date' => $item->date,
                    'value' => (int) $item->visitors,
                ];
            })->toArray(),
        ];
    }

    private function getTopProducts(Carbon $from, Carbon $to): array
    {
        // Mock data - replace with actual product views/sales query
        return [
            ['id' => 1, 'name' => 'MacBook Pro', 'views' => 1250, 'sales' => 45],
            ['id' => 2, 'name' => 'iPhone 15', 'views' => 980, 'sales' => 67],
            // ... more products
        ];
    }

    private function getRecentOrders(): array
    {
        // Mock data - replace with actual recent orders query
        return [
            ['id' => 1001, 'customer' => 'John Doe', 'total' => 299.99, 'status' => 'completed'],
            ['id' => 1002, 'customer' => 'Jane Smith', 'total' => 459.99, 'status' => 'pending'],
            // ... more orders
        ];
    }

    private function getConversionRate(Carbon $from, Carbon $to): float
    {
        $visitors = AnalyticsEvent::byEventType('page_view')
            ->inDateRange($from, $to)
            ->distinct('session_id')
            ->count();

        $orders = AnalyticsEvent::byEventType('order_placed')
            ->inDateRange($from, $to)
            ->count();

        return $visitors > 0 ? round(($orders / $visitors) * 100, 2) : 0;
    }

    private function getOverviewReport(Carbon $from, Carbon $to): array
    {
        return [
            'summary' => [
                'revenue' => $this->getRevenueData($from, $to),
                'orders' => $this->getOrdersData($from, $to),
                'visitors' => $this->getVisitorsData($from, $to),
            ],
            'trends' => [
                // Weekly/monthly trends
            ],
        ];
    }

    private function getTrafficReport(Carbon $from, Carbon $to): array
    {
        return [
            'page_views' => AnalyticsEvent::byEventType('page_view')->inDateRange($from, $to)->count(),
            'unique_visitors' => AnalyticsEvent::byEventType('page_view')->inDateRange($from, $to)->distinct('session_id')->count(),
            'bounce_rate' => 0.35, // Calculate based on single-page sessions
            'average_session_duration' => '2:34', // Calculate from session data
        ];
    }

    private function getSalesReport(Carbon $from, Carbon $to): array
    {
        return [
            'total_sales' => 125000,
            'orders_count' => 456,
            'average_order_value' => 274.12,
            'top_selling_products' => $this->getTopProducts($from, $to),
        ];
    }

    private function getProductsReport(Carbon $from, Carbon $to): array
    {
        return [
            'most_viewed' => $this->getTopProducts($from, $to),
            'best_converting' => [],
            'inventory_alerts' => [],
        ];
    }

    private function getCustomersReport(Carbon $from, Carbon $to): array
    {
        return [
            'new_customers' => 45,
            'returning_customers' => 123,
            'customer_lifetime_value' => 456.78,
            'top_customers' => [],
        ];
    }
}
