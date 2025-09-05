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
  BarElement,
  Title,
  Tooltip,
  Legend
} from 'chart.js'

ChartJS.register(
  CategoryScale,
  LinearScale,
  BarElement,
  Title,
  Tooltip,
  Legend
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
  plugins: {
    legend: {
      display: true,
      position: 'top'
    },
    tooltip: {
      backgroundColor: 'rgba(0, 0, 0, 0.8)',
      titleColor: '#fff',
      bodyColor: '#fff',
      cornerRadius: 8
    }
  },
  scales: {
    x: {
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    },
    y: {
      grid: {
        color: 'rgba(0, 0, 0, 0.05)'
      }
    }
  }
}

const createChart = () => {
  if (!canvasRef.value) return

  const ctx = canvasRef.value.getContext('2d')
  
  chart = new ChartJS(ctx, {
    type: 'bar',
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
</style>
