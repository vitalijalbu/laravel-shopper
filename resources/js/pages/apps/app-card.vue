<template>
  <div
    class="bg-white rounded-lg border shadow-sm overflow-hidden hover:shadow-md transition-shadow"
  >
    <div class="p-6">
      <div class="flex items-center space-x-3 mb-4">
        <div
          class="w-12 h-12 bg-gray-100 rounded-lg flex items-center justify-center"
        >
          <svg
            class="w-6 h-6 text-gray-600"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"
            />
          </svg>
        </div>
        <div>
          <h3 class="text-lg font-medium text-gray-900">{{ app.name }}</h3>
          <p class="text-sm text-gray-500">by {{ app.author }}</p>
        </div>
      </div>

      <p class="text-gray-600 text-sm mb-4">{{ app.description }}</p>

      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <span class="text-lg font-bold text-gray-900">{{
            app.price || "Free"
          }}</span>
          <div class="flex items-center space-x-1">
            <svg
              class="w-4 h-4 text-yellow-400"
              fill="currentColor"
              viewBox="0 0 20 20"
            >
              <path
                d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"
              />
            </svg>
            <span class="text-sm text-gray-500">{{ app.rating || "4.8" }}</span>
          </div>
        </div>

        <button
          type="button"
          class="px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
        >
          Install
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  app: {
    type: Object,
    required: true,
  },
});
</script>
