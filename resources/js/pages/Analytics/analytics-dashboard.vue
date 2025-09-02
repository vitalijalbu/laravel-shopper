<script setup lang="ts">
import { ref, computed, onMounted } from "vue";
import { Head, usePage } from "@inertiajs/vue3";
import Card from "@/components/ui/Card.vue";
import CardHeader from "@/components/ui/CardHeader.vue";
import CardTitle from "@/components/ui/CardTitle.vue";
import CardContent from "@/components/ui/CardContent.vue";
import { Line, Bar, Doughnut } from "vue-chartjs";
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
} from "chart.js";
import { formatCurrency, formatNumber } from "../../utils/formatters";

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  BarElement,
  ArcElement,
  Title,
  Tooltip,
  Legend,
);

interface Stats {
  total_revenue: number;
  total_orders: number;
  total_visitors: number;
  conversion_rate: number;
}

interface ChartDataPoint {
  date: string;
  value: number;
}

interface RevenueData {
  total: number;
  chart_data: ChartDataPoint[];
}

interface OrdersData {
  total: number;
  chart_data: ChartDataPoint[];
}

interface VisitorsData {
  total: number;
  chart_data: ChartDataPoint[];
}

interface Product {
  id: number;
  name: string;
  views: number;
  sales: number;
}

interface Order {
  id: number;
  customer: string;
  total: number;
  status: string;
}

interface Props {
  stats: Stats;
  revenue_data: RevenueData;
  orders_data: OrdersData;
  visitors_data: VisitorsData;
  top_products: Product[];
  recent_orders: Order[];
  widgets: string[];
  period: string;
}

const props = withDefaults(defineProps<Props>(), {
  period: "30",
  widgets: () => [
    "revenue_chart",
    "orders_chart",
    "visitors_chart",
    "top_products",
    "recent_orders",
  ],
});

const selectedPeriod = ref(props.period);
const isLoading = ref(false);

// Chart options
const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  scales: {
    y: {
      beginAtZero: true,
    },
  },
};

// Revenue chart data
const revenueChartData = computed(() => ({
  labels: props.revenue_data.chart_data.map((d) =>
    new Date(d.date).toLocaleDateString(),
  ),
  datasets: [
    {
      label: "Revenue",
      data: props.revenue_data.chart_data.map((d) => d.value),
      borderColor: "rgb(59, 130, 246)",
      backgroundColor: "rgba(59, 130, 246, 0.1)",
      tension: 0.3,
    },
  ],
}));

// Orders chart data
const ordersChartData = computed(() => ({
  labels: props.orders_data.chart_data.map((d) =>
    new Date(d.date).toLocaleDateString(),
  ),
  datasets: [
    {
      label: "Orders",
      data: props.orders_data.chart_data.map((d) => d.value),
      backgroundColor: "rgba(34, 197, 94, 0.8)",
      borderColor: "rgb(34, 197, 94)",
      borderWidth: 1,
    },
  ],
}));

// Visitors chart data
const visitorsChartData = computed(() => ({
  labels: props.visitors_data.chart_data.map((d) =>
    new Date(d.date).toLocaleDateString(),
  ),
  datasets: [
    {
      label: "Visitors",
      data: props.visitors_data.chart_data.map((d) => d.value),
      borderColor: "rgb(168, 85, 247)",
      backgroundColor: "rgba(168, 85, 247, 0.1)",
      fill: true,
      tension: 0.3,
    },
  ],
}));

// Check if widget is enabled
const isWidgetEnabled = (widget: string) => {
  return props.widgets.includes(widget);
};

// Get status badge class
const getStatusBadgeClass = (status: string) => {
  const classes = {
    completed: "bg-green-100 text-green-800",
    pending: "bg-yellow-100 text-yellow-800",
    cancelled: "bg-red-100 text-red-800",
    processing: "bg-blue-100 text-blue-800",
  };
  return classes[status as keyof typeof classes] || "bg-gray-100 text-gray-800";
};

// Handle period change
const handlePeriodChange = (period: string) => {
  if (period === selectedPeriod.value) return;

  selectedPeriod.value = period;
  isLoading.value = true;

  // Reload page with new period
  window.location.href = `/admin/analytics?period=${period}`;
};

onMounted(() => {
  // Any initialization logic
});
</script>

