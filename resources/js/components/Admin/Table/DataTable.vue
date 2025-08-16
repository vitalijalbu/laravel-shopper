<template>
  <div class="bg-white shadow-sm ring-1 ring-gray-900/5 rounded-lg">
    <!-- Table header with search and filters -->
    <div class="px-4 py-5 sm:p-6 border-b border-gray-200">
      <div
        class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-4 sm:space-y-0"
      >
        <!-- Left side: Title and description -->
        <div>
          <h3 v-if="title" class="text-lg font-medium text-gray-900">
            {{ title }}
          </h3>
          <p v-if="description" class="mt-1 text-sm text-gray-500">
            {{ description }}
          </p>
        </div>

        <!-- Right side: Search and actions -->
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div v-if="searchable" class="relative">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="searchPlaceholder"
              class="block w-full pl-10 pr-3 py-2 border border-gray-300 rounded-md leading-5 bg-white placeholder-gray-500 focus:outline-none focus:ring-1 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
            />
            <div
              class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none"
            >
              <svg
                class="h-5 w-5 text-gray-400"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
          </div>

          <!-- Filters -->
          <select
            v-for="filter in filters"
            :key="filter.key"
            v-model="activeFilters[filter.key]"
            class="block w-full pl-3 pr-10 py-2 text-base border border-gray-300 focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md"
          >
            <option value="">{{ filter.placeholder }}</option>
            <option
              v-for="option in filter.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <!-- Actions -->
          <div v-if="actions.length" class="flex space-x-2">
            <component
              v-for="action in actions"
              :key="action.key"
              :is="action.href ? Link : 'button'"
              :href="action.href"
              :type="action.href ? undefined : action.type || 'button'"
              :class="getActionClass(action)"
              @click="action.onClick"
            >
              <component
                v-if="action.icon"
                :is="action.icon"
                class="w-4 h-4"
                :class="{ 'mr-2': action.label }"
              />
              {{ action.label }}
            </component>
          </div>
        </div>
      </div>
    </div>

    <!-- Loading state -->
    <div v-if="loading" class="px-4 py-12 text-center">
      <div class="inline-flex items-center">
        <div
          class="animate-spin rounded-full h-6 w-6 border-b-2 border-indigo-600"
        ></div>
        <span class="ml-2 text-gray-600">Loading...</span>
      </div>
    </div>

    <!-- Empty state -->
    <div v-else-if="!filteredData.length" class="px-4 py-12 text-center">
      <svg
        class="mx-auto h-12 w-12 text-gray-400"
        stroke="currentColor"
        fill="none"
        viewBox="0 0 48 48"
      >
        <path
          d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02"
          stroke-width="2"
          stroke-linecap="round"
          stroke-linejoin="round"
        />
      </svg>
      <h3 class="mt-2 text-sm font-medium text-gray-900">
        {{ emptyStateTitle }}
      </h3>
      <p class="mt-1 text-sm text-gray-500">{{ emptyStateMessage }}</p>
    </div>

    <!-- Table -->
    <div v-else class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <!-- Checkbox column -->
            <th
              v-if="selectable"
              scope="col"
              class="relative w-12 px-6 sm:w-16 sm:px-8"
            >
              <input
                type="checkbox"
                :checked="isAllSelected"
                :indeterminate="isIndeterminate"
                @change="toggleSelectAll"
                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
              />
            </th>

            <!-- Column headers -->
            <th
              v-for="column in columns"
              :key="column.key"
              scope="col"
              :class="[
                'px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider',
                column.sortable ? 'cursor-pointer hover:bg-gray-100' : '',
                column.class || '',
              ]"
              @click="column.sortable ? sort(column.key) : null"
            >
              <div class="flex items-center space-x-1">
                <span>{{ column.label }}</span>
                <svg
                  v-if="column.sortable"
                  class="w-4 h-4"
                  :class="{
                    'text-indigo-600': sortColumn === column.key,
                    'text-gray-400': sortColumn !== column.key,
                  }"
                  fill="currentColor"
                  viewBox="0 0 20 20"
                >
                  <path
                    v-if="sortColumn === column.key && sortDirection === 'asc'"
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                  />
                  <path
                    v-else-if="
                      sortColumn === column.key && sortDirection === 'desc'
                    "
                    fill-rule="evenodd"
                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                    clip-rule="evenodd"
                  />
                  <path
                    v-else
                    fill-rule="evenodd"
                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                    clip-rule="evenodd"
                    opacity="0.5"
                  />
                </svg>
              </div>
            </th>

            <!-- Actions column -->
            <th v-if="rowActions.length" scope="col" class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>

        <tbody class="bg-white divide-y divide-gray-200">
          <tr
            v-for="(item, index) in paginatedData"
            :key="getRowKey(item, index)"
            :class="[
              'hover:bg-gray-50',
              selectedItems.includes(getRowKey(item, index))
                ? 'bg-gray-50'
                : '',
            ]"
          >
            <!-- Checkbox column -->
            <td v-if="selectable" class="relative w-12 px-6 sm:w-16 sm:px-8">
              <input
                type="checkbox"
                :value="getRowKey(item, index)"
                v-model="selectedItems"
                class="absolute left-4 top-1/2 -mt-2 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
              />
            </td>

            <!-- Data columns -->
            <td
              v-for="column in columns"
              :key="column.key"
              :class="[
                'px-6 py-4 whitespace-nowrap text-sm',
                column.class || '',
              ]"
            >
              <!-- Custom column slot -->
              <slot
                v-if="$slots[`column-${column.key}`]"
                :name="`column-${column.key}`"
                :item="item"
                :value="getNestedValue(item, column.key)"
                :column="column"
              />

              <!-- Default rendering -->
              <template v-else>
                <component
                  v-if="column.component"
                  :is="column.component"
                  :item="item"
                  :value="getNestedValue(item, column.key)"
                  :column="column"
                />
                <span v-else-if="column.format">
                  {{
                    formatValue(getNestedValue(item, column.key), column.format)
                  }}
                </span>
                <span v-else class="text-gray-900">
                  {{ getNestedValue(item, column.key) }}
                </span>
              </template>
            </td>

            <!-- Actions column -->
            <td
              v-if="rowActions.length"
              class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6"
            >
              <div class="flex items-center justify-end space-x-2">
                <component
                  v-for="action in getVisibleRowActions(item)"
                  :key="action.key"
                  :is="action.href ? Link : 'button'"
                  :href="
                    typeof action.href === 'function'
                      ? action.href(item)
                      : action.href
                  "
                  :type="action.href ? undefined : action.type || 'button'"
                  :class="getRowActionClass(action)"
                  @click="action.onClick ? action.onClick(item) : null"
                >
                  <component
                    v-if="action.icon"
                    :is="action.icon"
                    class="w-4 h-4"
                  />
                  {{ action.label }}
                </component>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div
      v-if="paginated && totalPages > 1"
      class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6"
    >
      <div class="flex-1 flex justify-between sm:hidden">
        <button
          @click="previousPage"
          :disabled="currentPage === 1"
          class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          Previous
        </button>
        <button
          @click="nextPage"
          :disabled="currentPage === totalPages"
          class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 disabled:opacity-50"
        >
          Next
        </button>
      </div>
      <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Showing
            <span class="font-medium">{{ startIndex }}</span>
            to
            <span class="font-medium">{{ endIndex }}</span>
            of
            <span class="font-medium">{{ filteredData.length }}</span>
            results
          </p>
        </div>
        <div>
          <nav
            class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px"
            aria-label="Pagination"
          >
            <button
              @click="previousPage"
              :disabled="currentPage === 1"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">Previous</span>
              <svg
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
            <button
              v-for="page in visiblePages"
              :key="page"
              @click="goToPage(page)"
              :class="[
                'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                page === currentPage
                  ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                  : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50',
              ]"
            >
              {{ page }}
            </button>
            <button
              @click="nextPage"
              :disabled="currentPage === totalPages"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50"
            >
              <span class="sr-only">Next</span>
              <svg
                class="h-5 w-5"
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20"
                fill="currentColor"
              >
                <path
                  fill-rule="evenodd"
                  d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";
