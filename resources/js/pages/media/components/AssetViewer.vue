<template>
  <Dialog :open="!!asset" @update:open="(open) => !open && $emit('close')">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 bg-black/80 z-50" />
      <DialogContent class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-full max-w-4xl max-h-[90vh] bg-white rounded-lg shadow-xl overflow-auto">
        <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex items-center justify-between">
          <DialogTitle class="text-lg font-medium">{{ asset?.filename }}</DialogTitle>
          <div class="flex items-center space-x-2">
            <button @click="$emit('edit', asset)" class="p-2 text-gray-600 hover:bg-gray-100 rounded">
              <PencilIcon class="h-5 w-5" />
            </button>
            <button @click="downloadAsset" class="p-2 text-gray-600 hover:bg-gray-100 rounded">
              <ArrowDownTrayIcon class="h-5 w-5" />
            </button>
            <DialogClose class="p-2 text-gray-600 hover:bg-gray-100 rounded">
              <XMarkIcon class="h-6 w-6" />
            </DialogClose>
          </div>
        </div>

        <div class="p-6">
          <div class="flex flex-col lg:flex-row gap-6">
            <div class="flex-1">
              <img v-if="asset?.is_image" :src="asset.url" :alt="asset.meta?.alt" class="w-full rounded-lg" />
              <video v-else-if="asset?.is_video" :src="asset.url" controls class="w-full rounded-lg" />
              <div v-else class="flex items-center justify-center h-96 bg-gray-100 rounded-lg">
                <DocumentIcon class="h-24 w-24 text-gray-400" />
              </div>
            </div>

            <div class="lg:w-80 space-y-4">
              <div>
                <h4 class="text-sm font-medium text-gray-700">{{ t('assets.details') }}</h4>
                <dl class="mt-2 space-y-2 text-sm">
                  <div class="flex justify-between"><dt class="text-gray-500">{{ t('assets.size') }}:</dt><dd class="font-medium">{{ asset?.size_human }}</dd></div>
                  <div class="flex justify-between"><dt class="text-gray-500">{{ t('assets.type') }}:</dt><dd class="font-medium">{{ asset?.mime_type }}</dd></div>
                  <div v-if="asset?.width" class="flex justify-between"><dt class="text-gray-500">{{ t('assets.dimensions') }}:</dt><dd class="font-medium">{{ asset.width }} × {{ asset.height }}</dd></div>
                  <div class="flex justify-between"><dt class="text-gray-500">{{ t('common.created') }}:</dt><dd class="font-medium">{{ formatDate(asset?.created_at) }}</dd></div>
                </dl>
              </div>

              <div v-if="asset?.meta">
                <h4 class="text-sm font-medium text-gray-700">{{ t('assets.metadata') }}</h4>
                <dl class="mt-2 space-y-2 text-sm">
                  <div v-if="asset.meta.alt"><dt class="text-gray-500">Alt:</dt><dd>{{ asset.meta.alt }}</dd></div>
                  <div v-if="asset.meta.title"><dt class="text-gray-500">Title:</dt><dd>{{ asset.meta.title }}</dd></div>
                  <div v-if="asset.meta.caption"><dt class="text-gray-500">Caption:</dt><dd>{{ asset.meta.caption }}</dd></div>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </DialogContent>
    </DialogPortal>
  </Dialog>
</template>

<script setup lang="ts">
import { Dialog, DialogClose, DialogContent, DialogOverlay, DialogPortal, DialogTitle } from 'reka-ui';
import { PencilIcon, ArrowDownTrayIcon, XMarkIcon, DocumentIcon } from '@heroicons/vue/24/outline';
import { useTranslations } from '@/composables/useTranslations';

const props = defineProps<{ asset: any }>();
const emit = defineEmits(['close', 'edit', 'delete']);
const { t } = useTranslations();

const downloadAsset = () => window.open(route('api.admin.assets.download', props.asset.id), '_blank');
const formatDate = (date: string) => new Date(date).toLocaleDateString();
</script>
