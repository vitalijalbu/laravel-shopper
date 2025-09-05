<template>
  <cp-layout>
    <PageLayout
      title="Customer analytics"
      subtitle="Understand your customers' behavior and preferences"
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
        <!-- Customer Metrics -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
          <MetricCard
            title="New customers"
            :value="metrics.new_customers"
            :previous="metrics.previous_new_customers"
            :change="metrics.new_customers_change"
            format="number"
          />
          <MetricCard
            title="Returning customers"
            :value="metrics.returning_customers"
            :previous="metrics.previous_returning_customers"
            :change="metrics.returning_customers_change"
            format="number"
          />
          <MetricCard
            title="Customer lifetime value"
            :value="metrics.avg_lifetime_value"
            :previous="metrics.previous_lifetime_value"
            :change="metrics.lifetime_value_change"
            format="currency"
          />
          <MetricCard
            title="Repeat purchase rate"
            :value="metrics.repeat_purchase_rate"
            :previous="metrics.previous_repeat_rate"
            :change="metrics.repeat_rate_change"
            format="percentage"
          />
        </div>

        <!-- Customer Analytics Charts -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Customer Acquisition -->
          <ChartCard title="Customer acquisition">
            <LineChart
              :data="customerAcquisitionData"
              :options="lineChartOptions"
              height="350"
            />
          </ChartCard>

          <!-- Customer Segmentation -->
          <ChartCard title="Customer segmentation">
            <DoughnutChart
              :data="customerSegmentationData"
              height="350"
            />
          </ChartCard>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Order Frequency -->
          <ChartCard title="Order frequency">
            <BarChart
              :data="orderFrequencyData"
              :options="barChartOptions"
              height="350"
            />
          </ChartCard>

          <!-- Customer Geography -->
          <ChartCard title="Customers by location">
            <BarChart
              :data="customerGeographyData"
              :options="horizontalBarOptions"
              height="350"
            />
          </ChartCard>
        </div>

        <!-- Customer Tables -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Top Customers -->
          <DataCard title="Top customers by value">
            <DataTable
              :columns="customerColumns"
              :data="topCustomers"
              :loading="loading"
            />
          </DataCard>

          <!-- Customer Cohorts -->
          <DataCard title="Customer cohort analysis">
            <DataTable
              :columns="cohortColumns"
              :data="cohortAnalysis"
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
const topCustomers = ref([])
const cohortAnalysis = ref([])

const breadcrumbs = [
  { name: 'Analytics', href: '/cp/analytics' },
  { name: 'Customers' }
]

// Chart data
const customerAcquisitionData = ref({
  labels: [],
  datasets: [
    {
      label: 'New customers',
      data: [],
      borderColor: 'rgb(59, 130, 246)',
      backgroundColor: 'rgba(59, 130, 246, 0.1)',
      tension: 0.4
    },
    {
      label: 'Returning customers',
      data: [],
      borderColor: 'rgb(16, 185, 129)',
      backgroundColor: 'rgba(16, 185, 129, 0.1)',
      tension: 0.4
    }
  ]
})

const customerSegmentationData = ref({
  labels: ['New', 'Occasional', 'Regular', 'VIP'],
  datasets: [{
    data: [],
    backgroundColor: [
      'rgb(59, 130, 246)',
      'rgb(16, 185, 129)',
      'rgb(245, 158, 11)',
      'rgb(139, 69, 19)'
    ]
  }]
})

const orderFrequencyData = ref({
  labels: ['1 order', '2-3 orders', '4-5 orders', '6-10 orders', '10+ orders'],
  datasets: [{
    label: 'Customers',
    data: [],
    backgroundColor: 'rgba(59, 130, 246, 0.8)',
    borderColor: 'rgb(59, 130, 246)',
    borderWidth: 1
  }]
})

const customerGeographyData = ref({
  labels: [],
  datasets: [{
    label: 'Customers',
    data: [],
    backgroundColor: 'rgba(16, 185, 129, 0.8)',
    borderColor: 'rgb(16, 185, 129)',
    borderWidth: 1
  }]
})

// Chart options
const lineChartOptions = {
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

const barChartOptions = {
  scales: {
    y: {
      beginAtZero: true
    }
  }
}

const horizontalBarOptions = {
  indexAxis: 'y',
  scales: {
    x: {
      beginAtZero: true
    }
  }
}

// Table columns
const customerColumns = [
  { key: 'name', label: 'Customer' },
  { key: 'orders_count', label: 'Orders' },
  { key: 'total_spent', label: 'Total spent', format: 'currency' },
  { key: 'avg_order_value', label: 'AOV', format: 'currency' },
  { key: 'last_order', label: 'Last order', format: 'date' }
]

const cohortColumns = [
  { key: 'cohort', label: 'Cohort' },
  { key: 'customers', label: 'Customers' },
  { key: 'retention_rate', label: 'Retention rate', format: 'percentage' },
  { key: 'revenue_per_customer', label: 'Revenue/Customer', format: 'currency' }
]

const updateDateRange = async () => {
  await fetchCustomerData()
}

const fetchCustomerData = async () => {
  loading.value = true
  try {
    const response = await fetch(`/cp/api/analytics/customers?period=${selectedPeriod.value}`)
    const data = await response.json()
    
    metrics.value = data.metrics
    topCustomers.value = data.top_customers
    cohortAnalysis.value = data.cohort_analysis
    
    // Update chart data
    customerAcquisitionData.value.labels = data.acquisition_chart.labels
    customerAcquisitionData.value.datasets[0].data = data.acquisition_chart.new_customers
    customerAcquisitionData.value.datasets[1].data = data.acquisition_chart.returning_customers
    
    customerSegmentationData.value.datasets[0].data = data.segmentation_chart.data
    
    orderFrequencyData.value.datasets[0].data = data.frequency_chart.data
    
    customerGeographyData.value.labels = data.geography_chart.labels
    customerGeographyData.value.datasets[0].data = data.geography_chart.data
  } catch (error) {
    console.error('Error fetching customer data:', error)
  } finally {
    loading.value = false
  }
}

onMounted(() => {
  fetchCustomerData()
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
