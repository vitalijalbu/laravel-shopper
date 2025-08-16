<template>
  <div class="data-table-wrapper">
    <!-- Table Header -->
    <div v-if="hasToolbar" class="table-toolbar">
      <div class="toolbar-left">
        <h3 v-if="title" class="table-title">{{ title }}</h3>
        <div v-if="hasSelection" class="selection-info">
          {{ selectedItems.length }} of {{ totalItems }} selected
        </div>
      </div>

      <div class="toolbar-right">
        <slot name="toolbar" />

        <!-- Search -->
        <div v-if="searchable" class="search-input">
          <input
            v-model="searchQuery"
            type="text"
            placeholder="Search..."
            class="search-field"
            @input="handleSearch"
          />
        </div>

        <!-- Filters -->
        <div v-if="filterable" class="filter-dropdown">
          <button class="filter-button" @click="showFilters = !showFilters">
            Filters
            <span v-if="activeFilters > 0" class="filter-badge">{{
              activeFilters
            }}</span>
          </button>
        </div>
      </div>
    </div>

    <!-- Bulk Actions -->
    <div v-if="hasSelection && selectedItems.length > 0" class="bulk-actions">
      <div class="bulk-actions-content">
        <span class="bulk-count">{{ selectedItems.length }} selected</span>
        <div class="bulk-buttons">
          <slot name="bulk-actions" :selected="selectedItems" />
        </div>
      </div>
    </div>

    <!-- Table Container -->
    <div class="table-container">
      <table class="data-table">
        <!-- Table Head -->
        <thead class="table-head">
          <tr class="header-row">
            <th v-if="selectable" class="select-column">
              <input
                type="checkbox"
                class="checkbox"
                :checked="allSelected"
                :indeterminate="someSelected"
                @change="toggleAll"
              />
            </th>

            <th
              v-for="column in columns"
              :key="column.key"
              :class="columnClasses(column)"
              @click="handleSort(column)"
            >
              <div class="column-header">
                <span class="column-title">{{ column.title }}</span>
                <span v-if="column.sortable" class="sort-icon">
                  <svg
                    v-if="sortBy === column.key"
                    class="sort-active"
                    viewBox="0 0 20 20"
                  >
                    <path
                      :d="
                        sortDirection === 'asc'
                          ? 'M5 8l5-5 5 5H5z'
                          : 'M5 12l5 5 5-5H5z'
                      "
                    />
                  </svg>
                  <svg v-else class="sort-inactive" viewBox="0 0 20 20">
                    <path d="M5 8l5-5 5 5H5zM5 12l5 5 5-5H5z" />
                  </svg>
                </span>
              </div>
            </th>

            <th v-if="hasActions" class="actions-column">Actions</th>
          </tr>
        </thead>

        <!-- Table Body -->
        <tbody class="table-body">
          <tr v-if="loading" class="loading-row">
            <td :colspan="totalColumns" class="loading-cell">
              <div class="loading-content">
                <div class="spinner"></div>
                <span>Loading...</span>
              </div>
            </td>
          </tr>

          <tr v-else-if="items.length === 0" class="empty-row">
            <td :colspan="totalColumns" class="empty-cell">
              <div class="empty-state">
                <slot name="empty">
                  <p>No data available</p>
                </slot>
              </div>
            </td>
          </tr>

          <tr
            v-else
            v-for="(item, index) in items"
            :key="getRowKey(item, index)"
            :class="rowClasses(item, index)"
            @click="handleRowClick(item, index)"
          >
            <td v-if="selectable" class="select-cell">
              <input
                type="checkbox"
                class="checkbox"
                :checked="isSelected(item)"
                @change="toggleSelection(item)"
                @click.stop
              />
            </td>

            <td
              v-for="column in columns"
              :key="`${getRowKey(item, index)}-${column.key}`"
              :class="cellClasses(column, item)"
            >
              <slot
                :name="`cell-${column.key}`"
                :item="item"
                :value="getCellValue(item, column.key)"
                :column="column"
                :index="index"
              >
                {{ formatCellValue(item, column) }}
              </slot>
            </td>

            <td v-if="hasActions" class="actions-cell">
              <div class="row-actions">
                <slot name="row-actions" :item="item" :index="index" />
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- Pagination -->
    <div v-if="paginated" class="table-pagination">
      <div class="pagination-info">
        Showing {{ startItem }} to {{ endItem }} of {{ totalItems }} results
      </div>

      <div class="pagination-controls">
        <button
          class="pagination-button"
          :disabled="currentPage <= 1"
          @click="goToPage(currentPage - 1)"
        >
          Previous
        </button>

        <div class="page-numbers">
          <button
            v-for="page in visiblePages"
            :key="page"
            :class="pageButtonClasses(page)"
            @click="goToPage(page)"
          >
            {{ page }}
          </button>
        </div>

        <button
          class="pagination-button"
          :disabled="currentPage >= totalPages"
          @click="goToPage(currentPage + 1)"
        >
          Next
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch } from "vue";

const props = defineProps({
  title: String,
  items: {
    type: Array,
    default: () => [],
  },
  columns: {
    type: Array,
    default: () => [],
  },
  loading: {
    type: Boolean,
    default: false,
  },
  selectable: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: false,
  },
  filterable: {
    type: Boolean,
    default: false,
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
    default: 20,
  },
  totalItems: {
    type: Number,
    default: 0,
  },
  currentPage: {
    type: Number,
    default: 1,
  },
  rowKey: {
    type: String,
    default: "id",
  },
});

