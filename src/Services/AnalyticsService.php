<?php

declare(strict_types=1);

namespace Shopper\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Shopper\Models\Customer;
use Shopper\Models\Order;
use Shopper\Models\OrderItem;
use Shopper\Models\Product;

class AnalyticsService
{
    /**
     * Get overview metrics for dashboard
     */
    public function getOverviewMetrics(array $dateRange, array $previousRange): array
    {
        $current = $this->calculatePeriodMetrics($dateRange);
        $previous = $this->calculatePeriodMetrics($previousRange);

        return [
            'total_sales' => [
                'value' => $current['total_sales'],
                'previous' => $previous['total_sales'],
                'change' => $this->calculatePercentageChange($current['total_sales'], $previous['total_sales']),
                'format' => 'currency',
            ],
            'orders' => [
                'value' => $current['orders'],
                'previous' => $previous['orders'],
                'change' => $this->calculatePercentageChange($current['orders'], $previous['orders']),
                'format' => 'number',
            ],
            'average_order_value' => [
                'value' => $current['average_order_value'],
                'previous' => $previous['average_order_value'],
                'change' => $this->calculatePercentageChange($current['average_order_value'], $previous['average_order_value']),
                'format' => 'currency',
            ],
            'conversion_rate' => [
                'value' => $current['conversion_rate'],
                'previous' => $previous['conversion_rate'],
                'change' => $this->calculatePercentageChange($current['conversion_rate'], $previous['conversion_rate']),
                'format' => 'percentage',
            ],
            'returning_customers' => [
                'value' => $current['returning_customers'],
                'previous' => $previous['returning_customers'],
                'change' => $this->calculatePercentageChange($current['returning_customers'], $previous['returning_customers']),
                'format' => 'percentage',
            ],
            'sessions' => [
                'value' => $current['sessions'],
                'previous' => $previous['sessions'],
                'change' => $this->calculatePercentageChange($current['sessions'], $previous['sessions']),
                'format' => 'number',
            ],
        ];
    }

    /**
     * Get sales metrics for specific period
     */
    public function getSalesMetrics(array $dateRange, array $previousRange): array
    {
        $current = $this->calculateSalesMetrics($dateRange);
        $previous = $this->calculateSalesMetrics($previousRange);

        return [
            'gross_sales' => [
                'value' => $current['gross_sales'],
                'previous' => $previous['gross_sales'],
                'change' => $this->calculatePercentageChange($current['gross_sales'], $previous['gross_sales']),
            ],
            'net_sales' => [
                'value' => $current['net_sales'],
                'previous' => $previous['net_sales'],
                'change' => $this->calculatePercentageChange($current['net_sales'], $previous['net_sales']),
            ],
            'tax_collected' => [
                'value' => $current['tax_collected'],
                'previous' => $previous['tax_collected'],
                'change' => $this->calculatePercentageChange($current['tax_collected'], $previous['tax_collected']),
            ],
            'shipping_collected' => [
                'value' => $current['shipping_collected'],
                'previous' => $previous['shipping_collected'],
                'change' => $this->calculatePercentageChange($current['shipping_collected'], $previous['shipping_collected']),
            ],
            'refunds' => [
                'value' => $current['refunds'],
                'previous' => $previous['refunds'],
                'change' => $this->calculatePercentageChange($current['refunds'], $previous['refunds']),
            ],
        ];
    }

    /**
     * Get customer metrics
     */
    public function getCustomerMetrics(array $dateRange, array $previousRange): array
    {
        $current = $this->calculateCustomerMetrics($dateRange);
        $previous = $this->calculateCustomerMetrics($previousRange);

        return [
            'new_customers' => [
                'value' => $current['new_customers'],
                'previous' => $previous['new_customers'],
                'change' => $this->calculatePercentageChange($current['new_customers'], $previous['new_customers']),
            ],
            'returning_customers' => [
                'value' => $current['returning_customers'],
                'previous' => $previous['returning_customers'],
                'change' => $this->calculatePercentageChange($current['returning_customers'], $previous['returning_customers']),
            ],
            'customer_lifetime_value' => [
                'value' => $current['customer_lifetime_value'],
                'previous' => $previous['customer_lifetime_value'],
                'change' => $this->calculatePercentageChange($current['customer_lifetime_value'], $previous['customer_lifetime_value']),
            ],
            'repeat_purchase_rate' => [
                'value' => $current['repeat_purchase_rate'],
                'previous' => $previous['repeat_purchase_rate'],
                'change' => $this->calculatePercentageChange($current['repeat_purchase_rate'], $previous['repeat_purchase_rate']),
            ],
        ];
    }

