<template>
  <admin-layout :title="t('assets.title')">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">{{ t('assets.title') }}</h1>
          <p class="text-gray-600">{{ t('assets.subtitle') }}</p>
        </div>
        <AssetUploader :container="currentContainer" @uploaded="refreshAssets" />
      </div>
    </template>

    <!-- Containers Tabs -->
    <div class="bg-white border-b border-gray-200 mb-6">
      <nav class="flex space-x-8 px-6" aria-label="Tabs">
        <button
          v-for="container in containers"
          :key="container.id"
          @click="selectContainer(container.handle)"
          :class="[
            currentContainer === container.handle
              ? 'border-primary-500 text-primary-600'
              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
            'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm'
          ]"
        >
          {{ container.title }}
          <span
            :class="[
              currentContainer === container.handle
                ? 'bg-primary-100 text-primary-600'
                : 'bg-gray-100 text-gray-900',
              'ml-3 py-0.5 px-2.5 rounded-full text-xs font-medium'
            ]"
          >
            {{ container.assets_count }}
          </span>
        </button>
      </nav>
    </div>

    <div class="grid grid-cols-12 gap-6">
      <!-- Folder Tree -->
      <div class="col-span-3">
        <div class="bg-white rounded-lg shadow-sm p-4">
          <h3 class="text-sm font-medium text-gray-900 mb-4">{{ t('assets.folders') }}</h3>
          <FolderTree
            :container="currentContainer"
            :current-folder="currentFolder"
            @select="selectFolder"
          />
        </div>
      </div>

      <!-- Assets Grid -->
      <div class="col-span-9">
        <div class="bg-white rounded-lg shadow-sm">
          <!-- Toolbar -->
          <div class="flex items-center justify-between border-b border-gray-200 p-4">
            <div class="flex items-center space-x-4 flex-1">
              <!-- Search -->
              <div class="relative flex-1 max-w-md">
                <MagnifyingGlassIcon class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-gray-400" />
                <input
                  v-model="searchQuery"
                  type="text"
                  :placeholder="t('assets.search_placeholder')"
                  class="pl-10 pr-4 py-2 w-full border border-gray-300 rounded-lg focus:ring-primary-500 focus:border-primary-500"
                  @input="debouncedSearch"
                />
              </div>

              <!-- Type Filter -->
              <select
                v-model="filterType"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500"
                @change="applyFilters"
              >
                <option value="">{{ t('assets.all_types') }}</option>
                <option value="image">{{ t('assets.images') }}</option>
                <option value="video">{{ t('assets.videos') }}</option>
                <option value="document">{{ t('assets.documents') }}</option>
              </select>

              <!-- Sort -->
              <select
                v-model="sortBy"
                class="border border-gray-300 rounded-lg px-4 py-2 focus:ring-primary-500 focus:border-primary-500"
                @change="applyFilters"
              >
                <option value="created_at">{{ t('assets.sort_newest') }}</option>
                <option value="filename">{{ t('assets.sort_name') }}</option>
                <option value="size">{{ t('assets.sort_size') }}</option>
              </select>
            </div>

            <!-- View Toggle -->
            <div class="flex items-center space-x-2 ml-4">
              <button
                @click="viewMode = 'grid'"
                :class="[
                  'p-2 rounded-md',
                  viewMode === 'grid'
                    ? 'bg-primary-100 text-primary-600'
                    : 'text-gray-400 hover:text-gray-600'
                ]"
              >
                <Squares2X2Icon class="w-5 h-5" />
              </button>
              <button
                @click="viewMode = 'list'"
                :class="[
                  'p-2 rounded-md',
                  viewMode === 'list'
                    ? 'bg-primary-100 text-primary-600'
                    : 'text-gray-400 hover:text-gray-600'
                ]"
              >
                <ListBulletIcon class="w-5 h-5" />
              </button>
            </div>
          </div>

          <!-- Bulk Actions -->
          <div v-if="selectedAssets.length > 0" class="bg-blue-50 border-b border-blue-100 px-4 py-3">
            <div class="flex items-center justify-between">
              <span class="text-sm text-blue-700 font-medium">
                {{ t('assets.selected_count', { count: selectedAssets.length }) }}
              </span>
              <div class="flex items-center space-x-2">
                <button
                  @click="bulkMove"
                  class="px-3 py-1.5 text-sm text-blue-700 hover:bg-blue-100 rounded-md"
                >
                  {{ t('assets.move') }}
                </button>
                <button
                  @click="bulkDelete"
                  class="px-3 py-1.5 text-sm text-red-700 hover:bg-red-100 rounded-md"
                >
                  {{ t('common.delete') }}
                </button>
                <button
                  @click="selectedAssets = []"
                  class="px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-100 rounded-md"
                >
                  {{ t('common.cancel') }}
                </button>
              </div>
            </div>
          </div>

          <!-- Grid View -->
          <div v-if="viewMode === 'grid'" class="p-4">
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
              <AssetCard
                v-for="asset in assets.data"
                :key="asset.id"
                :asset="asset"
                :selected="selectedAssets.includes(asset.id)"
                @select="toggleSelect(asset.id)"
                @click="viewAsset(asset)"
                @edit="editAsset(asset)"
                @delete="deleteAsset(asset)"
              />
            </div>
          </div>

          <!-- List View -->
          <AssetList
            v-else
            :assets="assets.data"
            :selected="selectedAssets"
            @select="toggleSelect"
            @select-all="toggleSelectAll"
            @view="viewAsset"
            @edit="editAsset"
            @delete="deleteAsset"
          />

          <!-- Empty State -->
          <div v-if="assets.data.length === 0" class="text-center py-16 px-4">
            <PhotoIcon class="mx-auto h-16 w-16 text-gray-300" />
            <h3 class="mt-4 text-lg font-medium text-gray-900">{{ t('assets.no_assets') }}</h3>
            <p class="mt-2 text-sm text-gray-500">{{ t('assets.no_assets_description') }}</p>
          </div>

          <!-- Pagination -->
          <div v-if="assets.data.length > 0" class="border-t border-gray-200 px-4 py-3">
            <pagination :meta="assets.meta" />
          </div>
        </div>
      </div>
    </div>

    <!-- Asset Viewer Modal -->
    <AssetViewer
      v-if="viewingAsset"
      :asset="viewingAsset"
      @close="viewingAsset = null"
      @edit="editAsset"
      @delete="deleteAsset"
      @updated="refreshAssets"
    />

    <!-- Asset Editor Modal -->
    <AssetEditor
      v-if="editingAsset"
      :asset="editingAsset"
      @close="editingAsset = null"
      @updated="handleAssetUpdated"
    />
  </admin-layout>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import { router, usePage } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { useConfirm } from '@/composables/use-confirm';
