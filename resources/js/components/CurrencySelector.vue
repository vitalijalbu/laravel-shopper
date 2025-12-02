<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
      Select Currencies
    </label>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-300 rounded-md">
      <label
        v-for="currency in availableCurrencies"
        :key="currency.code"
        class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
      >
        <input
          type="checkbox"
          :value="currency.code"
          :checked="isSelected(currency.code)"
          @change="toggleCurrency(currency.code)"
          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
        <span class="text-sm text-gray-700">{{ currency.code }} - {{ currency.name }}</span>
      </label>
    </div>
    <p class="mt-2 text-sm text-gray-500">{{ selectedCount }} currencies selected</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  availableCurrencies: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedCount = computed(() => props.modelValue.length)

const isSelected = (code) => {
  return props.modelValue.includes(code)
}

const toggleCurrency = (code) => {
  const newValue = [...props.modelValue]
  const index = newValue.indexOf(code)
  
  if (index > -1) {
    newValue.splice(index, 1)
  } else {
    newValue.push(code)
  }
  
  emit('update:modelValue', newValue)
}
</script>
