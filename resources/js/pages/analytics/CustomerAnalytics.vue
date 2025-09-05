<template>
  <cp-layout>
    <PageLayout
      title="Customer analytics"
      subtitle="Understand your customer behavior and value"
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
        <!-- Customer Overview Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="Total Customers"
            :value="customerMetrics.total_customers"
            :change="customerMetrics.customers_change"
            icon="users"
            color="blue"
          />
          <MetricCard
            title="New Customers"
            :value="customerMetrics.new_customers"
            :change="customerMetrics.new_customers_change"
            icon="user-plus"
            color="green"
          />
          <MetricCard
            title="Returning Customers"
            :value="customerMetrics.returning_customers"
            :change="customerMetrics.returning_change"
            icon="repeat"
            color="purple"
          />
          <MetricCard
            title="Customer Lifetime Value"
            :value="formatCurrency(customerMetrics.average_ltv)"
            :change="customerMetrics.ltv_change"
            icon="dollar-sign"
            color="orange"
          />
        </div>

        <!-- Customer Acquisition Chart -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Customer acquisition</h3>
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
              :data="customerChartData"
              :height="300"
              :loading="chartLoading"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Customer Segments -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Customer segments</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div 
                  v-for="segment in customerSegments" 
                  :key="segment.name"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center space-x-3">
                    <div 
                      class="w-4 h-4 rounded-full"
                      :style="{ backgroundColor: segment.color }"
                    ></div>
                    <div>
                      <p class="text-sm font-medium text-gray-900">{{ segment.name }}</p>
                      <p class="text-xs text-gray-500">{{ segment.description }}</p>
                    </div>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">{{ segment.count }}</p>
                    <p class="text-xs text-gray-500">{{ segment.percentage }}%</p>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- Top Customers -->
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Top customers by value</h3>
            </div>
            <div class="p-6">
              <div class="space-y-4">
                <div 
                  v-for="(customer, index) in topCustomers" 
                  :key="customer.id"
                  class="flex items-center space-x-4"
                >
                  <div class="flex-shrink-0 w-8 text-center">
                    <span class="text-sm font-medium text-gray-900">{{ index + 1 }}</span>
                  </div>
                  <div class="flex-shrink-0">
                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center">
                      <span class="text-sm font-medium text-gray-700">
                        {{ customer.name.charAt(0).toUpperCase() }}
                      </span>
                    </div>
                  </div>
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ customer.name }}</p>
                    <p class="text-sm text-gray-500">{{ customer.orders }} orders</p>
                  </div>
                  <div class="text-right">
                    <p class="text-sm font-medium text-gray-900">${{ formatCurrency(customer.total_spent) }}</p>
                    <p class="text-sm text-gray-500">Avg: ${{ formatCurrency(customer.average_order) }}</p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Customer Lifetime Value Distribution -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Customer lifetime value distribution</h3>
          </div>
          <div class="p-6">
            <BarChart
              :data="ltvDistributionData"
              :height="300"
              :loading="false"
            />
          </div>
        </div>

        <!-- Customer Cohort Analysis -->
        <div class="bg-white rounded-lg shadow">
          <div class="px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
              <h3 class="text-lg font-medium text-gray-900">Cohort analysis</h3>
              <select 
                v-model="selectedCohortMetric"
                class="text-sm border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="retention">Retention Rate</option>
                <option value="revenue">Revenue</option>
                <option value="orders">Orders</option>
              </select>
            </div>
          </div>
          <div class="p-6 overflow-x-auto">
            <div class="min-w-full">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Cohort
                    </th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Customers
                    </th>
                    <th 
                      v-for="period in cohortPeriods" 
                      :key="period"
                      class="px-4 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider"
                    >
                      {{ period }}
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr v-for="cohort in cohortData" :key="cohort.period">
                    <td class="px-4 py-3 whitespace-nowrap text-sm font-medium text-gray-900">
                      {{ cohort.period }}
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-500">
                      {{ cohort.customers }}
                    </td>
                    <td 
                      v-for="(value, index) in cohort.values" 
                      :key="index"
                      class="px-4 py-3 whitespace-nowrap text-sm text-center"
                      :class="getCohortCellClass(value)"
                    >
                      {{ formatCohortValue(value) }}
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Customer Geography -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Customers by country</h3>
            </div>
            <div class="p-6">
              <div class="space-y-3">
                <div 
                  v-for="country in customersByCountry" 
                  :key="country.code"
                  class="flex items-center justify-between"
                >
                  <div class="flex items-center space-x-3">
                    <span class="text-lg">{{ country.flag }}</span>
                    <span class="text-sm font-medium text-gray-900">{{ country.name }}</span>
                  </div>
                  <div class="flex items-center space-x-3">
                    <span class="text-sm text-gray-500">{{ country.customers }} customers</span>
                    <div class="w-20 bg-gray-200 rounded-full h-2">
                      <div 
                        class="bg-blue-600 h-2 rounded-full" 
                        :style="{ width: country.percentage + '%' }"
                      ></div>
                    </div>
                    <span class="text-sm font-medium text-gray-900 w-10 text-right">{{ country.percentage }}%</span>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
              <h3 class="text-lg font-medium text-gray-900">Customer acquisition channels</h3>
            </div>
            <div class="p-6">
              <DonutChart
                :data="acquisitionChannelData"
                :height="200"
                :loading="false"
              />
              <div class="mt-4 space-y-2">
                <div 
                  v-for="channel in acquisitionChannels" 
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
                    <span class="text-sm font-medium text-gray-900">{{ channel.customers }}</span>
                    <span class="text-sm text-gray-500 ml-2">({{ channel.percentage }}%)</span>
                  </div>
                </div>
              </div>
            </div>
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
import BarChart from '../../components/Admin/Charts/BarChart.vue'
import DonutChart from '../../components/Admin/Charts/DonutChart.vue'
import DateRangeSelector from '../../components/Admin/DateRangeSelector.vue'

