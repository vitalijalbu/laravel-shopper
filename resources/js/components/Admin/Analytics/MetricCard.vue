<template>
  <div class="bg-white rounded-lg shadow p-6">
    <div class="flex items-center">
      <div class="flex-shrink-0">
        <div 
          class="w-8 h-8 rounded-full flex items-center justify-center"
          :class="iconBackgroundClass"
        >
          <Icon :name="icon" class="w-5 h-5" :class="iconColorClass" />
        </div>
      </div>
      <div class="ml-4 flex-1">
        <p class="text-sm font-medium text-gray-500">{{ title }}</p>
        <div class="flex items-baseline">
          <p class="text-2xl font-semibold text-gray-900">{{ value }}</p>
          <div 
            v-if="change !== undefined && change !== null"
            class="ml-2 flex items-baseline text-sm font-semibold"
            :class="changeColorClass"
          >
            <Icon 
              :name="change >= 0 ? 'arrow-up' : 'arrow-down'" 
              class="w-3 h-3 mr-1"
            />
            {{ Math.abs(change) }}%
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './Icon.vue'

const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  value: {
    type: [String, Number],
    required: true,
  },
  change: {
    type: Number,
    default: null,
  },
  icon: {
    type: String,
    required: true,
  },
  color: {
    type: String,
    default: 'blue',
    validator: (value) => ['blue', 'green', 'red', 'yellow', 'purple', 'orange', 'gray'].includes(value),
  },
})

const iconBackgroundClass = computed(() => {
  const colorMap = {
    blue: 'bg-blue-100',
    green: 'bg-green-100',
    red: 'bg-red-100',
    yellow: 'bg-yellow-100',
    purple: 'bg-purple-100',
    orange: 'bg-orange-100',
    gray: 'bg-gray-100',
  }
  return colorMap[props.color] || colorMap.blue
})

const iconColorClass = computed(() => {
  const colorMap = {
    blue: 'text-blue-600',
    green: 'text-green-600',
    red: 'text-red-600',
    yellow: 'text-yellow-600',
    purple: 'text-purple-600',
    orange: 'text-orange-600',
    gray: 'text-gray-600',
  }
  return colorMap[props.color] || colorMap.blue
})

const changeColorClass = computed(() => {
  if (props.change === null || props.change === undefined) return ''
  return props.change >= 0 ? 'text-green-600' : 'text-red-600'
})
</script>
