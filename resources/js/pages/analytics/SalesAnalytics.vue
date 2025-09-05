<template>
  <cp-layout>
    <PageLayout
      title="Sales analytics"
      subtitle="Monitor sales performance and trends"
      :breadcrumbs="page.breadcrumbs"
    >
      <!-- Date Range Selector -->
      <template #actions>
        <div class="flex items-center space-x-3">
          <DateRangeSelector
            v-model:start-date="dateRange.start"
            v-model:end-date="dateRange.end"
            @change="loadData"
          />
          <button
            @click="exportData"
            class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <Icon name="download" class="w-4 h-4 mr-2" />
            Export
          </button>
        </div>
      </template>

      <div class="space-y-6">
        <!-- Sales Overview Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="Total Sales"
            :value="formatCurrency(salesMetrics.total_sales)"
            :change="salesMetrics.sales_change"
            icon="dollar-sign"
            color="green"
          />
          <MetricCard
            title="Orders"
            :value="salesMetrics.total_orders"
            :change="salesMetrics.orders_change"
            icon="shopping-bag"
            color="blue"
          />
          <MetricCard
            title="Average Order Value"
            :value="formatCurrency(salesMetrics.average_order_value)"
            :change="salesMetrics.aov_change"
            icon="trending-up"
            color="purple"
          />
          <MetricCard
            title="Conversion Rate"
            :value="salesMetrics.conversion_rate + '%'"
            :change="salesMetrics.conversion_change"
            icon="target"
            color="orange"
          />
        </div>

        <!-- Sales Chart -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Sales over time</h3>
              <div class="flex space-x-2">
                <button
                  v-for="period in chartPeriods"
                  :key="period.value"
                  @click="selectedChartPeriod = period.value"
                  :class="[
                    'px-3 py-1 text-sm rounded-md',
                    selectedChartPeriod === period.value
                      ? 'bg-blue-100 text-blue-700'
                      : 'text-gray-500 hover:text-gray-700'
                  ]"
                >
                  {{ period.label }}
                </button>
              </div>
            </div>
          </div>
          <div class="p-6">
            <LineChart
              :data="salesChartData"
              :height="300"
              :loading="chartLoading"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Products -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Top products by sales</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div 
                  v-for="(product, index) in topProducts" 
                  :key="product.id"
                  class="flex items-center space-x-4"
                >
                  <div class="flex-shrink-0 w-8 text-center">
                    <span class="text-sm font-medium text-gray-900">{{ index + 1 }}</span>
                  </div>
                  <div class="flex-shrink-0">
                    <img 
                      :src="product.image" 
                      :alt="product.name"
                      class="w-10 h-10 rounded-lg object-cover"
                    />
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ product.name }}</p>
                    <p class="text-sm text-gray-500">{{ product.orders }} orders</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">${{ formatCurrency(product.revenue) }}</p>
                    <p class="text-sm text-gray-500">{{ product.quantity }} sold</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Sales by Channel -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Sales by channel</h3>
            </div>
            <div class="p-6">
              <DonutChart
                :data="salesByChannelData"
                :height="200"
                :loading="false"
              />
              <div class="mt-4 space-y-2">
                <div 
                  v-for="channel in salesByChannel" 
                  :key="channel.name"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center space-x-2">
                    <div 
                      class="w-3 h-3 rounded-full"
                      :style="{ backgroundColor: channel.color }"
                    ></div>
                    <span class="text-sm text-gray-700">{{ channel.name }}</span>
                  </div>
                  <div class="text-right">
                    <span class="text-sm font-medium text-gray-900">${{ formatCurrency(channel.revenue) }}</span>
                    <span class="text-sm text-gray-500 ml-2">({{ channel.percentage }}%)</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Sales Funnel -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Sales funnel</h3>
          </div>
          <div class="p-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
              <div 
                v-for="(step, index) in salesFunnel" 
                :key="step.name"
                class="text-center"
              >
                <div class="relative">
                  <div 
                    class="mx-auto rounded-lg border-2 border-dashed border-gray-300 p-6"
                    :class="step.color"
                  >
                    <Icon :name="step.icon" class="w-8 h-8 mx-auto mb-2" />
                    <p class="text-2xl font-bold text-gray-900">{{ step.value.toLocaleString() }}</p>
                    <p class="text-sm text-gray-600">{{ step.name }}</p>
                  </div>
                  <div 
                    v-if="index < salesFunnel.length - 1"
                    class="absolute top-1/2 -right-2 transform -translate-y-1/2 text-gray-400"
                  >
                    <Icon name="chevron-right" class="w-4 h-4" />
                  </div>
                </div>
                <div class="mt-2">
                  <p class="text-sm text-gray-600">{{ step.conversion_rate }}% conversion</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Recent Orders Table -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Recent orders</h3>
              <router-link 
                to="/cp/orders"
                class="text-sm text-blue-600 hover:text-blue-500"
              >
                View all orders
              </router-link>
            </div>
          </div>
          <div class="overflow-hidden">
            <DataTable
              :columns="orderColumns"
              :data="recentOrders"
              :loading="false"
              :pagination="false"
            />
          </div>
        </div>
      </div>
    </PageLayout>
  </cp-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import CpLayout from '../../components/cp-layout.vue'
