<template>
  <cp-layout>
    <PageLayout
      :title="page.title"
      :subtitle="page.subtitle"
      :breadcrumbs="page.breadcrumbs"
      :tabs="page.tabs"
      default-tab="overview"
      @tab-change="handleTabChange"
    >
      <!-- Date Range Selector -->
      <template #actions>
        <div class="flex items-center space-x-3">
          <select
            v-model="selectedPeriod"
            class="form-select"
            @change="updateDateRange"
          >
            <option value="today">Today</option>
            <option value="yesterday">Yesterday</option>
            <option value="last_7_days">Last 7 days</option>
            <option value="last_30_days">Last 30 days</option>
            <option value="last_90_days">Last 3 months</option>
            <option value="this_month">This month</option>
            <option value="last_month">Last month</option>
            <option value="this_year">This year</option>
            <option value="custom">Custom range</option>
          </select>

          <button
            @click="refreshData"
            class="btn btn-secondary"
            :disabled="loading"
          >
            <Icon name="refresh" class="w-4 h-4" />
            Refresh
          </button>
        </div>
      </template>

      <!-- Overview Tab -->
      <template #tab-overview>
        <div class="space-y-6">
          <!-- Key Metrics Cards -->
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
            <MetricCard
              v-for="(metric, key) in metrics"
              :key="key"
              :title="formatMetricTitle(key)"
              :value="metric.value"
              :previous="metric.previous"
              :change="metric.change"
              :format="metric.format"
            />
          </div>

          <!-- Charts Row -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Sales Over Time -->
            <ChartCard title="Sales over time">
              <LineChart
                :data="salesOverTimeData"
                :loading="chartsLoading"
                height="300"
              />
            </ChartCard>

            <!-- Orders by Status -->
            <ChartCard title="Orders by status">
              <DoughnutChart
                :data="ordersByStatusData"
                :loading="chartsLoading"
                height="300"
              />
            </ChartCard>
          </div>

          <!-- Additional Analytics -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Revenue by Channel -->
            <ChartCard title="Revenue by channel">
              <BarChart
                :data="revenueByChannelData"
                :loading="chartsLoading"
                height="300"
              />
            </ChartCard>

            <!-- Customer Acquisition -->
            <ChartCard title="Customer acquisition">
              <LineChart
                :data="customerAcquisitionData"
                :loading="chartsLoading"
                height="300"
              />
            </ChartCard>
          </div>

          <!-- Data Tables -->
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- Top Products -->
            <DataCard title="Top products">
              <DataTable
                :data="topProducts"
                :columns="[
                  { key: 'name', label: 'Product' },
                  { key: 'units_sold', label: 'Units sold', type: 'number' },
                  { key: 'revenue', label: 'Revenue', type: 'currency' },
                  { key: 'orders_count', label: 'Orders', type: 'number' },
                ]"
                :pagination="false"
                max-height="400px"
              />
            </DataCard>

            <!-- Top Collections -->
            <DataCard title="Top collections">
              <DataTable
                :data="topCollections"
                :columns="[
                  { key: 'name', label: 'Collection' },
                  { key: 'units_sold', label: 'Units sold', type: 'number' },
                  { key: 'revenue', label: 'Revenue', type: 'currency' },
                  { key: 'orders_count', label: 'Orders', type: 'number' },
                ]"
                :pagination="false"
                max-height="400px"
              />
            </DataCard>
          </div>

          <!-- Recent Orders -->
          <DataCard title="Recent orders">
            <DataTable
              :data="recentOrders"
              :columns="[
                { key: 'number', label: 'Order' },
                { key: 'customer_name', label: 'Customer' },
                { key: 'total', label: 'Total', type: 'currency' },
                { key: 'status', label: 'Status', type: 'badge' },
                { key: 'items_count', label: 'Items', type: 'number' },
                { key: 'created_at', label: 'Date', type: 'datetime' },
              ]"
              :pagination="false"
              max-height="400px"
            />
          </DataCard>
        </div>
      </template>

      <!-- Sales Tab -->
      <template #tab-sales>
        <SalesAnalytics :date-range="dateRange" />
      </template>

      <!-- Customers Tab -->
      <template #tab-customers>
        <CustomerAnalytics :date-range="dateRange" />
      </template>

      <!-- Products Tab -->
      <template #tab-products>
        <ProductAnalytics :date-range="dateRange" />
      </template>

      <!-- Marketing Tab -->
      <template #tab-marketing>
        <MarketingAnalytics :date-range="dateRange" />
      </template>

      <!-- Reports Tab -->
      <template #tab-reports>
        <ReportsAnalytics :date-range="dateRange" />
      </template>
    </PageLayout>
  </cp-layout>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { usePage } from '@inertiajs/vue3'