<template>
  <Head title="Analytics Dashboard" />

  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Analytics Dashboard</h1>
          <p class="text-gray-600 mt-1">
            Monitor your store's performance and insights
          </p>
        </div>

        <div class="flex items-center gap-4">
          <select
            v-model="selectedPeriod"
            @change="handlePeriodChange(selectedPeriod)"
            class="rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
          >
            <option value="7">Last 7 days</option>
            <option value="30">Last 30 days</option>
            <option value="90">Last 90 days</option>
            <option value="365">Last year</option>
          </select>

          <DateRangePicker />
        </div>
      </div>

      <!-- Stats Overview -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Revenue</p>
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatCurrency(stats.total_revenue) }}
                </p>
              </div>
              <div class="p-3 bg-blue-100 rounded-full">
                <svg
                  class="w-6 h-6 text-blue-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"
                  />
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Total Orders</p>
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(stats.total_orders) }}
                </p>
              </div>
              <div class="p-3 bg-green-100 rounded-full">
                <svg
                  class="w-6 h-6 text-green-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
                  />
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Visitors</p>
                <p class="text-3xl font-bold text-gray-900">
                  {{ formatNumber(stats.total_visitors) }}
                </p>
              </div>
              <div class="p-3 bg-purple-100 rounded-full">
                <svg
                  class="w-6 h-6 text-purple-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"
                  />
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>

        <Card>
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-gray-600">Conversion Rate</p>
                <p class="text-3xl font-bold text-gray-900">
                  {{ stats.conversion_rate }}%
                </p>
              </div>
              <div class="p-3 bg-yellow-100 rounded-full">
                <svg
                  class="w-6 h-6 text-yellow-600"
                  fill="none"
                  stroke="currentColor"
                  viewBox="0 0 24 24"
                >
                  <path
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    stroke-width="2"
                    d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"
                  />
                </svg>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Charts Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Revenue Chart -->
        <Card v-if="isWidgetEnabled('revenue_chart')">
          <CardHeader>
            <CardTitle>Revenue Trend</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-80">
              <Line :data="revenueChartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Orders Chart -->
        <Card v-if="isWidgetEnabled('orders_chart')">
          <CardHeader>
            <CardTitle>Orders</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-80">
              <Bar :data="ordersChartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>

        <!-- Visitors Chart -->
        <Card v-if="isWidgetEnabled('visitors_chart')" class="lg:col-span-2">
          <CardHeader>
            <CardTitle>Website Traffic</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="h-80">
              <Line :data="visitorsChartData" :options="chartOptions" />
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Bottom Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Top Products -->
        <Card v-if="isWidgetEnabled('top_products')">
          <CardHeader>
            <CardTitle>Top Products</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="product in top_products.slice(0, 5)"
                :key="product.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">{{ product.name }}</p>
                  <p class="text-sm text-gray-600">
                    {{ formatNumber(product.views) }} views
                  </p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">
                    {{ product.sales }} sales
                  </p>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Recent Orders -->
        <Card v-if="isWidgetEnabled('recent_orders')">
          <CardHeader>
            <CardTitle>Recent Orders</CardTitle>
          </CardHeader>
          <CardContent>
            <div class="space-y-4">
              <div
                v-for="order in recent_orders.slice(0, 5)"
                :key="order.id"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div>
                  <p class="font-medium text-gray-900">#{{ order.id }}</p>
                  <p class="text-sm text-gray-600">{{ order.customer }}</p>
                </div>
                <div class="text-right">
                  <p class="font-medium text-gray-900">
                    {{ formatCurrency(order.total) }}
                  </p>
                  <span
                    :class="[
                      'inline-block px-2 py-1 text-xs font-medium rounded-full',
                      getStatusBadgeClass(order.status),
                    ]"
                  >
                    {{ order.status }}
                  </span>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Loading Overlay -->
      <div
        v-if="isLoading"
        class="fixed inset-0 bg-black bg-opacity-25 flex items-center justify-center z-50"
      >
        <div class="bg-white p-6 rounded-lg shadow-lg">
          <div class="flex items-center space-x-3">
            <div
              class="animate-spin rounded-full h-6 w-6 border-b-2 border-blue-600"
            ></div>
            <span>Loading analytics...</span>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
