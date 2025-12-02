<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
      Select Countries <span class="text-red-500">*</span>
    </label>
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-300 rounded-md">
      <label
        v-for="country in availableCountries"
        :key="country.code"
        class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
      >
        <input
          type="checkbox"
          :value="country.code"
          :checked="isSelected(country.code)"
          @change="toggleCountry(country.code)"
          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
        <span class="text-sm text-gray-700">{{ country.name }} ({{ country.code }})</span>
      </label>
    </div>
    <p class="mt-2 text-sm text-gray-500">{{ selectedCount }} countries selected</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  availableCountries: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedCount = computed(() => props.modelValue.length)

const isSelected = (code) => {
  return props.modelValue.includes(code)
}

const toggleCountry = (code) => {
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