const emit = defineEmits([
  "sort",
  "search",
  "filter",
  "page-change",
  "row-click",
  "selection-change",
]);

// State
const selectedItems = ref([]);
const searchQuery = ref("");
const sortBy = ref("");
const sortDirection = ref("asc");
const showFilters = ref(false);

// Computed
const hasToolbar = computed(() => {
  return props.title || props.searchable || props.filterable;
});

const hasSelection = computed(() => {
  return props.selectable && selectedItems.value.length > 0;
});

const hasActions = computed(() => {
  // Check if row-actions slot has content
  return true; // This would need to be properly implemented
});

const totalColumns = computed(() => {
  let count = props.columns.length;
  if (props.selectable) count++;
  if (hasActions.value) count++;
  return count;
});

const allSelected = computed(() => {
  return (
    props.items.length > 0 && selectedItems.value.length === props.items.length
  );
});

const someSelected = computed(() => {
  return (
    selectedItems.value.length > 0 &&
    selectedItems.value.length < props.items.length
  );
});

const activeFilters = computed(() => {
  // Count active filters
  return 0;
});

const totalPages = computed(() => {
  return Math.ceil(props.totalItems / props.perPage);
});

const startItem = computed(() => {
  return (props.currentPage - 1) * props.perPage + 1;
});

const endItem = computed(() => {
  return Math.min(props.currentPage * props.perPage, props.totalItems);
});

const visiblePages = computed(() => {
  const pages = [];
  const maxVisible = 5;
  let start = Math.max(1, props.currentPage - Math.floor(maxVisible / 2));
  let end = Math.min(totalPages.value, start + maxVisible - 1);

  if (end - start + 1 < maxVisible) {
    start = Math.max(1, end - maxVisible + 1);
  }

  for (let i = start; i <= end; i++) {
    pages.push(i);
  }

  return pages;
});

// Methods
const getRowKey = (item, index) => {
  return item[props.rowKey] || index;
};

const getCellValue = (item, key) => {
  return key.split(".").reduce((obj, k) => obj?.[k], item);
};

const formatCellValue = (item, column) => {
  const value = getCellValue(item, column.key);

  if (column.formatter && typeof column.formatter === "function") {
    return column.formatter(value, item);
  }

  return value;
};

const isSelected = (item) => {
  const key = getRowKey(item, -1);
  return selectedItems.value.some(
    (selected) => getRowKey(selected, -1) === key,
  );
};

const toggleSelection = (item) => {
  const key = getRowKey(item, -1);
  const index = selectedItems.value.findIndex(
    (selected) => getRowKey(selected, -1) === key,
  );

  if (index >= 0) {
    selectedItems.value.splice(index, 1);
  } else {
    selectedItems.value.push(item);
  }

  emit("selection-change", selectedItems.value);
};

const toggleAll = () => {
  if (allSelected.value) {
    selectedItems.value = [];
  } else {
    selectedItems.value = [...props.items];
  }

  emit("selection-change", selectedItems.value);
};

const handleSort = (column) => {
  if (!column.sortable && !props.sortable) return;

  if (sortBy.value === column.key) {
    sortDirection.value = sortDirection.value === "asc" ? "desc" : "asc";
  } else {
    sortBy.value = column.key;
    sortDirection.value = "asc";
  }

  emit("sort", {
    column: column.key,
    direction: sortDirection.value,
  });
};

const handleSearch = () => {
  emit("search", searchQuery.value);
};

const handleRowClick = (item, index) => {
  emit("row-click", { item, index });
};

const goToPage = (page) => {
  if (page >= 1 && page <= totalPages.value) {
    emit("page-change", page);
  }
};

// CSS Classes
const columnClasses = (column) => {
  return [
    "table-column",
    `column-${column.key}`,
    {
      sortable: column.sortable || props.sortable,
      sorted: sortBy.value === column.key,
      "text-right": column.align === "right",
      "text-center": column.align === "center",
    },
  ];
};

const cellClasses = (column, item) => {
  return [
    "table-cell",
    `cell-${column.key}`,
    {
      "text-right": column.align === "right",
      "text-center": column.align === "center",
    },
  ];
};

const rowClasses = (item, index) => {
  return [
    "table-row",
    {
      "row-selected": isSelected(item),
      "row-clickable": true,
    },
  ];
};

const pageButtonClasses = (page) => {
  return [
    "page-button",
    {
      "page-active": page === props.currentPage,
    },
  ];
};
</script>

<style scoped>
/* Component styles would go here */
/* This is a simplified version - full styles would be much more extensive */
.data-table-wrapper {
  background: white;
  border-radius: 8px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}

.table-toolbar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem;
  border-bottom: 1px solid #e5e7eb;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
}

.table-head th {
  background: #f9fafb;
  padding: 0.75rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 1px solid #e5e7eb;
}

.table-body td {
  padding: 0.75rem;
  border-bottom: 1px solid #f3f4f6;
}

.table-row:hover {
  background-color: #f9fafb;
}

.loading-content {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  gap: 0.5rem;
}

.spinner {
  width: 20px;
  height: 20px;
  border: 2px solid #e5e7eb;
  border-top: 2px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

.table-pagination {
  display: flex;
  justify-content: between;
  align-items: center;
  padding: 1rem;
  border-top: 1px solid #e5e7eb;
}
</style>
