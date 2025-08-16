<template>
  <div
    class="entry-card bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow duration-200 group"
  >
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-start justify-between mb-4">
        <div class="flex-1">
          <div class="flex items-center space-x-2 mb-2">
            <h3 class="text-lg font-medium text-gray-900 truncate">
              {{ entry.title || "Untitled Entry" }}
            </h3>
            <span
              v-if="entry.is_featured"
              class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800"
            >
              ⭐ Featured
            </span>
          </div>
          <p class="text-sm text-gray-500 mb-1">
            {{ entry.collection_title }} • {{ entry.slug || entry.handle }}
          </p>
          <p class="text-xs text-gray-400" v-if="entry.updated_at">
            Last updated {{ formatDate(entry.updated_at) }}
          </p>
        </div>

        <div class="flex items-center space-x-2">
          <!-- Status Badge -->
          <span :class="statusClasses">
            {{ statusLabel }}
          </span>

          <!-- Site Badge -->
          <span
            v-if="entry.site && entry.site !== 'default'"
            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
          >
            {{ entry.site }}
          </span>
        </div>
      </div>

      <!-- Preview Text -->
      <p
        class="text-gray-600 text-sm mb-4 line-clamp-2"
        v-if="entry.excerpt || entry.description"
      >
        {{ entry.excerpt || entry.description }}
      </p>

      <!-- Meta Information -->
      <div class="flex items-center justify-between text-sm text-gray-500 mb-4">
        <div class="flex items-center space-x-4">
          <span v-if="entry.author"> by {{ entry.author }} </span>
          <span v-if="entry.category"> in {{ entry.category }} </span>
          <span v-if="entry.price" class="font-medium text-green-600">
            {{ formatPrice(entry.price) }}
          </span>
        </div>

        <div class="flex items-center space-x-2">
          <span v-if="entry.views" class="flex items-center space-x-1">
            <icon name="eye" :size="12" />
            <span>{{ entry.views }}</span>
          </span>
          <span
            v-if="entry.stock_quantity !== undefined"
            class="flex items-center space-x-1"
            :class="{
              'text-red-600': entry.stock_quantity === 0,
              'text-yellow-600':
                entry.stock_quantity > 0 && entry.stock_quantity <= 10,
              'text-green-600': entry.stock_quantity > 10,
            }"
          >
            <icon name="box" :size="12" />
            <span>{{ entry.stock_quantity }}</span>
          </span>
        </div>
      </div>

      <!-- Actions -->
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-2">
          <!-- Quick Edit Button -->
          <router-link
            :to="`/cp/collections/${entry.collection_handle}/entries/${entry.id}/edit`"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <icon name="pencil" :size="12" class="mr-1" />
            Edit
          </router-link>

          <!-- View Button -->
          <a
            v-if="entry.url"
            :href="entry.url"
            target="_blank"
            class="inline-flex items-center px-3 py-1.5 border border-gray-300 shadow-sm text-xs font-medium rounded text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
          >
            <icon name="eye" :size="12" class="mr-1" />
            View
          </a>
        </div>

        <!-- Entry Date -->
        <span class="text-xs text-gray-400" v-if="entry.published_at">
          {{ formatDate(entry.published_at) }}
        </span>
      </div>
    </div>

    <!-- Thumbnail/Featured Image -->
    <div v-if="entry.featured_image" class="h-48 bg-gray-100 overflow-hidden">
      <img
        :src="entry.featured_image"
        :alt="entry.title"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
      />
    </div>

    <!-- Quick Actions Overlay (on hover) -->
    <div
      class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 absolute inset-0 bg-black bg-opacity-5 flex items-center justify-center"
    >
      <div class="flex items-center space-x-2">
        <router-link
          :to="`/cp/collections/${entry.collection_handle}/entries/${entry.id}/edit`"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
        >
          <icon name="pencil" :size="16" class="mr-1" />
          Quick Edit
        </router-link>

        <button
          @click="$emit('duplicate', entry)"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-gray-600 hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500"
        >
          <icon name="document-text" :size="16" class="mr-1" />
          Duplicate
        </button>

        <button
          @click="$emit('delete', entry)"
          class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          <icon name="trash" :size="16" class="mr-1" />
          Delete
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from "vue";
import Icon from "./icon.vue";

const props = defineProps({
  entry: {
    type: Object,
    required: true,
  },
});

const emit = defineEmits(["duplicate", "delete"]);

const statusClasses = computed(() => {
  const baseClasses =
    "inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium";

  switch (props.entry.status) {
    case "published":
      return `${baseClasses} bg-green-100 text-green-800`;
    case "draft":
      return `${baseClasses} bg-yellow-100 text-yellow-800`;
    case "scheduled":
      return `${baseClasses} bg-blue-100 text-blue-800`;
    case "expired":
      return `${baseClasses} bg-red-100 text-red-800`;
    default:
      return `${baseClasses} bg-gray-100 text-gray-800`;
  }
});

const statusLabel = computed(() => {
  switch (props.entry.status) {
    case "published":
      return "Published";
    case "draft":
      return "Draft";
    case "scheduled":
      return "Scheduled";
    case "expired":
      return "Expired";
    default:
      return props.entry.status || "Unknown";
  }
});

const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const formatPrice = (price) => {
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(price);
};
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.entry-card {
  position: relative;
}

.entry-card:hover .opacity-0 {
  opacity: 1;
}
</style>