import PageLayout from '../../components/Admin/Layout/PageLayout.vue'
import MetricCard from '../../components/Admin/Analytics/MetricCard.vue'
import LineChart from '../../components/Admin/Charts/LineChart.vue'
import DonutChart from '../../components/Admin/Charts/DonutChart.vue'
import DataTable from '../../components/Admin/DataTable/DataTable.vue'
import DateRangeSelector from '../../components/Admin/DateRangeSelector.vue'

const props = defineProps({
  page: Object,
  salesMetrics: Object,
  topProducts: Array,
  salesByChannel: Array,
  salesFunnel: Array,
  recentOrders: Array,
})

// State
const dateRange = ref({
  start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000), // 30 days ago
  end: new Date(),
})

const selectedChartPeriod = ref('daily')
const chartLoading = ref(false)
const salesChartData = ref({})

// Chart periods
const chartPeriods = [
  { value: 'hourly', label: 'Hourly' },
  { value: 'daily', label: 'Daily' },
  { value: 'weekly', label: 'Weekly' },
  { value: 'monthly', label: 'Monthly' },
]

// Order table columns
const orderColumns = [
  { key: 'number', label: 'Order', sortable: true },
  { key: 'customer_name', label: 'Customer', sortable: true },
  { key: 'status', label: 'Status', sortable: true },
  { key: 'total', label: 'Total', sortable: true, format: 'currency' },
  { key: 'created_at', label: 'Date', sortable: true, format: 'date' },
]

// Computed
const salesByChannelData = computed(() => {
  return {
    labels: props.salesByChannel.map(channel => channel.name),
    datasets: [{
      data: props.salesByChannel.map(channel => channel.revenue),
      backgroundColor: props.salesByChannel.map(channel => channel.color),
    }]
  }
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const loadData = async () => {
  await Promise.all([
    loadSalesChart(),
    loadSalesMetrics(),
  ])
}

const loadSalesChart = async () => {
  chartLoading.value = true
  try {
    const response = await fetch('/cp/analytics/data/sales-chart', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        start_date: dateRange.value.start.toISOString().split('T')[0],
        end_date: dateRange.value.end.toISOString().split('T')[0],
        period: selectedChartPeriod.value,
      }),
    })
    
    const data = await response.json()
    salesChartData.value = data
  } catch (error) {
    console.error('Failed to load sales chart:', error)
  } finally {
    chartLoading.value = false
  }
}

const loadSalesMetrics = async () => {
  try {
    const response = await fetch('/cp/analytics/data/sales-metrics', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
      },
      body: JSON.stringify({
        start_date: dateRange.value.start.toISOString().split('T')[0],
        end_date: dateRange.value.end.toISOString().split('T')[0],
      }),
    })
    
    const data = await response.json()
    Object.assign(props.salesMetrics, data)
  } catch (error) {
    console.error('Failed to load sales metrics:', error)
  }
}

const exportData = () => {
  window.open(`/cp/analytics/sales/export?start_date=${dateRange.value.start.toISOString().split('T')[0]}&end_date=${dateRange.value.end.toISOString().split('T')[0]}`)
}

// Watchers
watch(selectedChartPeriod, () => {
  loadSalesChart()
})

// Lifecycle
onMounted(() => {
  loadSalesChart()
})
</script>
