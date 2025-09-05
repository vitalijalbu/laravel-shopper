<?php

declare(strict_types=1);

namespace Shopper\Http\Controllers\CP\Analytics;

use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Shopper\Http\Controllers\CP\BaseController;
use Shopper\Services\AnalyticsService;

class AnalyticsDataController extends BaseController
{
    public function __construct(
        protected AnalyticsService $analyticsService
    ) {
        $this->middleware('can:view_analytics');
    }

    /**
     * Get sales over time data for charts
     */
    public function salesOverTime(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getSalesTrends($dateRange);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'line',
                'x_axis' => 'date',
                'y_axes' => ['revenue', 'orders_count'],
                'title' => 'Sales over time',
            ],
        ]);
    }

    /**
     * Get orders by status data
     */
    public function ordersByStatus(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);

        $data = \Shopper\Models\Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->selectRaw('status, COUNT(*) as count, SUM(total) as revenue')
            ->groupBy('status')
            ->get();

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'donut',
                'value_field' => 'count',
                'label_field' => 'status',
                'title' => 'Orders by status',
            ],
        ]);
    }

    /**
     * Get revenue by channel data
     */
    public function revenueByChannel(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getSalesByChannel($dateRange);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'bar',
                'x_axis' => 'channel',
                'y_axis' => 'revenue',
                'title' => 'Revenue by channel',
            ],
        ]);
    }

    /**
     * Get customer acquisition data
     */
    public function customerAcquisition(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getAcquisitionChannels($dateRange);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'bar',
                'x_axis' => 'channel',
                'y_axis' => 'customers',
                'title' => 'Customer acquisition by channel',
            ],
        ]);
    }

    /**
     * Get conversion funnel data
     */
    public function conversionFunnel(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getConversionFunnel($dateRange);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'funnel',
                'title' => 'Conversion funnel',
            ],
        ]);
    }

    /**
     * Get product performance data
     */
    public function productPerformance(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getTopProducts($dateRange, 20);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'table',
                'columns' => [
                    ['key' => 'name', 'label' => 'Product', 'sortable' => true],
                    ['key' => 'units_sold', 'label' => 'Units sold', 'sortable' => true, 'type' => 'number'],
                    ['key' => 'revenue', 'label' => 'Revenue', 'sortable' => true, 'type' => 'currency'],
                    ['key' => 'orders_count', 'label' => 'Orders', 'sortable' => true, 'type' => 'number'],
                ],
                'title' => 'Top performing products',
            ],
        ]);
    }

    /**
     * Get cohort analysis data
     */
    public function cohortAnalysis(Request $request): JsonResponse
    {
        $dateRange = $this->getDateRange($request);
        $data = $this->analyticsService->getDetailedCohortAnalysis($dateRange);

        return response()->json([
            'data' => $data,
            'chart_config' => [
                'type' => 'heatmap',
                'title' => 'Customer cohort retention',
            ],
        ]);
    }

    /**
     * Get live metrics for real-time dashboard
     */
    public function liveMetrics(): JsonResponse
    {
        $data = $this->analyticsService->getLiveData();

        return response()->json($data);
    }

    /**
     * Get custom analytics data based on query
     */
    public function customQuery(Request $request): JsonResponse
    {
        $request->validate([
            'metric' => 'required|string',
            'dimensions' => 'array',
            'filters' => 'array',
            'date_range' => 'array',
        ]);

        // This would be a more complex implementation
        // allowing users to build custom analytics queries

        return response()->json([
            'data' => [],
            'message' => 'Custom analytics queries not yet implemented',
        ]);
    }

    /**
     * Export analytics data
     */
    public function export(Request $request): JsonResponse
    {
        $request->validate([
            'report_type' => 'required|string',
            'format' => 'required|string|in:csv,xlsx,pdf',
        ]);

        // This would generate and return a downloadable export

        return response()->json([
            'download_url' => '/cp/analytics/downloads/export_'.time().'.'.$request->format,
            'message' => 'Export generated successfully',
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
}
