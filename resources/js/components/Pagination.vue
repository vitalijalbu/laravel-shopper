<template>
  <nav v-if="links.length > 3" class="flex items-center justify-between">
    <div class="flex items-center space-x-2">
      <p class="text-sm text-gray-700">
        Showing {{ meta?.from || 1 }} to {{ meta?.to || 0 }} of {{ meta?.total || 0 }} results
      </p>
    </div>
    
    <div class="flex items-center space-x-1">
      <template v-for="(link, index) in links" :key="index">
        <component
          :is="link.url ? Link : 'span'"
          :href="link.url"
          :class="[
            'px-3 py-2 text-sm font-medium rounded-md transition-colors',
            link.active
              ? 'bg-blue-600 text-white'
              : link.url
              ? 'text-gray-700 hover:bg-gray-100'
              : 'text-gray-400 cursor-not-allowed',
          ]"
          v-html="link.label"
        />
      </template>
    </div>
  </nav>
</template>

<script setup lang="ts">
import { Link } from '@inertiajs/vue3'

interface PaginationLink {
  url: string | null
  label: string
  active: boolean
}

interface PaginationMeta {
  from: number
  to: number
  total: number
  per_page: number
  current_page: number
  last_page: number
}

defineProps<{
  links: PaginationLink[]
  meta?: PaginationMeta
}>()
</script>