import { Link } from "@inertiajs/vue3";

// Props
const props = defineProps({
  title: String,
  description: String,
  data: {
    type: Array,
    required: true,
  },
  columns: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  searchPlaceholder: {
    type: String,
    default: "Search...",
  },
  sortable: {
    type: Boolean,
    default: true,
  },
  paginated: {
    type: Boolean,
    default: true,
  },
  perPage: {
    type: Number,
    default: 10,
  },
  selectable: {
    type: Boolean,
    default: false,
  },
  rowKey: {
    type: String,
    default: "id",
  },
  actions: {
    type: Array,
    default: () => [],
  },
  rowActions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Array,
    default: () => [],
  },
  emptyStateTitle: {
    type: String,
    default: "No data found",
  },
  emptyStateMessage: {
    type: String,
    default: "Get started by creating your first record.",
  },
});

// Emits
const emit = defineEmits([
  "selection-change",
  "sort-change",
  "filter-change",
  "search",
]);

// State
const searchQuery = ref("");
const sortColumn = ref(null);
const sortDirection = ref("asc");
const currentPage = ref(1);
const selectedItems = ref([]);
const activeFilters = ref({});

// Initialize filters
props.filters.forEach((filter) => {
  activeFilters.value[filter.key] = "";
});

// Computed
const filteredData = computed(() => {
  let result = [...props.data];

  // Apply search
  if (searchQuery.value) {
    const query = searchQuery.value.toLowerCase();
    result = result.filter((item) => {
      return props.columns.some((column) => {
        const value = getNestedValue(item, column.key);
        return String(value).toLowerCase().includes(query);
      });
    });
  }

  // Apply filters
  Object.entries(activeFilters.value).forEach(([key, value]) => {
    if (value) {
      result = result.filter((item) => {
        const itemValue = getNestedValue(item, key);
        return String(itemValue) === String(value);
      });
    }
  });

  // Apply sorting
  if (sortColumn.value) {
    result.sort((a, b) => {
      const aVal = getNestedValue(a, sortColumn.value);
      const bVal = getNestedValue(b, sortColumn.value);

      let comparison = 0;
      if (aVal < bVal) comparison = -1;
      if (aVal > bVal) comparison = 1;

      return sortDirection.value === "desc" ? -comparison : comparison;
    });
  }

  return result;
});