import { useNotification } from '@/composables/use-notification';
import { useDebounceFn } from '@vueuse/core';
import {
  MagnifyingGlassIcon,
  Squares2X2Icon,
  ListBulletIcon,
  PhotoIcon,
} from '@heroicons/vue/24/outline';
import AssetCard from './components/AssetCard.vue';
import AssetList from './components/AssetList.vue';
import AssetViewer from './components/AssetViewer.vue';
import AssetEditor from './components/AssetEditor.vue';
import AssetUploader from './components/AssetUploader.vue';
import FolderTree from './components/FolderTree.vue';

interface Asset {
  id: number;
  container: string;
  folder: string;
  path: string;
  basename: string;
  filename: string;
  extension: string;
  mime_type: string;
  type: string;
  size: number;
  size_human: string;
  width: number | null;
  height: number | null;
  url: string;
  meta: Record<string, any>;
  is_image: boolean;
  is_video: boolean;
  is_audio: boolean;
  is_document: boolean;
  created_at: string;
  updated_at: string;
}

interface Container {
  id: number;
  handle: string;
  title: string;
  assets_count: number;
}

interface Props {
  containers: Container[];
  assets: {
    data: Asset[];
    meta: any;
  };
  filters: {
    container?: string;
    folder?: string;
    type?: string;
    search?: string;
    sort_by?: string;
  };
}