import CpLayout from '../../components/cp-layout.vue'
import PageLayout from '../../components/Admin/Layout/PageLayout.vue'
import MetricCard from '../../components/Analytics/MetricCard.vue'
import ChartCard from '../../components/Analytics/ChartCard.vue'
import DataCard from '../../components/Analytics/DataCard.vue'
import LineChart from '../../components/Charts/LineChart.vue'
import BarChart from '../../components/Charts/BarChart.vue'
import DoughnutChart from '../../components/Charts/DoughnutChart.vue'
import DataTable from '../../components/DataTable.vue'
import SalesAnalytics from './SalesAnalytics.vue'
import CustomerAnalytics from './CustomerAnalytics.vue'
import ProductAnalytics from './ProductAnalytics.vue'
import MarketingAnalytics from './MarketingAnalytics.vue'
import ReportsAnalytics from './ReportsAnalytics.vue'

const props = defineProps({
  page: Object,
  metrics: Object,
  topProducts: Array,
  topCollections: Array,
  recentOrders: Array,
  conversionFunnel: Object,
  dateRange: Object,
  charts: Object,
})

const page = usePage()

// State
const selectedPeriod = ref('last_30_days')
const loading = ref(false)
const chartsLoading = ref(false)
const activeTab = ref('overview')

// Chart data
const salesOverTimeData = ref([])
const ordersByStatusData = ref([])
const revenueByChannelData = ref([])

// Computed
const dateRange = computed(() => ({
  start: props.dateRange?.start,
  end: props.dateRange?.end,
  label: props.dateRange?.label,
}))

// Methods
const formatMetricTitle = (key) => {
  return key.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase())
}

const updateDateRange = () => {
  loading.value = true
  
  router.get(route('cp.analytics.index'), {
    period: selectedPeriod.value,
  }, {
    preserveState: true,
    onFinish: () => {
      loading.value = false
      loadChartData()
    },
  })
}

const refreshData = () => {
  updateDateRange()
}

const handleTabChange = (tab) => {
  activeTab.value = tab
}

const loadChartData = async () => {
  chartsLoading.value = true
  
  try {
    // Load all chart data in parallel
    const [salesData, ordersData, revenueData] = await Promise.all([
      fetch(`${props.charts.salesOverTime}?period=${selectedPeriod.value}`).then(r => r.json()),
      fetch(`${props.charts.ordersByStatus}?period=${selectedPeriod.value}`).then(r => r.json()),
      fetch(`${props.charts.revenueByChannel}?period=${selectedPeriod.value}`).then(r => r.json()),
    ])
    
    salesOverTimeData.value = salesData.data
    ordersByStatusData.value = ordersData.data
    revenueByChannelData.value = revenueData.data
  } catch (error) {
    console.error('Failed to load chart data:', error)
  } finally {
    chartsLoading.value = false
  }
}

// Lifecycle
onMounted(() => {
  selectedPeriod.value = props.dateRange?.period || 'last_30_days'
  loadChartData()
})

// Auto-refresh live data every 60 seconds
let refreshInterval
onMounted(() => {
  refreshInterval = setInterval(() => {
    if (activeTab.value === 'overview') {
      loadChartData()
    }
  }, 60000)
})

onUnmounted(() => {
  if (refreshInterval) {
    clearInterval(refreshInterval)
  }
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

.btn {
  display: inline-flex;
  align-items: center;
  padding: 0.5rem 1rem;
  border: 1px solid transparent;
  font-size: 0.875rem;
  font-weight: 500;
  border-radius: 0.375rem;
  box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}

.btn:focus {
  outline: none;
  box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
}

.btn-secondary {
  color: #374151;
  background-color: white;
  border-color: #d1d5db;
}

.btn-secondary:hover {
  background-color: #f9fafb;
}

.btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
