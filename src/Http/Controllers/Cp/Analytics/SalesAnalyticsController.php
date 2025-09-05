<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Response;
use Shopper\CP\Page;
use Shopper\Http\Controllers\CP\BaseController;
use Shopper\Services\AnalyticsService;

class SalesAnalyticsController extends BaseController
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('can:view_analytics');
    }

    /**
     * Sales analytics dashboard
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Sales');

        $dateRange = $this->getDateRange($request);
        $previousRange = $this->getPreviousDateRange($dateRange);

        $salesMetrics = $this->analyticsService->getSalesMetrics($dateRange, $previousRange);
        $salesTrends = $this->analyticsService->getSalesTrends($dateRange);
        $salesByChannel = $this->analyticsService->getSalesByChannel($dateRange);
        $salesByLocation = $this->analyticsService->getSalesByLocation($dateRange);
        $topSellingProducts = $this->analyticsService->getTopSellingProducts($dateRange, 20);

        $page = Page::make('Sales analytics')
            ->subtitle('Analyze your sales performance')
            ->secondaryActions([
                ['label' => 'Export data', 'action' => 'export'],
                ['label' => 'Schedule report', 'action' => 'schedule'],
            ]);

        return $this->inertiaResponse('analytics/Sales', [
            'page' => $page->compile(),
            'salesMetrics' => $salesMetrics,
            'salesTrends' => $salesTrends,
            'salesByChannel' => $salesByChannel,
            'salesByLocation' => $salesByLocation,
            'topSellingProducts' => $topSellingProducts,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Sales by product performance
     */
    public function products(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Sales', 'cp.analytics.sales.index')
            ->addBreadcrumb('Product performance');

        $dateRange = $this->getDateRange($request);
        $products = $this->analyticsService->getProductSalesAnalytics($dateRange);

        $page = Page::make('Product sales performance')
            ->subtitle('Analyze individual product performance');

        return $this->inertiaResponse('analytics/sales/Products', [
            'page' => $page->compile(),
            'products' => $products,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Sales funnel analysis
     */
    public function funnel(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Sales', 'cp.analytics.sales.index')
            ->addBreadcrumb('Sales funnel');

        $dateRange = $this->getDateRange($request);
        $funnelData = $this->analyticsService->getSalesFunnel($dateRange);

        $page = Page::make('Sales funnel analysis')
            ->subtitle('Track customer journey from visit to purchase');

        return $this->inertiaResponse('analytics/sales/Funnel', [
            'page' => $page->compile(),
            'funnelData' => $funnelData,
            'dateRange' => $dateRange,
        ]);
    }

    private function getDateRange(Request $request): array
    {
        // Same logic as AnalyticsController
        $period = $request->get('period', 'last_30_days');

        return match ($period) {
            'today' => [
                'start' => Carbon::today(),
                'end' => Carbon::now(),
                'label' => 'Today',
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
            default => [
                'start' => Carbon::now()->subDays(29)->startOfDay(),
                'end' => Carbon::now(),
                'label' => 'Last 30 days',
            ],
        };
    }

    private function getPreviousDateRange(array $dateRange): array
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