const props = defineProps({
  page: Object,
  customerMetrics: Object,
  customerSegments: Array,
  topCustomers: Array,
  cohortData: Array,
  customersByCountry: Array,
  acquisitionChannels: Array,
})

// State
const dateRange = ref({
  start: new Date(Date.now() - 30 * 24 * 60 * 60 * 1000), // 30 days ago
  end: new Date(),
})

const selectedChartPeriod = ref('daily')
const selectedCohortMetric = ref('retention')
const chartLoading = ref(false)
const customerChartData = ref({})

// Chart periods
const chartPeriods = [
  { value: 'daily', label: 'Daily' },
  { value: 'weekly', label: 'Weekly' },
  { value: 'monthly', label: 'Monthly' },
]

// Cohort periods (months)
const cohortPeriods = ['Month 1', 'Month 2', 'Month 3', 'Month 4', 'Month 5', 'Month 6']

// Computed
const ltvDistributionData = computed(() => {
  return {
    labels: ['$0-$100', '$100-$500', '$500-$1000', '$1000-$2500', '$2500+'],
    datasets: [{
      label: 'Customers',
      data: [150, 300, 200, 100, 50],
      backgroundColor: 'rgba(59, 130, 246, 0.8)',
    }]
  }
})

const acquisitionChannelData = computed(() => {
  return {
    labels: props.acquisitionChannels.map(channel => channel.name),
    datasets: [{
      data: props.acquisitionChannels.map(channel => channel.customers),
      backgroundColor: props.acquisitionChannels.map(channel => channel.color),
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

const formatCohortValue = (value) => {
  if (selectedCohortMetric.value === 'retention') {
    return value + '%'
  } else if (selectedCohortMetric.value === 'revenue') {
    return '$' + formatCurrency(value)
  } else {
    return value
  }
}

const getCohortCellClass = (value) => {
  if (selectedCohortMetric.value === 'retention') {
    if (value >= 50) return 'bg-green-100 text-green-800'
    if (value >= 25) return 'bg-yellow-100 text-yellow-800'
    return 'bg-red-100 text-red-800'
  }
  return 'text-gray-900'
}

const loadData = async () => {
  await Promise.all([
    loadCustomerChart(),
    loadCustomerMetrics(),
  ])
}

const loadCustomerChart = async () => {
  chartLoading.value = true
  try {
    const response = await fetch('/cp/analytics/data/customer-chart', {
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
    customerChartData.value = data
  } catch (error) {
    console.error('Failed to load customer chart:', error)
  } finally {
    chartLoading.value = false
  }
}

const loadCustomerMetrics = async () => {
  try {
    const response = await fetch('/cp/analytics/data/customer-metrics', {
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
    Object.assign(props.customerMetrics, data)
  } catch (error) {
    console.error('Failed to load customer metrics:', error)
  }
}

const exportData = () => {
  window.open(`/cp/analytics/customers/export?start_date=${dateRange.value.start.toISOString().split('T')[0]}&end_date=${dateRange.value.end.toISOString().split('T')[0]}`)
}

// Watchers
watch(selectedChartPeriod, () => {
  loadCustomerChart()
})

// Lifecycle
onMounted(() => {
  loadCustomerChart()
})
</script>
