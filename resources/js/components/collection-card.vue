<template>
  <div class="collection-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200">
    <div class="p-6">
      <div class="flex items-start justify-between mb-4">
        <div class="flex items-center space-x-3">
          <div :class="[
            'w-10 h-10 rounded-md flex items-center justify-center text-white',
            collection.color || 'bg-blue-500'
          ]">
            <icon :name="collection.icon || 'collection'" :size="20" />
          </div>
          <div>
            <h3 class="text-lg font-medium text-gray-900">
              {{ collection.title }}
            </h3>
            <p class="text-sm text-gray-500" v-if="collection.handle">
              {{ collection.handle }}
            </p>
          </div>
        </div>
        <div class="flex items-center space-x-2">
          <span v-if="collection.is_published" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
            Published
          </span>
          <span v-else class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
            Draft
          </span>
        </div>
      </div>

      <p class="text-gray-600 text-sm mb-4 line-clamp-2" v-if="collection.description">
        {{ collection.description }}
      </p>

      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4 text-sm text-gray-500">
          <span v-if="collection.entries_count !== undefined">
            {{ collection.entries_count }} {{ collection.entries_count === 1 ? 'entry' : 'entries' }}
          </span>
          <span v-if="collection.created_at">
            Created {{ formatDate(collection.created_at) }}
          </span>
        </div>
        
        <div class="flex items-center space-x-1">
          <router-link 
            :to="`/cp/collections/${collection.handle}/entries`"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            View Entries
          </router-link>
          <router-link 
            :to="`/cp/collections/${collection.handle}/edit`"
            class="inline-flex items-center p-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <icon name="pencil" :size="14" />
          </router-link>
        </div>
      </div>
    </div>

    <!-- Quick actions on hover -->
    <div class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute inset-0 bg-black bg-opacity-5 flex items-center justify-center">
      <div class="flex items-center space-x-2">
        <router-link 
          :to="`/cp/collections/${collection.handle}/entries/create`"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <icon name="plus" :size="16" class="mr-1" />
          Add Entry
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import Icon from './icon.vue'

const props = defineProps({
  collection: {
    type: Object,
    required: true
  }
})

const formatDate = (dateString) => {
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', { 
    year: 'numeric', 
    month: 'short', 
    day: 'numeric' 
  })
}
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.collection-card {
  position: relative;
}

.collection-card:hover .opacity-0 {
  opacity: 1;
}
</style>