const totalPages = computed(() => {
  return props.paginated
    ? Math.ceil(filteredData.value.length / props.perPage)
    : 1;
});

const paginatedData = computed(() => {
  if (!props.paginated) return filteredData.value;

  const start = (currentPage.value - 1) * props.perPage;
  const end = start + props.perPage;
  return filteredData.value.slice(start, end);
});

const startIndex = computed(() => {
  return (currentPage.value - 1) * props.perPage + 1;
});

const endIndex = computed(() => {
  const end = currentPage.value * props.perPage;
  return Math.min(end, filteredData.value.length);
});

const visiblePages = computed(() => {
  const pages = [];
  const totalPagesCount = totalPages.value;
  const current = currentPage.value;

  if (totalPagesCount <= 7) {
    for (let i = 1; i <= totalPagesCount; i++) {
      pages.push(i);
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i);
      }
    } else if (current >= totalPagesCount - 3) {
      for (let i = totalPagesCount - 4; i <= totalPagesCount; i++) {
        pages.push(i);
      }
    } else {
      for (let i = current - 2; i <= current + 2; i++) {
        pages.push(i);
      }
    }
  }

  return pages;
});

const isAllSelected = computed(() => {
  return (
    paginatedData.value.length > 0 &&
    selectedItems.value.length === paginatedData.value.length
  );
});

const isIndeterminate = computed(() => {
  return (
    selectedItems.value.length > 0 &&
    selectedItems.value.length < paginatedData.value.length
  );
});

// Methods
const getRowKey = (item, index) => {
  return item[props.rowKey] || index;
};

const getNestedValue = (obj, path) => {
  return path.split(".").reduce((value, key) => value?.[key], obj);
};

const formatValue = (value, format) => {
  switch (format) {
    case "currency":
      return new Intl.NumberFormat("en-US", {
        style: "currency",
        currency: "USD",
      }).format(value);
    case "date":
      return new Date(value).toLocaleDateString();
    case "datetime":
      return new Date(value).toLocaleString();
    case "number":
      return new Intl.NumberFormat().format(value);
    default:
      return value;
  }
};

const sort = (column) => {
  if (sortColumn.value === column) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortColumn.value = column;
    sortDirection.value = "asc";
  }

  emit("sort-change", {
    column: sortColumn.value,
    direction: sortDirection.value,
  });
};

const toggleSelectAll = () => {
  if (isAllSelected.value) {
    selectedItems.value = [];
  } else {
    selectedItems.value = paginatedData.value.map((item, index) =>
      getRowKey(item, index),
    );
  }
};

const previousPage = () => {
  if (currentPage.value > 1) {
    currentPage.value--;
  }
};

const nextPage = () => {
  if (currentPage.value < totalPages.value) {
    currentPage.value++;
  }
};

const goToPage = (page) => {
  currentPage.value = page;
};

const getActionClass = (action) => {
  const baseClass =
    "inline-flex items-center px-3 py-2 border text-xs font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2";

  const variants = {
    primary:
      "border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500",
    secondary:
      "border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500",
  };

  return `${baseClass} ${variants[action.variant] || variants.secondary}`;
};

const getRowActionClass = (action) => {
  const baseClass = "text-xs font-medium";

  const variants = {
    primary: "text-indigo-600 hover:text-indigo-900",
    danger: "text-red-600 hover:text-red-900",
    secondary: "text-gray-600 hover:text-gray-900",
  };

  return `${baseClass} ${variants[action.variant] || variants.primary}`;
};

const getVisibleRowActions = (item) => {
  return props.rowActions.filter((action) => {
    return typeof action.visible === "function"
      ? action.visible(item)
      : action.visible !== false;
  });
};

// Watchers
watch(
  selectedItems,
  (newSelection) => {
    emit("selection-change", newSelection);
  },
  { deep: true },
);

watch(
  activeFilters,
  (newFilters) => {
    emit("filter-change", newFilters);
    currentPage.value = 1;
  },
  { deep: true },
);

watch(searchQuery, (newQuery) => {
  emit("search", newQuery);
  currentPage.value = 1;
});

watch(
  () => props.data,
  () => {
    currentPage.value = 1;
    selectedItems.value = [];
  },
);
</script>
