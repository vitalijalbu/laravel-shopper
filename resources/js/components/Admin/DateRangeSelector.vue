<template>
  <div class="flex items-center space-x-3">
    <div class="flex items-center space-x-2">
      <label for="start-date" class="text-sm font-medium text-gray-700">From:</label>
      <input
        id="start-date"
        type="date"
        :value="formatDateForInput(startDate)"
        @change="updateStartDate"
        class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
      />
    </div>
    
    <div class="flex items-center space-x-2">
      <label for="end-date" class="text-sm font-medium text-gray-700">To:</label>
      <input
        id="end-date"
        type="date"
        :value="formatDateForInput(endDate)"
        @change="updateEndDate"
        class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
      />
    </div>

    <div class="flex space-x-1">
      <button
        v-for="preset in datePresets"
        :key="preset.value"
        @click="applyPreset(preset)"
        :class="[
          'px-3 py-1 text-sm rounded-md transition-colors',
          isActivePreset(preset) 
            ? 'bg-blue-100 text-blue-700 border border-blue-200' 
            : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'
        ]"
      >
        {{ preset.label }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  startDate: {
    type: Date,
    required: true,
  },
  endDate: {
    type: Date,
    required: true,
  },
})

const emit = defineEmits(['update:startDate', 'update:endDate', 'change'])

const datePresets = [
  {
    label: 'Today',
    value: 'today',
    getDates: () => {
      const today = new Date()
      return { start: today, end: today }
    }
  },
  {
    label: 'Yesterday',
    value: 'yesterday',
    getDates: () => {
      const yesterday = new Date()
      yesterday.setDate(yesterday.getDate() - 1)
      return { start: yesterday, end: yesterday }
    }
  },
  {
    label: 'Last 7 days',
    value: 'last_7_days',
    getDates: () => {
      const end = new Date()
      const start = new Date()
      start.setDate(start.getDate() - 6)
      return { start, end }
    }
  },
  {
    label: 'Last 30 days',
    value: 'last_30_days',
    getDates: () => {
      const end = new Date()
      const start = new Date()
      start.setDate(start.getDate() - 29)
      return { start, end }
    }
  },
  {
    label: 'Last 90 days',
    value: 'last_90_days',
    getDates: () => {
      const end = new Date()
      const start = new Date()
      start.setDate(start.getDate() - 89)
      return { start, end }
    }
  },
  {
    label: 'This month',
    value: 'this_month',
    getDates: () => {
      const now = new Date()
      const start = new Date(now.getFullYear(), now.getMonth(), 1)
      const end = new Date()
      return { start, end }
    }
  },
  {
    label: 'Last month',
    value: 'last_month',
    getDates: () => {
      const now = new Date()
      const start = new Date(now.getFullYear(), now.getMonth() - 1, 1)
      const end = new Date(now.getFullYear(), now.getMonth(), 0)
      return { start, end }
    }
  },
]

const formatDateForInput = (date) => {
  if (!date) return ''
  return date.toISOString().split('T')[0]
}

const updateStartDate = (event) => {
  const newDate = new Date(event.target.value)
  emit('update:startDate', newDate)
  emit('change', { start: newDate, end: props.endDate })
}

const updateEndDate = (event) => {
  const newDate = new Date(event.target.value)
  emit('update:endDate', newDate)
  emit('change', { start: props.startDate, end: newDate })
}

const applyPreset = (preset) => {
  const { start, end } = preset.getDates()
  emit('update:startDate', start)
  emit('update:endDate', end)
  emit('change', { start, end })
}

const isActivePreset = (preset) => {
  const { start, end } = preset.getDates()
  return (
    formatDateForInput(start) === formatDateForInput(props.startDate) &&
    formatDateForInput(end) === formatDateForInput(props.endDate)
  )
}
</script>
