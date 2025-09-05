<template>
  <cp-layout>
    <PageLayout
      title="Sales analytics"
      subtitle="Monitor your store's sales performance"
      :breadcrumbs="breadcrumbs"
    >
      <!-- Date Range Selector -->
      <template #actions>
        <div class="flex items-center space-x-3">
          <select v-model="selectedPeriod" class="form-select" @change="updateDateRange">
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_7_days">Last 7 days</option>
            <option value="last_30_days">Last 30 days</option>
            <option value="last_90_days">Last 3 months</option>
            <option value="this_month">This month</option>
            <option value="last_month">Last month</option>
            <option value="this_year">This year</option>
          </select>
        </div>
      </template>

      <div class="space-y-6">
        <!-- Key Sales Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="Total sales"
            :value="metrics.total_sales"
            :previous="metrics.previous_sales"
            :change="metrics.sales_change"
            format="currency"
          />
          <MetricCard
            title="Average order value"
            :value="metrics.avg_order_value"
            :previous="metrics.previous_aov"
            :change="metrics.aov_change"
            format="currency"
          />
          <MetricCard
            title="Orders"
            :value="metrics.total_orders"
            :previous="metrics.previous_orders"
            :change="metrics.orders_change"
            format="number"
          />
          <MetricCard
            title="Conversion rate"
            :value="metrics.conversion_rate"
            :previous="metrics.previous_conversion"
            :change="metrics.conversion_change"
            format="percentage"
          />
        </div>

        <!-- Sales Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Sales Trend -->
          <ChartCard title="Sales trend">
            <LineChart
              :data="salesTrendData"
              :options="lineChartOptions"
              height="350"
            />
          </ChartCard>

          <!-- Revenue by Channel -->
          <ChartCard title="Revenue by channel">
            <DoughnutChart
              :data="revenueByChannelData"
              height="350"
            />
          </ChartCard>
        </div>

        <!-- Sales Performance -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Products by Revenue -->
          <ChartCard title="Top products by revenue">
            <BarChart
              :data="topProductsData"
              :options="barChartOptions"
              height="350"
            />
          </ChartCard>

          <!-- Sales by Time of Day -->
          <ChartCard title="Sales by time of day">
            <LineChart
              :data="salesByTimeData"
              :options="timeChartOptions"
              height="350"
            />
          </ChartCard>
        </div>

        <!-- Detailed Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Products Table -->
          <DataCard title="Top selling products">
            <DataTable
              :columns="productColumns"
              :data="topProducts"
              :loading="loading"
            />
          </DataCard>

          <!-- Recent Orders -->
          <DataCard title="Recent high-value orders">
            <DataTable
              :columns="orderColumns"
              :data="recentOrders"
              :loading="loading"
            />
          </DataCard>
        </div>
      </div>
    </PageLayout>
  </cp-layout>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
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
const topProducts = ref([])
const recentOrders = ref([])

const breadcrumbs = [
  { name: 'Analytics', href: '/cp/analytics' },
  { name: 'Sales' }
]

// Chart data
const salesTrendData = ref({
  labels: [],
  datasets: [{
    label: 'Sales',
    data: [],
    borderColor: 'rgb(59, 130, 246)',
    backgroundColor: 'rgba(59, 130, 246, 0.1)',
    tension: 0.4
  }]
})

const revenueByChannelData = ref({
  labels: ['Online Store', 'Point of Sale', 'Mobile App', 'Social Media'],
  datasets: [{
    data: [],
    backgroundColor: [
      'rgb(59, 130, 246)',
      'rgb(16, 185, 129)',
      'rgb(245, 158, 11)',
      'rgb(239, 68, 68)'
    ]
  }]
})

const topProductsData = ref({
  labels: [],
  datasets: [{
    label: 'Revenue',
    data: [],
    backgroundColor: 'rgba(59, 130, 246, 0.8)',
    borderColor: 'rgb(59, 130, 246)',
    borderWidth: 1
  }]
})

const salesByTimeData = ref({
  labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
  datasets: [{
    label: 'Orders',
    data: [],
    borderColor: 'rgb(16, 185, 129)',
    backgroundColor: 'rgba(16, 185, 129, 0.1)',
    tension: 0.4
  }]
})

// Chart options
const lineChartOptions = {
  scales: {
    y: {
      beginAtZero: true,
      ticks: {
        callback: function(value) {
          return '€' + new Intl.NumberFormat('it-IT').format(value)
        }
      }
    }
  }
}

const barChartOptions = {
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

const timeChartOptions = {
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

// Table columns
const productColumns = [
  { key: 'name', label: 'Product' },
  { key: 'orders', label: 'Orders' },
  { key: 'revenue', label: 'Revenue', format: 'currency' },
  { key: 'units_sold', label: 'Units sold' }
]

const orderColumns = [
  { key: 'order_number', label: 'Order' },
  { key: 'customer', label: 'Customer' },
  { key: 'total', label: 'Total', format: 'currency' },
  { key: 'date', label: 'Date', format: 'date' }
]

const updateDateRange = async () => {
  await fetchSalesData()
}

const fetchSalesData = async () => {
  loading.value = true
  try {
    const response = await fetch(`/cp/api/analytics/sales?period=${selectedPeriod.value}`)
    const data = await response.json()
    
    metrics.value = data.metrics
    topProducts.value = data.top_products
    recentOrders.value = data.recent_orders
    
    // Update chart data
    salesTrendData.value.labels = data.sales_trend.labels
    salesTrendData.value.datasets[0].data = data.sales_trend.data
    
    revenueByChannelData.value.datasets[0].data = data.revenue_by_channel.data
    
    topProductsData.value.labels = data.top_products_chart.labels
    topProductsData.value.datasets[0].data = data.top_products_chart.data
    
    salesByTimeData.value.datasets[0].data = data.sales_by_time.data
  } catch (error) {
    console.error('Error fetching sales data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchSalesData()
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
