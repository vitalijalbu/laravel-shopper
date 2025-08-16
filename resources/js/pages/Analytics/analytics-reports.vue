<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Card from '../../Components/ui/Card.vue'
import CardHeader from '../../Components/ui/CardHeader.vue'
import CardTitle from '../../Components/ui/CardTitle.vue'
import CardContent from '../../Components/ui/CardContent.vue'
import { Line, Bar, Doughnut } from 'vue-chartjs'
import { formatCurrency, formatNumber, formatPercentage } from '../../utils/formatters'

interface ReportData {
  summary?: {
    revenue: {
      total: number
      chart_data: Array<{ date: string; value: number }>
    }
    orders: {
      total: number
      chart_data: Array<{ date: string; value: number }>
    }
    visitors: {
      total: number
      chart_data: Array<{ date: string; value: number }>
    }
  }
  trends?: any[]
  page_views?: number
  unique_visitors?: number
  bounce_rate?: number
  average_session_duration?: string
  total_sales?: number
  orders_count?: number
  average_order_value?: number
  top_selling_products?: Array<{
    id: number
    name: string
    sales: number
    revenue: number
  }>
  most_viewed?: Array<{
    id: number
    name: string
    views: number
  }>
  best_converting?: Array<{
    id: number
    name: string
    conversion_rate: number
  }>
  inventory_alerts?: Array<{
    id: number
    name: string
    stock: number
  }>
  new_customers?: number
  returning_customers?: number
  customer_lifetime_value?: number
  top_customers?: Array<{
    id: number
    name: string
    total_spent: number
    orders_count: number
  }>
}

interface Props {
  report_type: string
  period: string
  data: ReportData
}

const props = defineProps<Props>()

const selectedReportType = ref(props.report_type)
const selectedPeriod = ref(props.period)
const isLoading = ref(false)

// Report type options
const reportTypes = [
  { value: 'overview', label: 'Overview', description: 'General performance metrics' },
  { value: 'traffic', label: 'Traffic', description: 'Website traffic analysis' },
  { value: 'sales', label: 'Sales', description: 'Sales performance and revenue' },
  { value: 'products', label: 'Products', description: 'Product performance insights' },
  { value: 'customers', label: 'Customers', description: 'Customer behavior and metrics' },
]

// Period options
const periodOptions = [
  { value: '7', label: 'Last 7 days' },
  { value: '30', label: 'Last 30 days' },
  { value: '90', label: 'Last 90 days' },
  { value: '365', label: 'Last year' },
]

// Chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      position: 'top' as const,
    },
  },
  scales: {
    y: {
      beginAtZero: true,
    },
  },
}

// Revenue chart data (for overview report)
const revenueChartData = computed(() => {
  if (!props.data.summary?.revenue?.chart_data) return null
  
  return {
    labels: props.data.summary.revenue.chart_data.map(d => new Date(d.date).toLocaleDateString()),
    datasets: [
      {
        label: 'Revenue',
        data: props.data.summary.revenue.chart_data.map(d => d.value),
        borderColor: 'rgb(59, 130, 246)',
        backgroundColor: 'rgba(59, 130, 246, 0.1)',
        tension: 0.3,
        fill: true,
      },
    ],
  }
})

// Orders chart data (for overview report)
const ordersChartData = computed(() => {
  if (!props.data.summary?.orders?.chart_data) return null
  
  return {
    labels: props.data.summary.orders.chart_data.map(d => new Date(d.date).toLocaleDateString()),
    datasets: [
      {
        label: 'Orders',
        data: props.data.summary.orders.chart_data.map(d => d.value),
        backgroundColor: 'rgba(34, 197, 94, 0.8)',
        borderColor: 'rgb(34, 197, 94)',
        borderWidth: 1,
      },
    ],
  }
})

// Traffic sources chart data (for traffic report)
const trafficSourcesData = computed(() => {
  // Mock data for traffic sources
  return {
    labels: ['Direct', 'Search', 'Social', 'Referral', 'Email'],
    datasets: [
      {
        data: [45, 30, 15, 7, 3],
        backgroundColor: [
          '#3B82F6',
          '#10B981',
          '#8B5CF6',
          '#F59E0B',
          '#EF4444',
        ],
        borderWidth: 0,
      },
    ],
  }
})

// Handle report type change
const handleReportTypeChange = () => {
  if (selectedReportType.value === props.report_type) return
  
  isLoading.value = true
  router.get(`/admin/analytics/reports?type=${selectedReportType.value}&period=${selectedPeriod.value}`)
}

// Handle period change
const handlePeriodChange = () => {
  if (selectedPeriod.value === props.period) return
  
  isLoading.value = true
  router.get(`/admin/analytics/reports?type=${selectedReportType.value}&period=${selectedPeriod.value}`)
}

// Get current report type info
const currentReportType = computed(() => {
  return reportTypes.find(type => type.value === props.report_type) || reportTypes[0]
})

// Export report
const exportReport = () => {
  const params = new URLSearchParams({
    type: props.report_type,
    period: props.period,
    export: 'true',
  })
  
  window.open(`/admin/analytics/reports?${params.toString()}`)
}

// Watch for route changes to update loading state
watch(() => [props.report_type, props.period], () => {
  isLoading.value = false
})
</script>