    /**
     * Get top products by sales
     */
    public function getTopProducts(array $dateRange, int $limit = 10): Collection
    {
        return OrderItem::query()
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.status', '!=', 'cancelled')
            ->select([
                'products.id',
                'products.name',
                'products.slug',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
            ])
            ->groupBy('products.id', 'products.name', 'products.slug')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get top collections by sales
     */
    public function getTopCollections(array $dateRange, int $limit = 10): Collection
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('collection_product', 'products.id', '=', 'collection_product.product_id')
            ->join('collections', 'collection_product.collection_id', '=', 'collections.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.status', '!=', 'cancelled')
            ->select([
                'collections.id',
                'collections.name',
                'collections.slug',
                DB::raw('SUM(order_items.quantity) as units_sold'),
                DB::raw('SUM(order_items.quantity * order_items.unit_price) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
            ])
            ->groupBy('collections.id', 'collections.name', 'collections.slug')
            ->orderByDesc('revenue')
            ->limit($limit)
            ->get();
    }

    /**
     * Get recent orders
     */
    public function getRecentOrders(int $limit = 10): Collection
    {
        return Order::query()
            ->with(['customer', 'items.product'])
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get()
            ->map(function ($order) {
                return [
                    'id' => $order->id,
                    'number' => $order->number,
                    'customer_name' => $order->customer?->name ?? 'Guest',
                    'total' => $order->total,
                    'status' => $order->status,
                    'created_at' => $order->created_at,
                    'items_count' => $order->items->count(),
                ];
            });
    }

    /**
     * Get conversion funnel data
     */
    public function getConversionFunnel(array $dateRange): array
    {
        // This would integrate with actual analytics data
        // For now, returning sample structure
        return [
            'sessions' => 10000,
            'product_views' => 5000,
            'add_to_cart' => 1000,
            'checkout_started' => 500,
            'purchases' => 250,
            'conversion_rates' => [
                'session_to_view' => 50.0,
                'view_to_cart' => 20.0,
                'cart_to_checkout' => 50.0,
                'checkout_to_purchase' => 50.0,
                'overall' => 2.5,
            ],
        ];
    }

    /**
     * Get sales trends over time
     */
    public function getSalesTrends(array $dateRange): Collection
    {
        $start = $dateRange['start'];
        $end = $dateRange['end'];
        $days = $start->diffInDays($end) + 1;

        // Determine grouping based on date range
        $groupBy = $days <= 31 ? 'DATE(created_at)' : 'YEAR(created_at), MONTH(created_at)';
        $selectFormat = $days <= 31 ? 'DATE(created_at) as date' : 'YEAR(created_at) as year, MONTH(created_at) as month';

        return Order::query()
            ->whereBetween('created_at', [$start, $end])
            ->where('status', '!=', 'cancelled')
            ->selectRaw("
                $selectFormat,
                COUNT(*) as orders_count,
                SUM(total) as revenue,
                AVG(total) as average_order_value
            ")
            ->groupByRaw($groupBy)
            ->orderByRaw($groupBy)
            ->get();
    }

    /**
     * Get sales by channel
     */
    public function getSalesByChannel(array $dateRange): Collection
    {
        return Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', '!=', 'cancelled')
            ->select([
                DB::raw('COALESCE(channel, "Online Store") as channel'),
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('AVG(total) as average_order_value'),
            ])
            ->groupBy('channel')
            ->orderByDesc('revenue')
            ->get();
    }

    /**
     * Get live data for real-time view
     */
    public function getLiveData(): array
    {
        $now = Carbon::now();
        $lastHour = $now->copy()->subHour();

        return [
            'current_visitors' => rand(15, 150), // Would integrate with real analytics
            'page_views_last_hour' => rand(100, 1000),
            'orders_last_hour' => Order::whereBetween('created_at', [$lastHour, $now])->count(),
            'revenue_last_hour' => Order::whereBetween('created_at', [$lastHour, $now])
                ->where('status', '!=', 'cancelled')
                ->sum('total'),
            'top_pages' => [
                ['path' => '/', 'views' => rand(50, 200)],
                ['path' => '/products', 'views' => rand(30, 150)],
                ['path' => '/collections/featured', 'views' => rand(20, 100)],
            ],
            'recent_orders' => $this->getRecentOrders(5),
        ];
    }

    /**
     * Calculate metrics for a specific period
     */
    protected function calculatePeriodMetrics(array $dateRange): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', '!=', 'cancelled');

        $totalSales = $orders->sum('total');
        $ordersCount = $orders->count();
        $averageOrderValue = $ordersCount > 0 ? $totalSales / $ordersCount : 0;

        // Mock data for metrics that require external analytics
        $sessions = rand(1000, 10000);
        $conversionRate = $sessions > 0 ? ($ordersCount / $sessions) * 100 : 0;

        $returningCustomers = Customer::query()
            ->whereHas('orders', function (Builder $query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', '!=', 'cancelled');
            })
            ->whereHas('orders', function (Builder $query) use ($dateRange) {
                $query->where('created_at', '<', $dateRange['start']);
            })
            ->count();

        $totalCustomersWithOrders = Customer::query()
            ->whereHas('orders', function (Builder $query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('status', '!=', 'cancelled');
            })
            ->count();

        $returningCustomersRate = $totalCustomersWithOrders > 0
            ? ($returningCustomers / $totalCustomersWithOrders) * 100
            : 0;

        return [
            'total_sales' => $totalSales,
            'orders' => $ordersCount,
            'average_order_value' => $averageOrderValue,
            'conversion_rate' => $conversionRate,
            'returning_customers' => $returningCustomersRate,
            'sessions' => $sessions,
        ];
    }

    /**
     * Calculate sales metrics for a specific period
     */
    protected function calculateSalesMetrics(array $dateRange): array
    {
        $orders = Order::query()
            ->whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
            ->where('status', '!=', 'cancelled');

        return [
            'gross_sales' => $orders->sum('subtotal'),
            'net_sales' => $orders->sum('total'),
            'tax_collected' => $orders->sum('tax_amount'),
            'shipping_collected' => $orders->sum('shipping_amount'),
            'refunds' => Order::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])
                ->where('status', 'refunded')
                ->sum('total'),
        ];
    }

    /**
     * Calculate customer metrics for a specific period
     */
    protected function calculateCustomerMetrics(array $dateRange): array
    {
        $newCustomers = Customer::whereBetween('created_at', [$dateRange['start'], $dateRange['end']])->count();

        $returningCustomers = Customer::query()
            ->whereHas('orders', function (Builder $query) use ($dateRange) {
                $query->whereBetween('created_at', [$dateRange['start'], $dateRange['end']]);
            })
            ->whereHas('orders', function (Builder $query) use ($dateRange) {
                $query->where('created_at', '<', $dateRange['start']);
            })
            ->count();

        $avgLifetimeValue = Customer::query()
            ->withSum('orders', 'total')
            ->whereHas('orders')
            ->avg('orders_sum_total') ?? 0;

        $customersWithMultipleOrders = Customer::query()
            ->withCount('orders')
            ->having('orders_count', '>', 1)
            ->count();

        $totalCustomers = Customer::count();
        $repeatPurchaseRate = $totalCustomers > 0 ? ($customersWithMultipleOrders / $totalCustomers) * 100 : 0;

        return [
            'new_customers' => $newCustomers,
            'returning_customers' => $returningCustomers,
            'customer_lifetime_value' => $avgLifetimeValue,
            'repeat_purchase_rate' => $repeatPurchaseRate,
        ];
    }

    /**
     * Calculate percentage change between two values
     */
    protected function calculatePercentageChange(float $current, float $previous): float
    {
        if ($previous == 0) {
            return $current > 0 ? 100 : 0;
        }

        return (($current - $previous) / $previous) * 100;
    }

    /**
     * Get cohort analysis data
     */
    public function getCohortAnalysis(array $dateRange): array
    {
        // Simplified cohort analysis
        // In a real implementation, this would be more complex
        return [
            'cohorts' => [
                ['month' => '2024-01', 'customers' => 100, 'retention' => [100, 45, 32, 28, 25]],
                ['month' => '2024-02', 'customers' => 120, 'retention' => [100, 48, 35, 30]],
                ['month' => '2024-03', 'customers' => 150, 'retention' => [100, 52, 38]],
                ['month' => '2024-04', 'customers' => 180, 'retention' => [100, 55]],
                ['month' => '2024-05', 'customers' => 200, 'retention' => [100]],
            ],
        ];
    }

    /**
     * Get customer segments
     */
    public function getCustomerSegments(array $dateRange): Collection
    {
        return collect([
            ['name' => 'VIP Customers', 'count' => 150, 'revenue' => 45000, 'avg_order_value' => 300],
            ['name' => 'Regular Customers', 'count' => 800, 'revenue' => 120000, 'avg_order_value' => 150],
            ['name' => 'New Customers', 'count' => 300, 'revenue' => 30000, 'avg_order_value' => 100],
            ['name' => 'At-Risk Customers', 'count' => 200, 'revenue' => 15000, 'avg_order_value' => 75],
        ]);
    }

    /**
     * Get acquisition channels
     */
    public function getAcquisitionChannels(array $dateRange): Collection
    {
        return collect([
            ['channel' => 'Organic Search', 'customers' => 450, 'cost' => 0, 'cac' => 0],
            ['channel' => 'Social Media', 'customers' => 300, 'cost' => 5000, 'cac' => 16.67],
            ['channel' => 'Email Marketing', 'customers' => 200, 'cost' => 1000, 'cac' => 5],
            ['channel' => 'Paid Search', 'customers' => 150, 'cost' => 3000, 'cac' => 20],
            ['channel' => 'Direct', 'customers' => 350, 'cost' => 0, 'cac' => 0],
        ]);
    }

    /**
     * Get retention rates
     */
    public function getRetentionRates(array $dateRange): array
    {
        return [
            '1_month' => 45.2,
            '3_months' => 32.1,
            '6_months' => 28.5,
            '12_months' => 25.8,
        ];
    }

    /**
     * Get customer lifetime value analysis
     */
    public function getCustomerLifetimeValue(array $dateRange): array
    {
        return [
            'average_clv' => 250.50,
            'clv_by_segment' => [
                'VIP' => 850.00,
                'Regular' => 350.00,
                'New' => 125.00,
            ],
            'clv_trend' => [
                ['month' => '2024-01', 'clv' => 220.00],
                ['month' => '2024-02', 'clv' => 235.00],
                ['month' => '2024-03', 'clv' => 245.00],
                ['month' => '2024-04', 'clv' => 250.50],
            ],
        ];
    }

    /**
     * Get detailed cohort analysis
     */
    public function getDetailedCohortAnalysis(array $dateRange): array
    {
        // This would involve complex SQL queries in a real implementation
        return [
            'cohort_table' => [
                ['cohort' => '2024-01', 'size' => 100, 'month_0' => 100, 'month_1' => 45, 'month_2' => 32, 'month_3' => 28],
                ['cohort' => '2024-02', 'size' => 120, 'month_0' => 100, 'month_1' => 48, 'month_2' => 35, 'month_3' => null],
                ['cohort' => '2024-03', 'size' => 150, 'month_0' => 100, 'month_1' => 52, 'month_2' => null, 'month_3' => null],
            ],
            'average_retention' => [
                'month_1' => 48.3,
                'month_2' => 33.5,
                'month_3' => 28.0,
            ],
        ];
    }

    /**
     * Get sales funnel data
     */
    public function getSalesFunnel(array $dateRange): array
    {
        return [
            'steps' => [
                ['name' => 'Sessions', 'count' => 10000, 'rate' => 100],
                ['name' => 'Product Views', 'count' => 5000, 'rate' => 50],
                ['name' => 'Add to Cart', 'count' => 1000, 'rate' => 20],
                ['name' => 'Checkout Started', 'count' => 500, 'rate' => 50],
                ['name' => 'Orders', 'count' => 250, 'rate' => 50],
            ],
            'conversion_points' => [
                'session_to_view' => 50.0,
                'view_to_cart' => 20.0,
                'cart_to_checkout' => 50.0,
                'checkout_to_order' => 50.0,
            ],
        ];
    }

    /**
     * Get product sales analytics
     */
    public function getProductSalesAnalytics(array $dateRange): Collection
    {
        return Product::query()
            ->leftJoin('order_items', 'products.id', '=', 'order_items.product_id')
            ->leftJoin('orders', function ($join) use ($dateRange) {
                $join->on('order_items.order_id', '=', 'orders.id')
                    ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
                    ->where('orders.status', '!=', 'cancelled');
            })
            ->select([
                'products.id',
                'products.name',
                'products.sku',
                DB::raw('COALESCE(SUM(order_items.quantity), 0) as units_sold'),
                DB::raw('COALESCE(SUM(order_items.quantity * order_items.unit_price), 0) as revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as orders_count'),
                DB::raw('COALESCE(AVG(order_items.unit_price), 0) as avg_price'),
            ])
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('revenue')
            ->get();
    }

    /**
     * Get sales by location
     */
    public function getSalesByLocation(array $dateRange): Collection
    {
        return Order::query()
            ->join('addresses', 'orders.shipping_address_id', '=', 'addresses.id')
            ->whereBetween('orders.created_at', [$dateRange['start'], $dateRange['end']])
            ->where('orders.status', '!=', 'cancelled')
            ->select([
                'addresses.country',
                'addresses.state',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(orders.total) as revenue'),
            ])
            ->groupBy('addresses.country', 'addresses.state')
            ->orderByDesc('revenue')
            ->get();
    }
}
