<template>
  <Dialog v-model:open="isOpen">
    <DialogTrigger as-child>
      <button
        type="button"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
      >
        <CloudArrowUpIcon class="h-5 w-5 mr-2" />
        {{ t('assets.upload') }}
      </button>
    </DialogTrigger>

    <DialogPortal>
      <DialogOverlay class="fixed inset-0 bg-black/50 z-50" />
      <DialogContent class="fixed left-1/2 top-1/2 -translate-x-1/2 -translate-y-1/2 z-50 w-full max-w-2xl bg-white rounded-lg shadow-xl">
        <DialogTitle class="text-lg font-medium text-gray-900 p-6 border-b border-gray-200">
          {{ t('assets.upload_files') }}
        </DialogTitle>

        <div class="p-6 space-y-6">
          <!-- Drop Zone -->
          <div
            ref="dropzoneRef"
            :class="[
              'border-2 border-dashed rounded-lg p-12 text-center transition-colors',
              isDragOver
                ? 'border-primary-500 bg-primary-50'
                : 'border-gray-300 hover:border-gray-400'
            ]"
            @drop.prevent="handleDrop"
            @dragover.prevent="isDragOver = true"
            @dragleave.prevent="isDragOver = false"
          >
            <CloudArrowUpIcon class="mx-auto h-16 w-16 text-gray-400" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">
              {{ t('assets.drop_files_here') }}
            </h3>
            <p class="mt-2 text-sm text-gray-500">
              {{ t('assets.or_click_to_browse') }}
            </p>
            <input
              ref="fileInputRef"
              type="file"
              multiple
              :accept="acceptedTypes"
              class="hidden"
              @change="handleFileSelect"
            />
            <button
              type="button"
              @click="fileInputRef?.click()"
              class="mt-6 inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              {{ t('assets.select_files') }}
            </button>

            <p v-if="maxFileSize" class="mt-4 text-xs text-gray-500">
              {{ t('assets.max_file_size', { size: formatBytes(maxFileSize) }) }}
            </p>
            <p v-if="allowedExtensions.length" class="mt-1 text-xs text-gray-500">
              {{ t('assets.allowed_types') }}: {{ allowedExtensions.join(', ') }}
            </p>
          </div>

          <!-- Selected Files List -->
          <div v-if="files.length > 0" class="space-y-3">
            <div class="flex items-center justify-between">
              <h4 class="text-sm font-medium text-gray-900">
                {{ t('assets.selected_files', { count: files.length }) }}
              </h4>
              <button
                type="button"
                @click="files = []"
                class="text-sm text-gray-500 hover:text-gray-700"
              >
                {{ t('common.clear_all') }}
              </button>
            </div>

            <div class="max-h-64 overflow-y-auto space-y-2">
              <div
                v-for="(file, index) in files"
                :key="index"
                class="flex items-center justify-between p-3 bg-gray-50 rounded-lg"
              >
                <div class="flex items-center space-x-3 flex-1 min-w-0">
                  <DocumentIcon class="h-8 w-8 text-gray-400 flex-shrink-0" />
                  <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 truncate">
                      {{ file.name }}
                    </p>
                    <p class="text-xs text-gray-500">
                      {{ formatBytes(file.size) }}
                    </p>
                  </div>
                </div>

                <!-- Progress Bar -->
                <div v-if="uploadProgress[index] !== undefined" class="ml-4 w-24">
                  <div class="h-2 bg-gray-200 rounded-full overflow-hidden">
                    <div
                      class="h-full bg-primary-600 transition-all"
                      :style="{ width: `${uploadProgress[index]}%` }"
                    />
                  </div>
                </div>

                <!-- Status Icon -->
                <div class="ml-4">
                  <CheckCircleIcon
                    v-if="uploadProgress[index] === 100"
                    class="h-5 w-5 text-green-500"
                  />
                  <ExclamationCircleIcon
                    v-else-if="uploadErrors[index]"
                    class="h-5 w-5 text-red-500"
                    :title="uploadErrors[index]"
                  />
                  <button
                    v-else-if="uploadProgress[index] === undefined"
                    type="button"
                    @click="removeFile(index)"
                    class="text-gray-400 hover:text-gray-600"
                  >
                    <XMarkIcon class="h-5 w-5" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Upload Button -->
          <div v-if="files.length > 0 && !isUploading" class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
            <button
              type="button"
              @click="isOpen = false"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
            >
              {{ t('common.cancel') }}
            </button>
            <button
              type="button"
              @click="uploadFiles"
              class="px-4 py-2 text-sm font-medium text-white bg-primary-600 rounded-md hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500"
            >
              {{ t('assets.upload_n_files', { count: files.length }) }}
            </button>
          </div>

          <!-- Uploading State -->
          <div v-if="isUploading" class="flex items-center justify-center space-x-3 pt-4 border-t border-gray-200">
            <div class="animate-spin rounded-full h-5 w-5 border-b-2 border-primary-600"></div>
            <span class="text-sm text-gray-600">
              {{ t('assets.uploading_files', {
                completed: Object.values(uploadProgress).filter(p => p === 100).length,
                total: files.length
              }) }}
            </span>
          </div>
        </div>

        <DialogClose class="absolute top-4 right-4 text-gray-400 hover:text-gray-600">
          <XMarkIcon class="h-6 w-6" />
        </DialogClose>
      </DialogContent>
    </DialogPortal>
  </Dialog>
