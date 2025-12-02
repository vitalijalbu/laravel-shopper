<template>
  <Dialog :open="!!asset" @update:open="(open) => !open && $emit('close')">
    <DialogPortal>
      <DialogOverlay class="fixed inset-0 bg-black/50 z-50" />
      <DialogContent class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-full max-w-2xl bg-white rounded-lg shadow-xl">
        <DialogTitle class="text-lg font-medium p-6 border-b">{{ t('assets.edit_asset') }}</DialogTitle>
        
        <form @submit.prevent="save" class="p-6 space-y-6">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('assets.alt_text') }}</label>
            <input v-model="form.meta.alt" type="text" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('assets.title') }}</label>
            <input v-model="form.meta.title" type="text" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500" />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('assets.caption') }}</label>
            <textarea v-model="form.meta.caption" rows="3" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-primary-500 focus:border-primary-500"></textarea>
          </div>

          <div v-if="asset?.is_image">
            <label class="block text-sm font-medium text-gray-700 mb-2">{{ t('assets.focus_point') }}</label>
            <div class="relative inline-block">
              <img :src="asset.url" class="max-h-64 rounded" @click="setFocusPoint" />
              <div v-if="form.focus_point" :style="{ left: form.focus_point.x + '%', top: form.focus_point.y + '%' }" class="absolute w-4 h-4 bg-primary-500 rounded-full -translate-x-1/2 -translate-y-1/2 ring-4 ring-white"></div>
            </div>
          </div>

          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button type="button" @click="$emit('close')" class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50">
              {{ t('common.cancel') }}
            </button>
            <button type="submit" :disabled="processing" class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 disabled:opacity-50">
              {{ t('common.save') }}
            </button>
          </div>
        </form>
      </DialogContent>
    </DialogPortal>
  </Dialog>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { Dialog, DialogContent, DialogOverlay, DialogPortal, DialogTitle } from 'reka-ui';
import { useTranslations } from '@/composables/useTranslations';
import { useNotification } from '@/composables/use-notification';

const props = defineProps<{ asset: any }>();
const emit = defineEmits(['close', 'updated']);
const { t } = useTranslations();
const { notify } = useNotification();

const processing = ref(false);
const form = reactive({
  meta: { ...props.asset?.meta },
  focus_point: props.asset?.focus_css ? parseFocusPoint(props.asset.focus_css) : null,
});

function parseFocusPoint(css: string) {
  const [x, y] = css.split('-').map(Number);
  return { x, y };
}

function setFocusPoint(e: MouseEvent) {
  const rect = (e.target as HTMLElement).getBoundingClientRect();
  const x = Math.round(((e.clientX - rect.left) / rect.width) * 100);
  const y = Math.round(((e.clientY - rect.top) / rect.height) * 100);
  form.focus_point = { x, y };
}

function save() {
  processing.value = true;
  router.put(route('api.admin.assets.update', props.asset.id), form, {
    onSuccess: () => {
      notify.success(t('assets.updated_successfully'));
      emit('updated', props.asset);
    },
    onError: () => notify.error(t('common.error_occurred')),
    onFinish: () => processing.value = false,
  });
}
</script>
