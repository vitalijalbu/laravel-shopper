<template>
  <div class="space-y-1">
    <button
      @click="selectFolder('')"
      :class="[
        'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
        currentFolder === '' ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50'
      ]"
    >
      <FolderIcon class="inline h-4 w-4 mr-2" />
      {{ t('assets.all_files') }}
    </button>
    
    <div v-for="folder in folders" :key="folder.path" class="pl-3">
      <button
        @click="selectFolder(folder.path)"
        :class="[
          'w-full text-left px-3 py-2 rounded-md text-sm transition-colors',
          currentFolder === folder.path ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-700 hover:bg-gray-50'
        ]"
      >
        <FolderIcon class="inline h-4 w-4 mr-2" />
        {{ folder.name }}
        <span class="text-xs text-gray-500 ml-1">({{ folder.assets_count }})</span>
      </button>
    </div>
  </div>
</template>

<script setup lang="ts">
import { FolderIcon } from '@heroicons/vue/24/outline';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps<{
  container: string;
  currentFolder: string;
  folders?: Array<{ path: string; name: string; assets_count: number }>;
}>();

const emit = defineEmits(['select']);
const { t } = useTranslations();

const selectFolder = (path: string) => emit('select', path);
</script>
