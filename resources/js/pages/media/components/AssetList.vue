<template>
  <table class="min-w-full divide-y divide-gray-200">
    <thead class="bg-gray-50">
      <tr>
        <th class="w-12 px-6 py-3">
          <input type="checkbox" @change="$emit('select-all', $event.target.checked)" class="rounded" />
        </th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('assets.name') }}</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('assets.type') }}</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('assets.size') }}</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('assets.dimensions') }}</th>
        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">{{ t('common.date') }}</th>
        <th class="w-24"></th>
      </tr>
    </thead>
    <tbody class="bg-white divide-y divide-gray-200">
      <tr v-for="asset in assets" :key="asset.id" class="hover:bg-gray-50">
        <td class="px-6 py-4">
          <input type="checkbox" :checked="selected.includes(asset.id)" @change="$emit('select', asset.id)" class="rounded" />
        </td>
        <td class="px-6 py-4">
          <div class="flex items-center cursor-pointer" @click="$emit('view', asset)">
            <img v-if="asset.is_image" :src="asset.url" class="h-10 w-10 rounded object-cover" />
            <DocumentIcon v-else class="h-10 w-10 text-gray-400" />
            <span class="ml-3 text-sm font-medium text-gray-900">{{ asset.filename }}</span>
          </div>
        </td>
        <td class="px-6 py-4 text-sm text-gray-500">{{ asset.type }}</td>
        <td class="px-6 py-4 text-sm text-gray-500">{{ asset.size_human }}</td>
        <td class="px-6 py-4 text-sm text-gray-500">
          <span v-if="asset.width">{{ asset.width }} × {{ asset.height }}</span>
        </td>
        <td class="px-6 py-4 text-sm text-gray-500">{{ formatDate(asset.created_at) }}</td>
        <td class="px-6 py-4 text-right space-x-2">
          <button @click="$emit('edit', asset)" class="text-primary-600 hover:text-primary-900">{{ t('common.edit') }}</button>
          <button @click="$emit('delete', asset)" class="text-red-600 hover:text-red-900">{{ t('common.delete') }}</button>
        </td>
      </tr>
    </tbody>
  </table>
</template>

<script setup lang="ts">
import { DocumentIcon } from '@heroicons/vue/24/outline';
import { useTranslations } from '@/composables/useTranslations';

defineProps<{ assets: any[]; selected: number[] }>();
defineEmits(['select', 'select-all', 'view', 'edit', 'delete']);
const { t } = useTranslations();

const formatDate = (date: string) => new Date(date).toLocaleDateString();
</script>
