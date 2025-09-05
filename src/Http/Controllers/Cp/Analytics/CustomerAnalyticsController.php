<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP\Analytics;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Response;
use Shopper\CP\Page;
use Shopper\Http\Controllers\CP\BaseController;
use Shopper\Services\AnalyticsService;

class CustomerAnalyticsController extends BaseController
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('can:view_analytics');
    }

    /**
     * Customer analytics dashboard
     */
    public function index(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Customers');

        $dateRange = $this->getDateRange($request);
        $previousRange = $this->getPreviousDateRange($dateRange);

        $customerMetrics = $this->analyticsService->getCustomerMetrics($dateRange, $previousRange);
        $cohortAnalysis = $this->analyticsService->getCohortAnalysis($dateRange);
        $customerSegments = $this->analyticsService->getCustomerSegments($dateRange);
        $acquisitionChannels = $this->analyticsService->getAcquisitionChannels($dateRange);
        $retentionRates = $this->analyticsService->getRetentionRates($dateRange);

        $page = Page::make('Customer analytics')
            ->subtitle('Understand your customer behavior and lifetime value');

        return $this->inertiaResponse('analytics/Customers', [
            'page' => $page->compile(),
            'customerMetrics' => $customerMetrics,
            'cohortAnalysis' => $cohortAnalysis,
            'customerSegments' => $customerSegments,
            'acquisitionChannels' => $acquisitionChannels,
            'retentionRates' => $retentionRates,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Customer lifetime value analysis
     */
    public function lifetimeValue(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Customers', 'cp.analytics.customers.index')
            ->addBreadcrumb('Lifetime value');

        $dateRange = $this->getDateRange($request);
        $clvData = $this->analyticsService->getCustomerLifetimeValue($dateRange);

        $page = Page::make('Customer lifetime value')
            ->subtitle('Analyze customer value over time');

        return $this->inertiaResponse('analytics/customers/LifetimeValue', [
            'page' => $page->compile(),
            'clvData' => $clvData,
            'dateRange' => $dateRange,
        ]);
    }

    /**
     * Customer cohort analysis
     */
    public function cohorts(Request $request): Response
    {
        $this->addDashboardBreadcrumb()
            ->addBreadcrumb('Analytics', 'cp.analytics.index')
            ->addBreadcrumb('Customers', 'cp.analytics.customers.index')
            ->addBreadcrumb('Cohort analysis');

        $dateRange = $this->getDateRange($request);
        $cohortData = $this->analyticsService->getDetailedCohortAnalysis($dateRange);

        $page = Page::make('Cohort analysis')
            ->subtitle('Track customer retention over time');

        return $this->inertiaResponse('analytics/customers/Cohorts', [
            'page' => $page->compile(),
            'cohortData' => $cohortData,
            'dateRange' => $dateRange,
        ]);
    }

    private function getDateRange(Request $request): array
    {
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
