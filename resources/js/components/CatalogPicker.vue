<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
      Assign Catalogs
    </label>

    <div v-if="availableCatalogs.length === 0" class="p-4 text-center text-gray-500 border border-gray-300 rounded-md">
      No catalogs available
    </div>

    <div v-else class="space-y-3">
      <div
        v-for="catalog in availableCatalogs"
        :key="catalog.id"
        class="border border-gray-300 rounded-md p-4"
      >
        <div class="flex items-start justify-between">
          <div class="flex items-center space-x-3 flex-1">
            <input
              type="checkbox"
              :checked="isCatalogSelected(catalog.id)"
              @change="toggleCatalog(catalog.id)"
              class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500 mt-1"
            />
            <div class="flex-1">
              <h3 class="text-sm font-medium text-gray-900">{{ catalog.name }}</h3>
              <p v-if="catalog.description" class="text-sm text-gray-500">{{ catalog.description }}</p>
            </div>
          </div>
        </div>

        <!-- Pivot Settings (shown only if catalog is selected) -->
        <div v-if="isCatalogSelected(catalog.id)" class="mt-4 ml-8 grid grid-cols-1 md:grid-cols-3 gap-4 p-4 bg-gray-50 rounded">
          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Priority</label>
            <input
              v-model.number="getCatalogPivot(catalog.id).priority"
              type="number"
              min="0"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            />
          </div>

          <div>
            <label class="flex items-center space-x-2 pt-6">
              <input
                v-model="getCatalogPivot(catalog.id).is_default"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <span class="text-xs text-gray-700">Default</span>
            </label>
          </div>

          <div>
            <label class="flex items-center space-x-2 pt-6">
              <input
                v-model="getCatalogPivot(catalog.id).is_active"
                type="checkbox"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <span class="text-xs text-gray-700">Active</span>
            </label>
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Starts At</label>
            <input
              v-model="getCatalogPivot(catalog.id).starts_at"
              type="datetime-local"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            />
          </div>

          <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">Ends At</label>
            <input
              v-model="getCatalogPivot(catalog.id).ends_at"
              type="datetime-local"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 text-sm"
            />
          </div>
        </div>
      </div>
    </div>

    <p class="mt-2 text-sm text-gray-500">{{ selectedCount }} catalogs assigned</p>
  </div>
</template>

<script setup>
import { computed, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  availableCatalogs: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedCount = computed(() => props.modelValue.length)

const isCatalogSelected = (catalogId) => {
  return props.modelValue.some((c) => c.id === catalogId)
}

const getCatalogPivot = (catalogId) => {
  const catalog = props.modelValue.find((c) => c.id === catalogId)
  return (
    catalog || {
      id: catalogId,
      priority: 0,
      is_default: false,
      is_active: true,
      starts_at: null,
      ends_at: null,
      settings: {},
    }
  )
}

const toggleCatalog = (catalogId) => {
  const newValue = [...props.modelValue]
  const index = newValue.findIndex((c) => c.id === catalogId)

  if (index > -1) {
    newValue.splice(index, 1)
  } else {
    newValue.push({
      id: catalogId,
      priority: 0,
      is_default: false,
      is_active: true,
      starts_at: null,
      ends_at: null,
      settings: {},
    })
  }

  emit('update:modelValue', newValue)
}

// Watch for pivot changes
watch(
  () => props.modelValue,
  (newValue) => {
    emit('update:modelValue', newValue)
  },
  { deep: true }
)
</script>