</template>

<script setup lang="ts">
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { useNotification } from '@/composables/use-notification';
import {
  Dialog,
  DialogClose,
  DialogContent,
  DialogOverlay,
  DialogPortal,
  DialogTitle,
  DialogTrigger,
} from 'reka-ui';
import {
  CloudArrowUpIcon,
  DocumentIcon,
  CheckCircleIcon,
  ExclamationCircleIcon,
  XMarkIcon,
} from '@heroicons/vue/24/outline';

interface Props {
  container: string;
  folder?: string;
  allowedExtensions?: string[];
  maxFileSize?: number;
}

const props = withDefaults(defineProps<Props>(), {
  folder: '',
  allowedExtensions: () => [],
  maxFileSize: 10485760, // 10MB default
});

const emit = defineEmits(['uploaded']);
const { t } = useTranslations();
const { notify } = useNotification();

const isOpen = ref(false);
const isDragOver = ref(false);
const files = ref<File[]>([]);
const isUploading = ref(false);
const uploadProgress = ref<Record<number, number>>({});
const uploadErrors = ref<Record<number, string>>({});
const fileInputRef = ref<HTMLInputElement>();
const dropzoneRef = ref<HTMLDivElement>();

const acceptedTypes = props.allowedExtensions.length
  ? props.allowedExtensions.map(ext => `.${ext}`).join(',')
  : '*';

const handleDrop = (e: DragEvent) => {
  isDragOver.value = false;
  const droppedFiles = Array.from(e.dataTransfer?.files || []);
  addFiles(droppedFiles);
};

const handleFileSelect = (e: Event) => {
  const input = e.target as HTMLInputElement;
  const selectedFiles = Array.from(input.files || []);
  addFiles(selectedFiles);
  input.value = '';
};

const addFiles = (newFiles: File[]) => {
  const validFiles = newFiles.filter(file => {
    // Check file size
    if (props.maxFileSize && file.size > props.maxFileSize) {
      notify.error(t('assets.file_too_large', { name: file.name }));
      return false;
    }

    // Check extension
    if (props.allowedExtensions.length) {
      const ext = file.name.split('.').pop()?.toLowerCase();
      if (!ext || !props.allowedExtensions.includes(ext)) {
        notify.error(t('assets.file_type_not_allowed', { name: file.name }));
        return false;
      }
    }

    return true;
  });

  files.value.push(...validFiles);
};

const removeFile = (index: number) => {
  files.value.splice(index, 1);
};

const uploadFiles = async () => {
  isUploading.value = true;
  uploadProgress.value = {};
  uploadErrors.value = {};

  for (let i = 0; i < files.value.length; i++) {
    const file = files.value[i];
    const formData = new FormData();
    formData.append('file', file);
    formData.append('container', props.container);
    if (props.folder) formData.append('folder', props.folder);

    try {
      uploadProgress.value[i] = 0;

      const response = await fetch(route('api.admin.assets.upload'), {
        method: 'POST',
        body: formData,
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
        },
        // Track upload progress
      });

      if (response.ok) {
        uploadProgress.value[i] = 100;
      } else {
        const error = await response.json();
        uploadErrors.value[i] = error.message || 'Upload failed';
      }
    } catch (error) {
      uploadErrors.value[i] = error.message || 'Upload failed';
    }
  }

  isUploading.value = false;

  const successCount = Object.values(uploadProgress.value).filter(p => p === 100).length;
  const errorCount = Object.keys(uploadErrors.value).length;

  if (successCount > 0) {
    notify.success(t('assets.uploaded_successfully', { count: successCount }));
    emit('uploaded');
  }

  if (errorCount === 0) {
    isOpen.value = false;
    files.value = [];
    uploadProgress.value = {};
  }
};

const formatBytes = (bytes: number): string => {
  if (bytes === 0) return '0 B';
  const k = 1024;
  const sizes = ['B', 'KB', 'MB', 'GB'];
  const i = Math.floor(Math.log(bytes) / Math.log(k));
  return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};
</script>
