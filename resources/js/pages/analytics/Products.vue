<template>
  <cp-layout>
    <PageLayout
      title="Product analytics"
      subtitle="Analyze product performance and trends"
      :breadcrumbs="breadcrumbs"
    >
      <!-- Date Range Selector -->
      <template #actions>
        <div class="flex items-center space-x-3">
          <select v-model="selectedPeriod" class="form-select" @change="updateDateRange">
            <option value="last_7_days">Last 7 days</option>
            <option value="last_30_days">Last 30 days</option>
            <option value="last_90_days">Last 3 months</option>
            <option value="this_year">This year</option>
          </select>
        </div>
      </template>

      <div class="space-y-6">
        <!-- Product Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="Products sold"
            :value="metrics.products_sold"
            :previous="metrics.previous_products_sold"
            :change="metrics.products_sold_change"
            format="number"
          />
          <MetricCard
            title="Units sold"
            :value="metrics.units_sold"
            :previous="metrics.previous_units_sold"
            :change="metrics.units_sold_change"
            format="number"
          />
          <MetricCard
            title="Avg. units per order"
            :value="metrics.avg_units_per_order"
            :previous="metrics.previous_avg_units"
            :change="metrics.avg_units_change"
            format="decimal"
          />
          <MetricCard
            title="Inventory value"
            :value="metrics.inventory_value"
            :previous="metrics.previous_inventory_value"
            :change="metrics.inventory_value_change"
            format="currency"
          />
        </div>

        <!-- Product Performance Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Products by Revenue -->
          <ChartCard title="Top products by revenue">
            <BarChart
              :data="topProductsRevenueData"
              :options="horizontalBarOptions"
              height="350"
            />
          </ChartCard>

          <!-- Product Categories Performance -->
          <ChartCard title="Revenue by category">
            <DoughnutChart
              :data="categoriesRevenueData"
              height="350"
            />
          </ChartCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Product Sales Trend -->
          <ChartCard title="Product sales trend">
            <LineChart
              :data="productSalesTrendData"
              :options="lineChartOptions"
              height="350"
            />
          </ChartCard>

          <!-- Inventory Status -->
          <ChartCard title="Inventory status">
            <DoughnutChart
              :data="inventoryStatusData"
              height="350"
            />
          </ChartCard>
        </div>

        <!-- Product Data Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Best Selling Products -->
          <DataCard title="Best selling products">
            <DataTable
              :columns="productColumns"
              :data="bestSellingProducts"
              :loading="loading"
            />
          </DataCard>

          <!-- Low Stock Alert -->
          <DataCard title="Low stock products">
            <DataTable
              :columns="stockColumns"
              :data="lowStockProducts"
              :loading="loading"
            />
          </DataCard>
        </div>

        <!-- Product Insights -->
        <div class="grid grid-cols-1 gap-6">
          <DataCard title="Product performance insights">
            <DataTable
              :columns="insightColumns"
              :data="productInsights"
              :loading="loading"
            />
          </DataCard>
        </div>
      </div>
    </PageLayout>
  </cp-layout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import CpLayout from '../../components/cp-layout.vue'
import PageLayout from '../../components/Admin/Layout/PageLayout.vue'
import MetricCard from '../../components/Analytics/MetricCard.vue'
import ChartCard from '../../components/Analytics/ChartCard.vue'
import DataCard from '../../components/Analytics/DataCard.vue'
import LineChart from '../../components/Charts/LineChart.vue'
import BarChart from '../../components/Charts/BarChart.vue'
import DoughnutChart from '../../components/Charts/DoughnutChart.vue'
import DataTable from '../../components/DataTable.vue'

const selectedPeriod = ref('last_30_days')
const loading = ref(false)
const metrics = ref({})
const bestSellingProducts = ref([])
const lowStockProducts = ref([])
const productInsights = ref([])

const breadcrumbs = [
  { name: 'Analytics', href: '/cp/analytics' },
  { name: 'Products' }
]

// Chart data
const topProductsRevenueData = ref({
  labels: [],
  datasets: [{
    label: 'Revenue',
    data: [],
    backgroundColor: 'rgba(59, 130, 246, 0.8)',
    borderColor: 'rgb(59, 130, 246)',
    borderWidth: 1
  }]
})

const categoriesRevenueData = ref({
  labels: [],
  datasets: [{
    data: [],
    backgroundColor: [
      'rgb(59, 130, 246)',
      'rgb(16, 185, 129)',
      'rgb(245, 158, 11)',
      'rgb(239, 68, 68)',
      'rgb(139, 69, 19)',
      'rgb(168, 85, 247)'
    ]
  }]
})

const productSalesTrendData = ref({
  labels: [],
  datasets: [{
    label: 'Units sold',
    data: [],
    borderColor: 'rgb(16, 185, 129)',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4
  }]
})

const inventoryStatusData = ref({
  labels: ['In stock', 'Low stock', 'Out of stock'],
  datasets: [{
    data: [],
    backgroundColor: [
      'rgb(16, 185, 129)',
      'rgb(245, 158, 11)',
      'rgb(239, 68, 68)'
    ]
  }]
})

// Chart options
const horizontalBarOptions = {
  indexAxis: 'y',
  scales: {
    x: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return '€' + new Intl.NumberFormat('it-IT').format(value)
        }
      }
    }
  }
}

const lineChartOptions = {
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

// Table columns
const productColumns = [
  { key: 'name', label: 'Product' },
  { key: 'sku', label: 'SKU' },
  { key: 'units_sold', label: 'Units sold' },
  { key: 'revenue', label: 'Revenue', format: 'currency' },
  { key: 'avg_price', label: 'Avg. price', format: 'currency' }
]

const stockColumns = [
  { key: 'name', label: 'Product' },
  { key: 'sku', label: 'SKU' },
  { key: 'current_stock', label: 'Current stock' },
  { key: 'min_stock', label: 'Min. stock' },
  { key: 'status', label: 'Status' }
]

const insightColumns = [
  { key: 'product', label: 'Product' },
  { key: 'insight_type', label: 'Insight' },
  { key: 'metric', label: 'Metric' },
  { key: 'trend', label: 'Trend' },
  { key: 'action', label: 'Suggested action' }
]

const updateDateRange = async () => {
  await fetchProductData()
}

const fetchProductData = async () => {
  loading.value = true
  try {
    const response = await fetch(`/cp/api/analytics/products?period=${selectedPeriod.value}`)
    const data = await response.json()
    
    metrics.value = data.metrics
    bestSellingProducts.value = data.best_selling_products
    lowStockProducts.value = data.low_stock_products
    productInsights.value = data.insights
    
    // Update chart data
    topProductsRevenueData.value.labels = data.top_products_chart.labels
    topProductsRevenueData.value.datasets[0].data = data.top_products_chart.data
    
    categoriesRevenueData.value.labels = data.categories_chart.labels
    categoriesRevenueData.value.datasets[0].data = data.categories_chart.data
    
    productSalesTrendData.value.labels = data.sales_trend_chart.labels
    productSalesTrendData.value.datasets[0].data = data.sales_trend_chart.data
    
    inventoryStatusData.value.datasets[0].data = data.inventory_chart.data
  } catch (error) {
    console.error('Error fetching product data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchProductData()
})
</script>

<style scoped>
.form-select {
  display: block;
  width: 100%;
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
  font-size: 0.875rem;
}

.form-select:focus {
  outline: none;
  border-color: #6366f1;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}
</style>
