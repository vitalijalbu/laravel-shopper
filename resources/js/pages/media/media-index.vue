<template>
  <admin-layout :title="t('media.title')">
    <template #header>
      <div class="flex justify-between items-center">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">
            {{ t("media.title") }}
          </h1>
          <p class="text-gray-600">{{ t("media.subtitle") }}</p>
        </div>
        <upload-media-button @uploaded="refreshMedia" />
      </div>
    </template>

    <template #actions>
      <div class="flex items-center space-x-4">
        <media-filters
          :collections="collections"
          :types="types"
          :filters="filters"
          @filter="applyFilters"
        />
        <bulk-actions-menu
          :selected-count="selectedMedia.length"
          @bulk-delete="handleBulkDelete"
        />
      </div>
    </template>

    <div class="bg-white shadow-sm rounded-lg overflow-hidden">
      <!-- Media Grid -->
      <media-grid
        :media="media.data"
        :selected="selectedMedia"
        :view-mode="viewMode"
        @select="handleSelectMedia"
        @select-all="handleSelectAll"
        @edit="handleEditMedia"
        @delete="handleDeleteMedia"
      />

      <!-- Pagination -->
      <pagination
        :meta="media.meta"
        :links="media.links"
        class="border-t border-gray-200"
      />
    </div>

    <!-- Edit Media Modal -->
    <edit-media-modal
      v-if="editingMedia"
      :media="editingMedia"
      @close="editingMedia = null"
      @updated="handleMediaUpdated"
    />

    <!-- Media Viewer Modal -->
    <media-viewer-modal
      v-if="viewingMedia"
      :media="viewingMedia"
      @close="viewingMedia = null"
      @edit="handleEditFromViewer"
      @delete="handleDeleteFromViewer"
    />
  </admin-layout>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from "vue";
import { router } from "@inertiajs/vue3";
import { useTranslations } from "@/composables/useTranslations";
import { useConfirm } from "@/composables/use-confirm";
import { useNotification } from "@/composables/use-notification";
import MediaGrid from "./components/media-grid.vue";

interface MediaItem {
  id: number;
  name: string;
  file_name: string;
  mime_type: string;
  size: number;
  collection_name: string;
  url: string;
  conversions: Record<string, string>;
  custom_properties: Record<string, any>;
  created_at: string;
  updated_at: string;
}

interface Props {
  media: {
    data: MediaItem[];
    meta: any;
    links: any;
  };
  collections: string[];
  types: string[];
  filters: Record<string, string>;
}

const props = defineProps<Props>();
const { t } = useTranslations();
const { confirm } = useConfirm();
const { notify } = useNotification();

const selectedMedia = ref<number[]>([]);
const editingMedia = ref<MediaItem | null>(null);
const viewingMedia = ref<MediaItem | null>(null);
const viewMode = ref<"grid" | "list">("grid");

const handleSelectMedia = (mediaId: number, selected: boolean) => {
  if (selected) {
    selectedMedia.value.push(mediaId);
  } else {
    selectedMedia.value = selectedMedia.value.filter((id) => id !== mediaId);
  }
};

const handleSelectAll = (selected: boolean) => {
  if (selected) {
    selectedMedia.value = props.media.data.map((item) => item.id);
  } else {
    selectedMedia.value = [];
  }
};

const handleEditMedia = (media: MediaItem) => {
  editingMedia.value = media;
};

const handleDeleteMedia = async (media: MediaItem) => {
  const confirmed = await confirm({
    title: t("media.delete_confirm_title"),
    message: t("media.delete_confirm_message", { name: media.name }),
    confirmText: t("common.delete"),
    confirmVariant: "danger",
  });

  if (confirmed) {
    router.delete(route("admin.media.destroy", media.id), {
      onSuccess: () => {
        notify.success(t("media.deleted_successfully"));
        refreshMedia();
      },
      onError: (errors) => {
        notify.error(errors.message || t("common.error_occurred"));
      },
    });
  }
};

const handleBulkDelete = async () => {
  const confirmed = await confirm({
    title: t("media.bulk_delete_confirm_title"),
    message: t("media.bulk_delete_confirm_message", {
      count: selectedMedia.value.length,
    }),
    confirmText: t("common.delete"),
    confirmVariant: "danger",
  });

  if (confirmed) {
    router.post(
      route("admin.media.bulk-delete"),
      {
        ids: selectedMedia.value,
      },
      {
        onSuccess: () => {
          notify.success(t("media.bulk_deleted_successfully"));
          selectedMedia.value = [];
          refreshMedia();
        },
        onError: (errors) => {
          notify.error(errors.message || t("common.error_occurred"));
        },
      },
    );
  }
};

const handleMediaUpdated = (updatedMedia: MediaItem) => {
  editingMedia.value = null;
  notify.success(t("media.updated_successfully"));
  refreshMedia();
};

const handleEditFromViewer = (media: MediaItem) => {
  viewingMedia.value = null;
  editingMedia.value = media;
};

const handleDeleteFromViewer = (media: MediaItem) => {
  viewingMedia.value = null;
  handleDeleteMedia(media);
};

const applyFilters = (filters: Record<string, string>) => {
  router.get(route("admin.media.index"), filters, {
    preserveState: true,
    replace: true,
  });
};

const refreshMedia = () => {
  router.reload({ only: ["media"] });
};
</script>