const props = defineProps<Props>();
const { t } = useTranslations();
const { confirm } = useConfirm();
const { notify } = useNotification();

const currentContainer = ref(props.filters.container || props.containers[0]?.handle || 'images');
const currentFolder = ref(props.filters.folder || '');
const searchQuery = ref(props.filters.search || '');
const filterType = ref(props.filters.type || '');
const sortBy = ref(props.filters.sort_by || 'created_at');
const viewMode = ref<'grid' | 'list'>('grid');

const selectedAssets = ref<number[]>([]);
const viewingAsset = ref<Asset | null>(null);
const editingAsset = ref<Asset | null>(null);

const selectContainer = (handle: string) => {
  currentContainer.value = handle;
  currentFolder.value = '';
  applyFilters();
};

const selectFolder = (folder: string) => {
  currentFolder.value = folder;
  applyFilters();
};

const applyFilters = () => {
  const params: Record<string, any> = {
    container: currentContainer.value,
  };

  if (currentFolder.value) params.folder = currentFolder.value;
  if (filterType.value) params.type = filterType.value;
  if (searchQuery.value) params.search = searchQuery.value;
  if (sortBy.value) params.sort_by = sortBy.value;

  router.get(route('admin.assets.index'), params, {
    preserveState: true,
    preserveScroll: true,
  });
};

const debouncedSearch = useDebounceFn(() => {
  applyFilters();
}, 300);

const toggleSelect = (id: number) => {
  const index = selectedAssets.value.indexOf(id);
  if (index > -1) {
    selectedAssets.value.splice(index, 1);
  } else {
    selectedAssets.value.push(id);
  }
};

const toggleSelectAll = (selected: boolean) => {
  selectedAssets.value = selected ? props.assets.data.map((a) => a.id) : [];
};

const viewAsset = (asset: Asset) => {
  viewingAsset.value = asset;
};

const editAsset = (asset: Asset) => {
  viewingAsset.value = null;
  editingAsset.value = asset;
};

const deleteAsset = async (asset: Asset) => {
  const confirmed = await confirm({
    title: t('assets.delete_confirm_title'),
    message: t('assets.delete_confirm_message', { name: asset.filename }),
    confirmText: t('common.delete'),
    confirmVariant: 'danger',
  });

  if (confirmed) {
    router.delete(route('api.admin.assets.destroy', asset.id), {
      onSuccess: () => {
        notify.success(t('assets.deleted_successfully'));
        refreshAssets();
      },
      onError: () => {
        notify.error(t('common.error_occurred'));
      },
    });
  }
};

const bulkDelete = async () => {
  const confirmed = await confirm({
    title: t('assets.bulk_delete_confirm_title'),
    message: t('assets.bulk_delete_confirm_message', { count: selectedAssets.value.length }),
    confirmText: t('common.delete'),
    confirmVariant: 'danger',
  });

  if (confirmed) {
    router.post(
      route('api.admin.assets.bulk.delete'),
      { ids: selectedAssets.value },
      {
        onSuccess: () => {
          notify.success(t('assets.bulk_deleted_successfully'));
          selectedAssets.value = [];
          refreshAssets();
        },
        onError: () => {
          notify.error(t('common.error_occurred'));
        },
      }
    );
  }
};

const bulkMove = () => {
  // TODO: Implement bulk move modal
  notify.info('Bulk move coming soon');
};

const handleAssetUpdated = (asset: Asset) => {
  editingAsset.value = null;
  notify.success(t('assets.updated_successfully'));
  refreshAssets();
};

const refreshAssets = () => {
  router.reload({ preserveScroll: true, preserveState: true });
};
</script>
