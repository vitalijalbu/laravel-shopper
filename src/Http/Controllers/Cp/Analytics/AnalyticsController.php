<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Response;
use Shopper\CP\Page;
use Shopper\Http\Controllers\CP\BaseController;
use Shopper\Services\AnalyticsService;

class AnalyticsController extends BaseController
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('can:view_analytics');
    }

    /**
     * Analytics dashboard overview
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics');

        $dateRange = $this->getDateRange($request);
        $previousRange = $this->getPreviousDateRange($dateRange);

        // Get overview metrics
        $metrics = $this->analyticsService->getOverviewMetrics($dateRange, $previousRange);
        $topProducts = $this->analyticsService->getTopProducts($dateRange, 10);
        $topCollections = $this->analyticsService->getTopCollections($dateRange, 10);
        $recentOrders = $this->analyticsService->getRecentOrders(10);
        $conversionFunnel = $this->analyticsService->getConversionFunnel($dateRange);

        $page = Page::make('Analytics')
            ->subtitle('Track your store performance')
            ->tabs([
                'overview' => ['label' => 'Overview', 'component' => 'AnalyticsOverview'],
                'sales' => ['label' => 'Sales', 'component' => 'AnalyticsSales'],
                'customers' => ['label' => 'Customers', 'component' => 'AnalyticsCustomers'],
                'products' => ['label' => 'Products', 'component' => 'AnalyticsProducts'],
                'marketing' => ['label' => 'Marketing', 'component' => 'AnalyticsMarketing'],
                'reports' => ['label' => 'Reports', 'component' => 'AnalyticsReports'],
            ]);

        return $this->inertiaResponse('analytics/Index', [
            'page' => $page->compile(),
            'metrics' => $metrics,
            'topProducts' => $topProducts,
            'topCollections' => $topCollections,
            'recentOrders' => $recentOrders,
            'conversionFunnel' => $conversionFunnel,
            'dateRange' => $dateRange,
            'charts' => [
                'salesOverTime' => route('cp.analytics.data.sales-over-time'),
                'ordersByStatus' => route('cp.analytics.data.orders-by-status'),
                'revenueByChannel' => route('cp.analytics.data.revenue-by-channel'),
                'customerAcquisition' => route('cp.analytics.data.customer-acquisition'),
            ],
        ]);
    }

    /**
     * Live view - real-time analytics
     */
    public function liveView(): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Live view');

        $page = Page::make('Live view')
            ->subtitle('Real-time visitor activity');

        $liveData = $this->analyticsService->getLiveData();

        return $this->inertiaResponse('analytics/LiveView', [
            'page' => $page->compile(),
            'liveData' => $liveData,
            'refreshInterval' => 30, // seconds
        ]);
    }

    /**
     * Get date range from request
     */
    protected function getDateRange(Request $request): array
    {
        $period = $request->get('period', 'last_30_days');

        return match ($period) {
            'today' => [
                'start' => Carbon::today(),
                'end' => Carbon::now(),
                'label' => 'Today',
            ],
            'yesterday' => [
                'start' => Carbon::yesterday(),
                'end' => Carbon::yesterday()->endOfDay(),
                'label' => 'Yesterday',
            ],
            'last_7_days' => [
                'start' => Carbon::now()->subDays(6)->startOfDay(),
                'end' => Carbon::now(),
                'label' => 'Last 7 days',
            ],
            'last_30_days' => [
                'start' => Carbon::now()->subDays(29)->startOfDay(),
                'end' => Carbon::now(),
                'label' => 'Last 30 days',
            ],
            'last_90_days' => [
                'start' => Carbon::now()->subDays(89)->startOfDay(),
                'end' => Carbon::now(),
                'label' => 'Last 3 months',
            ],
            'this_month' => [
                'start' => Carbon::now()->startOfMonth(),
                'end' => Carbon::now(),
                'label' => 'This month',
            ],
            'last_month' => [
                'start' => Carbon::now()->subMonth()->startOfMonth(),
                'end' => Carbon::now()->subMonth()->endOfMonth(),
                'label' => 'Last month',
            ],
            'this_year' => [
                'start' => Carbon::now()->startOfYear(),
                'end' => Carbon::now(),
                'label' => 'This year',
            ],
            'custom' => [
                'start' => Carbon::parse($request->get('start_date', Carbon::now()->subDays(29))),
                'end' => Carbon::parse($request->get('end_date', Carbon::now())),
                'label' => 'Custom range',
            ],
            default => [
                'start' => Carbon::now()->subDays(29)->startOfDay(),
                'end' => Carbon::now(),
                'label' => 'Last 30 days',
            ],
        };
    }

    /**
     * Get previous date range for comparison
     */
    protected function getPreviousDateRange(array $dateRange): array
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $days = $start->diffInDays($end) + 1;

        return [
            'start' => $start->copy()->subDays($days),
            'end' => $start->copy()->subDay(),
            'label' => 'Previous period',
        ];
    }
}