<template>
  <Head :title="`${currentReportType.label} Report`" />
  
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">{{ currentReportType.label }} Report</h1>
          <p class="text-gray-600 mt-1">{{ currentReportType.description }}</p>
        </div>
        
        <div class="flex items-center gap-4">
          <select
            v-model="selectedPeriod"
            @change="handlePeriodChange"
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option
              v-for="option in periodOptions"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>
          
          <button
            @click="exportReport"
            class="btn btn-outline flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
            </svg>
            Export
          </button>
        </div>
      </div>

      <!-- Report Type Tabs -->
      <Card>
        <CardContent class="p-4">
          <div class="flex flex-wrap gap-2">
            <button
              v-for="reportType in reportTypes"
              :key="reportType.value"
              @click="selectedReportType = reportType.value; handleReportTypeChange()"
              :class="[
                'px-4 py-2 rounded-lg text-sm font-medium transition-colors',
                reportType.value === props.report_type
                  ? 'bg-blue-100 text-blue-900 border-blue-200'
                  : 'bg-gray-100 text-gray-700 hover:bg-gray-200'
              ]"
            >
              {{ reportType.label }}
            </button>
          </div>
        </CardContent>
      </Card>

      <!-- Overview Report -->
      <template v-if="report_type === 'overview'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Revenue Chart -->
          <Card v-if="revenueChartData">
            <CardHeader>
              <CardTitle>Revenue Trend</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="h-64">
                <Line :data="revenueChartData" :options="chartOptions" />
              </div>
            </CardContent>
          </Card>

          <!-- Orders Chart -->
          <Card v-if="ordersChartData">
            <CardHeader>
              <CardTitle>Orders Trend</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="h-64">
                <Bar :data="ordersChartData" :options="chartOptions" />
              </div>
            </CardContent>
          </Card>
        </div>
      </template>

      <!-- Traffic Report -->
      <template v-if="report_type === 'traffic'">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(data.page_views || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Page Views</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(data.unique_visitors || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Unique Visitors</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatPercentage(data.bounce_rate || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Bounce Rate</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ data.average_session_duration || '0:00' }}
                </p>
                <p class="text-sm font-medium text-gray-600">Avg. Session</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Traffic Sources Chart -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <Card>
            <CardHeader>
              <CardTitle>Traffic Sources</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="h-64">
                <Doughnut :data="trafficSourcesData" :options="{ responsive: true, maintainAspectRatio: false }" />
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardHeader>
              <CardTitle>Traffic Insights</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-4">
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Direct Traffic</span>
                  <span class="font-medium">45%</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Search Engines</span>
                  <span class="font-medium">30%</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Social Media</span>
                  <span class="font-medium">15%</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Referrals</span>
                  <span class="font-medium">7%</span>
                </div>
                <div class="flex justify-between items-center">
                  <span class="text-gray-600">Email</span>
                  <span class="font-medium">3%</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </template>

      <!-- Sales Report -->
      <template v-if="report_type === 'sales'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatCurrency(data.total_sales || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Total Sales</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(data.orders_count || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Orders</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatCurrency(data.average_order_value || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Avg. Order Value</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Top Selling Products -->
        <Card v-if="data.top_selling_products?.length">
          <CardHeader>
            <CardTitle>Top Selling Products</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div
                v-for="product in data.top_selling_products"
                :key="product.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ product.name }}</p>
                  <p class="text-sm text-gray-600">{{ product.sales }} sales</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">{{ formatCurrency(product.revenue) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>

      <!-- Products Report -->
      <template v-if="report_type === 'products'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Most Viewed Products -->
          <Card v-if="data.most_viewed?.length">
            <CardHeader>
              <CardTitle>Most Viewed Products</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div
                  v-for="product in data.most_viewed"
                  :key="product.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <p class="font-medium text-gray-900">{{ product.name }}</p>
                  <span class="text-gray-600">{{ formatNumber(product.views) }} views</span>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Best Converting Products -->
          <Card v-if="data.best_converting?.length">
            <CardHeader>
              <CardTitle>Best Converting Products</CardTitle>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div
                  v-for="product in data.best_converting"
                  :key="product.id"
                  class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
                >
                  <p class="font-medium text-gray-900">{{ product.name }}</p>
                  <span class="text-green-600 font-medium">{{ formatPercentage(product.conversion_rate) }}</span>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Inventory Alerts -->
        <Card v-if="data.inventory_alerts?.length">
          <CardHeader>
            <CardTitle>Low Stock Alerts</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div
                v-for="product in data.inventory_alerts"
                :key="product.id"
                class="flex items-center justify-between p-3 bg-red-50 border border-red-200 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ product.name }}</p>
                  <p class="text-sm text-red-600">Low stock warning</p>
                </div>
                <span class="text-red-600 font-medium">{{ product.stock }} remaining</span>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>

      <!-- Customers Report -->
      <template v-if="report_type === 'customers'">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(data.new_customers || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">New Customers</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(data.returning_customers || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Returning Customers</p>
              </div>
            </CardContent>
          </Card>

          <Card>
            <CardContent class="p-6">
              <div class="text-center">
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatCurrency(data.customer_lifetime_value || 0) }}
                </p>
                <p class="text-sm font-medium text-gray-600">Avg. CLV</p>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Top Customers -->
        <Card v-if="data.top_customers?.length">
          <CardHeader>
            <CardTitle>Top Customers</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-3">
              <div
                v-for="customer in data.top_customers"
                :key="customer.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ customer.name }}</p>
                  <p class="text-sm text-gray-600">{{ customer.orders_count }} orders</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">{{ formatCurrency(customer.total_spent) }}</p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>

      <!-- Loading Overlay -->
      <div
        v-if="isLoading"
        class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center space-x-3">
            <div class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"></div>
            <span>Loading report...</span>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
