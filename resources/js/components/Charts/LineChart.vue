<template>
  <div class="chart-container">
    <canvas :ref="canvasRef" :height="height"></canvas>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, watch, nextTick } from 'vue'
import {
  Chart as ChartJS,
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
} from 'chart.js'

// Register Chart.js components
ChartJS.register(
  CategoryScale,
  LinearScale,
  PointElement,
  LineElement,
  Title,
  Tooltip,
  Legend,
  Filler
)

const props = defineProps({
  data: {
    type: Object,
    required: true
  },
  options: {
    type: Object,
    default: () => ({})
  },
  height: {
    type: [String, Number],
    default: 400
  }
})

const canvasRef = ref(null)
let chart = null

const defaultOptions = {
  responsive: true,
  maintainAspectRatio: false,
  interaction: {
    intersect: false,
    mode: 'index'
  },
  plugins: {
    legend: {
      display: true,
      position: 'top',
      labels: {
        boxWidth: 12,
        padding: 20,
        usePointStyle: true
      }
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      borderColor: 'rgba(255, 255, 255, 0.1)',
      borderWidth: 1,
      cornerRadius: 8,
      displayColors: true,
      usePointStyle: true
    }
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
        borderColor: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: '#6b7280'
      }
    },
    y: {
      grid: {
        color: 'rgba(0, 0, 0, 0.05)',
        borderColor: 'rgba(0, 0, 0, 0.1)'
      },
      ticks: {
        color: '#6b7280'
      }
    }
  },
  elements: {
    line: {
      borderWidth: 3,
      fill: true,
      tension: 0.4
    },
    point: {
      radius: 4,
      hoverRadius: 6,
      borderWidth: 2
    }
  }
}

const createChart = () => {
  if (!canvasRef.value) return

  const ctx = canvasRef.value.getContext('2d')
  
  chart = new ChartJS(ctx, {
    type: 'line',
    data: props.data,
    options: {
      ...defaultOptions,
      ...props.options
    }
  })
}

const updateChart = () => {
  if (!chart) return

  chart.data = props.data
  chart.options = {
    ...defaultOptions,
    ...props.options
  }
  chart.update('none')
}

const destroyChart = () => {
  if (chart) {
    chart.destroy()
    chart = null
  }
}

watch(() => props.data, updateChart, { deep: true })
watch(() => props.options, updateChart, { deep: true })

onMounted(async () => {
  await nextTick()
  createChart()
})

onUnmounted(() => {
  destroyChart()
})
</script>

<style scoped>
.chart-container {
  position: relative;
  width: 100%;
}

canvas {
  max-width: 100%;
  height: auto;
}
</style>
