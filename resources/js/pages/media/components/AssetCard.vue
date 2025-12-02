<template>
  <div
    class="group relative bg-white border-2 rounded-lg overflow-hidden cursor-pointer transition-all"
    :class="[
      selected
        ? 'border-primary-500 ring-2 ring-primary-500'
        : 'border-gray-200 hover:border-gray-300 hover:shadow-md'
    ]"
    @click="$emit('click', asset)"
  >
    <!-- Selection Checkbox -->
    <div class="absolute top-2 left-2 z-10">
      <input
        type="checkbox"
        :checked="selected"
        @click.stop="$emit('select', !selected)"
        class="h-5 w-5 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
      />
    </div>

    <!-- Preview -->
    <div class="aspect-square bg-gray-50 flex items-center justify-center overflow-hidden">
      <img
        v-if="asset.is_image"
        :src="asset.url"
        :alt="asset.meta?.alt || asset.filename"
        class="w-full h-full object-cover"
        loading="lazy"
      />
      <div v-else-if="asset.is_video" class="relative w-full h-full bg-gray-900 flex items-center justify-center">
        <VideoCameraIcon class="h-12 w-12 text-gray-400" />
        <span class="absolute bottom-2 right-2 px-2 py-0.5 bg-black/70 text-white text-xs rounded">
          {{ asset.meta?.duration || 'Video' }}
        </span>
      </div>
      <div v-else-if="asset.is_document" class="w-full h-full flex flex-col items-center justify-center">
        <DocumentTextIcon class="h-16 w-16 text-gray-400" />
        <span class="mt-2 text-xs font-medium text-gray-500 uppercase">{{ asset.extension }}</span>
      </div>
      <div v-else class="w-full h-full flex flex-col items-center justify-center">
        <DocumentIcon class="h-16 w-16 text-gray-400" />
        <span class="mt-2 text-xs font-medium text-gray-500 uppercase">{{ asset.extension }}</span>
      </div>
    </div>

    <!-- Info -->
    <div class="p-3 border-t border-gray-200">
      <p class="text-sm font-medium text-gray-900 truncate" :title="asset.filename">
        {{ asset.filename }}
      </p>
      <div class="mt-1 flex items-center justify-between text-xs text-gray-500">
        <span>{{ asset.size_human }}</span>
        <span v-if="asset.width && asset.height">
          {{ asset.width }} × {{ asset.height }}
        </span>
      </div>
    </div>

    <!-- Actions (on hover) -->
    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center space-x-2">
      <button
        @click.stop="$emit('edit', asset)"
        class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors"
        :title="t('common.edit')"
      >
        <PencilIcon class="h-5 w-5 text-gray-700" />
      </button>
      <button
        @click.stop="downloadAsset"
        class="p-2 bg-white rounded-full hover:bg-gray-100 transition-colors"
        :title="t('assets.download')"
      >
        <ArrowDownTrayIcon class="h-5 w-5 text-gray-700" />
      </button>
      <button
        @click.stop="$emit('delete', asset)"
        class="p-2 bg-white rounded-full hover:bg-red-50 transition-colors"
        :title="t('common.delete')"
      >
        <TrashIcon class="h-5 w-5 text-red-600" />
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { useTranslations } from '@/composables/useTranslations';
import {
  VideoCameraIcon,
  DocumentTextIcon,
  DocumentIcon,
  PencilIcon,
  ArrowDownTrayIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline';

interface Props {
  asset: any;
  selected: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits(['select', 'click', 'edit', 'delete']);
const { t } = useTranslations();

const downloadAsset = () => {
  window.open(route('api.admin.assets.download', props.asset.id), '_blank');
};
</script>
