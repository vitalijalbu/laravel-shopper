<template>
  <div class="media-grid">
    <!-- View Mode Toggle -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center space-x-2">
        <label class="flex items-center">
          <input
            type="checkbox"
            :checked="allSelected"
            @change="$emit('select-all', $event.target.checked)"
            class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
          >
          <span class="ml-2 text-sm text-gray-700">
            {{ $t('common.select_all') }}
          </span>
        </label>
        <span v-if="selected.length > 0" class="text-sm text-gray-600">
          {{ $t('common.selected_count', { count: selected.length }) }}
        </span>
      </div>

      <div class="flex items-center space-x-2">
        <button
          @click="$emit('view-mode', 'grid')"
          :class="[
            'p-2 rounded-md',
            viewMode === 'grid' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'
          ]"
        >
          <GridIcon class="w-5 h-5" />
        </button>
        <button
          @click="$emit('view-mode', 'list')"
          :class="[
            'p-2 rounded-md',
            viewMode === 'list' ? 'bg-blue-100 text-blue-600' : 'text-gray-400 hover:text-gray-600'
          ]"
        >
          <ListIcon class="w-5 h-5" />
        </button>
      </div>
    </div>

    <!-- Grid View -->
    <div v-if="viewMode === 'grid'" class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
      <media-item-card
        v-for="item in media"
        :key="item.id"
        :media="item"
        :selected="selected.includes(item.id)"
        @select="$emit('select', item.id, $event)"
        @edit="$emit('edit', item)"
        @delete="$emit('delete', item)"
        @view="$emit('view', item)"
      />
    </div>

    <!-- List View -->
    <div v-else class="bg-white shadow-sm rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="w-4 px-6 py-3">
              <input
                type="checkbox"
                :checked="allSelected"
                @change="$emit('select-all', $event.target.checked)"
                class="rounded border-gray-300 text-blue-600 shadow-sm focus:ring-blue-500"
              >
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('media.preview') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('media.name') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('media.type') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('media.size') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('media.collection') }}
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('common.date') }}
            </th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
              {{ $t('common.actions') }}
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <media-item-row
            v-for="item in media"
            :key="item.id"
            :media="item"
            :selected="selected.includes(item.id)"
            @select="$emit('select', item.id, $event)"
            @edit="$emit('edit', item)"
            @delete="$emit('delete', item)"
            @view="$emit('view', item)"
          />
        </tbody>
      </table>
    </div>

    <!-- Empty State -->
    <div v-if="media.length === 0" class="text-center py-12">
      <PhotoIcon class="mx-auto h-12 w-12 text-gray-400" />
      <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $t('media.no_media') }}</h3>
      <p class="mt-1 text-sm text-gray-500">{{ $t('media.no_media_description') }}</p>
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { GridIcon, ListIcon, PhotoIcon } from '@heroicons/vue/24/outline'

interface MediaItem {
  id: number
  name: string
  file_name: string
  mime_type: string
  size: number
  collection_name: string
  url: string
  conversions: Record<string, string>
  custom_properties: Record<string, any>
  created_at: string
  updated_at: string
}

interface Props {
  media: MediaItem[]
  selected: number[]
  viewMode: 'grid' | 'list'
}

interface Emits {
  (e: 'select', mediaId: number, selected: boolean): void
  (e: 'select-all', selected: boolean): void
  (e: 'edit', media: MediaItem): void
  (e: 'delete', media: MediaItem): void
  (e: 'view', media: MediaItem): void
  (e: 'view-mode', mode: 'grid' | 'list'): void
}

const props = defineProps<Props>()
const emit = defineEmits<Emits>()
const { t } = useI18n()

const allSelected = computed(() => {
  return props.media.length > 0 && props.selected.length === props.media.length
})
</script>
