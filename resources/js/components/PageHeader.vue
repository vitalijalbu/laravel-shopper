<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Page Header with Tabs -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header Section -->
        <div class="py-6">
          <div class="flex items-start justify-between">
            <!-- Title & Subtitle -->
            <div class="flex-1 min-w-0">
              <h1 class="text-2xl font-bold text-gray-900 sm:text-3xl">
                {{ title }}
              </h1>
              <p v-if="subtitle" class="mt-2 text-sm text-gray-600">
                {{ subtitle }}
              </p>
            </div>

            <!-- Actions Slot (Right Side) -->
            <div v-if="$slots.actions" class="ml-4 flex-shrink-0">
              <slot name="actions" />
            </div>
          </div>
        </div>

        <!-- Tabs Navigation (Statamic-style) -->
        <div v-if="$slots.tabs" class="-mb-px">
          <slot name="tabs" />
        </div>
      </div>
    </div>

    <!-- Page Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: {
    type: String,
    default: '',
  },
})
</script>
