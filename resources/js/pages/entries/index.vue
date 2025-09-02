<template>
  <page
    :title="collection.title"
    :subtitle="
      collection.description || `Manage ${collection.title.toLowerCase()}`
    "
    :breadcrumbs="breadcrumbs"
    :tabs="tabs"
    :active-tab="activeTab"
    @tab-change="changeTab"
  >
    <template #actions>
      <div class="entry-actions">
        <!-- Bulk Actions (when items selected) -->
        <div v-if="selectedEntries.length > 0" class="bulk-actions">
          <span class="selection-count"
            >{{ selectedEntries.length }} selected</span
          >

          <button
            class="btn btn-outline btn-sm"
            @click="bulkPublish"
            v-if="canPublish"
          >
            Publish
          </button>

          <button
            class="btn btn-outline btn-sm"
            @click="bulkUnpublish"
            v-if="canPublish"
          >
            Unpublish
          </button>

          <button
            class="btn btn-danger btn-sm"
            @click="bulkDelete"
            v-if="canDelete"
          >
            Delete
          </button>
        </div>

        <!-- Regular Actions -->
        <div v-else class="regular-actions">
          <button
            class="btn btn-outline"
            @click="exportEntries"
            v-if="canExport"
          >
            <icon name="download" class="btn-icon" />
            Export
          </button>

          <button class="btn btn-primary" @click="createEntry" v-if="canCreate">
            <icon name="plus" class="btn-icon" />
            Create {{ collection.singular || "Entry" }}
          </button>
        </div>
      </div>
    </template>

    <!-- Entries Listing -->
    <div class="entries-container">
      <div class="entries-toolbar">
        <!-- Search -->
        <div class="search-section">
          <div class="search-input-wrapper">
            <input
              v-model="searchQuery"
              type="text"
              :placeholder="`Search ${collection.title.toLowerCase()}...`"
              class="search-input"
              @input="performSearch"
            />
            <icon name="search" class="search-icon" />
          </div>
        </div>

        <!-- Filters -->
        <div class="filters-section">
          <!-- Status Filter -->
          <div class="filter-group">
            <label class="filter-label">Status</label>
            <select
              v-model="filters.status"
              class="filter-select"
              @change="applyFilters"
            >
              <option value="">All Status</option>
              <option value="published">Published</option>
              <option value="draft">Draft</option>
              <option value="scheduled">Scheduled</option>
            </select>
          </div>

          <!-- Site Filter (multisite) -->
          <div v-if="isMultisite" class="filter-group">
            <label class="filter-label">Site</label>
            <select
              v-model="filters.site"
              class="filter-select"
              @change="applyFilters"
            >
              <option value="">All Sites</option>
              <option
                v-for="site in collection.sites"
                :key="site"
                :value="site"
              >
                {{ getSiteName(site) }}
              </option>
            </select>
          </div>

          <!-- Collection-specific filters -->
          <div v-if="isProductCollection" class="filter-group">
            <label class="filter-label">Stock Status</label>
            <select
              v-model="filters.stock_status"
              class="filter-select"
              @change="applyFilters"
            >
              <option value="">All Stock</option>
              <option value="in_stock">In Stock</option>
              <option value="low_stock">Low Stock</option>
              <option value="out_of_stock">Out of Stock</option>
            </select>
          </div>

          <div v-if="isOrderCollection" class="filter-group">
            <label class="filter-label">Order Status</label>
            <select
              v-model="filters.order_status"
              class="filter-select"
              @change="applyFilters"
            >
              <option value="">All Orders</option>
              <option value="pending">Pending</option>
              <option value="processing">Processing</option>
              <option value="shipped">Shipped</option>
              <option value="delivered">Delivered</option>
              <option value="cancelled">Cancelled</option>
            </select>
          </div>

          <!-- Clear Filters -->
          <button
            v-if="hasActiveFilters"
            class="clear-filters-btn"
            @click="clearFilters"
          >
            Clear Filters
          </button>
        </div>

        <!-- View Options -->
        <div class="view-options">
          <div class="view-toggle">
            <button
              class="view-btn"
              :class="{ active: viewMode === 'table' }"
              @click="viewMode = 'table'"
            >
              <icon name="table" />
            </button>
            <button
              class="view-btn"
              :class="{ active: viewMode === 'grid' }"
              @click="viewMode = 'grid'"
            >
              <icon name="grid" />
            </button>
          </div>

          <div class="per-page-selector">
            <select v-model="perPage" @change="loadEntries">
              <option value="20">20 per page</option>
              <option value="50">50 per page</option>
              <option value="100">100 per page</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Data Table View -->
      <data-table
        v-if="viewMode === 'table'"
        :items="entries"
        :columns="tableColumns"
        :loading="loading"
        :selectable="true"
        :sortable="true"
        :paginated="true"
        :per-page="perPage"
        :total-items="totalEntries"
        :current-page="currentPage"
        v-model:selected="selectedEntries"
        @sort="handleSort"
        @page-change="handlePageChange"
        @row-click="editEntry"
      >
        <!-- Custom cell templates -->
        <template #cell-title="{ item, value }">
          <div class="entry-title-cell">
            <router-link
              :to="`/cp/collections/${collection.handle}/entries/${item.id}`"
              class="entry-title-link"
            >
              {{ value }}
            </router-link>
            <div v-if="item.slug" class="entry-slug">{{ item.slug }}</div>
          </div>
        </template>

        <template #cell-status="{ item, value }">
          <span :class="['status-badge', `status-${value}`]">
            <span class="status-dot"></span>
            {{ formatStatus(value) }}
          </span>
        </template>

        <template #cell-price="{ item, value }" v-if="isProductCollection">
          <span class="price-cell">
            {{ formatPrice(value) }}
          </span>
        </template>

        <template #cell-inventory="{ item, value }" v-if="isProductCollection">
          <span :class="['inventory-cell', getInventoryClass(value)]">
            {{ value || 0 }}
          </span>
        </template>

        <template #cell-total="{ item, value }" v-if="isOrderCollection">
          <span class="total-cell">
            {{ formatPrice(value) }}
          </span>
        </template>

        <template #row-actions="{ item }">
          <div class="row-actions">
            <button
              class="action-btn"
              @click.stop="editEntry(item)"
              title="Edit"
            >
              <icon name="pencil" />
            </button>

            <button
              v-if="item.url"
              class="action-btn"
              @click.stop="viewEntry(item)"
              title="View"
            >
              <icon name="eye" />
            </button>

            <button
              class="action-btn action-danger"
              @click.stop="deleteEntry(item)"
              title="Delete"
              v-if="canDelete"
            >
              <icon name="trash" />
            </button>
          </div>
        </template>
      </data-table>

      <!-- Grid View -->
      <div v-else-if="viewMode === 'grid'" class="entries-grid">
        <entry-card
          v-for="entry in entries"
          :key="entry.id"
          :entry="entry"
          :collection="collection"
          @edit="editEntry"
          @delete="deleteEntry"
          @view="viewEntry"
        />
      </div>

      <!-- Empty State -->
      <div v-if="!loading && entries.length === 0" class="empty-entries">
        <div class="empty-icon">
          <icon
            :name="collection.icon || 'document'"
            class="empty-state-icon"
          />
        </div>
        <h3 class="empty-title">No {{ collection.title.toLowerCase() }} yet</h3>
        <p class="empty-description">
          Create your first
          {{ collection.singular?.toLowerCase() || "entry" }} to get started.
        </p>
        <button class="btn btn-primary" @click="createEntry" v-if="canCreate">
          Create {{ collection.singular || "Entry" }}
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="loading-entries">
        <div class="loading-spinner"></div>
        <p>Loading {{ collection.title.toLowerCase() }}...</p>
      </div>
    </div>

    <!-- Delete Confirmation -->
    <confirm-modal
      v-if="showDeleteModal"
      :title="`Delete ${deletingEntry?.title || 'Entry'}?`"
      message="This action cannot be undone."
      danger
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </page>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useShopperStore } from "../stores/shopper";
import Page from "../components/page.vue";
import DataTable from "../components/data-table.vue";
import EntryCard from "../components/entry-card.vue";
import ConfirmModal from "../components/confirm-modal.vue";

const page = usePage();
const shopperStore = useShopperStore();

// Props from controller
const props = defineProps({
  collection: {
    type: Object,
    required: true,
  },
  entries: {
    type: Array,
    default: () => [],
  },
});

const collectionHandle = props.collection.handle;

// State
const selectedEntries = ref([]);
const loading = ref(false);
const searchQuery = ref("");
const viewMode = ref("table");
const currentPage = ref(1);
const perPage = ref(20);
const totalEntries = ref(0);
const showDeleteModal = ref(false);
const deletingEntry = ref(null);
const activeTab = ref("entries");

const filters = ref({
  status: "",
  site: "",
  stock_status: "",
  order_status: "",
});

const sortBy = ref("created_at");
const sortDirection = ref("desc");

// Computed
const breadcrumbs = computed(() => [
  { title: "Control Panel", url: "/cp" },
  { title: "Collections", url: "/cp/collections" },
  { title: collection.value.title, url: `/cp/collections/${collectionHandle}` },
]);

const tabs = computed(() => [
  { id: "entries", title: "Entries" },
  { id: "blueprint", title: "Blueprint" },
  { id: "settings", title: "Settings" },
]);

const isMultisite = computed(() => {
  return collection.value.sites && collection.value.sites.length > 1;
});

const isProductCollection = computed(() => {
  return collectionHandle === "products";
});

const isOrderCollection = computed(() => {
  return collectionHandle === "orders";
});

const hasActiveFilters = computed(() => {
  return Object.values(filters.value).some((filter) => filter !== "");
});

const canCreate = computed(() => shopperStore.canEdit("entries"));
const canDelete = computed(() => shopperStore.canDelete("entries"));
const canPublish = computed(() => shopperStore.canEdit("entries"));
const canExport = computed(() => shopperStore.canView("entries"));

const tableColumns = computed(() => {
  const baseColumns = [
    { key: "title", title: "Title", sortable: true },
    { key: "status", title: "Status", sortable: true, align: "center" },
  ];

  if (isProductCollection.value) {
    baseColumns.push(
      { key: "sku", title: "SKU", sortable: true },
      { key: "price", title: "Price", sortable: true, align: "right" },
      { key: "inventory", title: "Stock", sortable: true, align: "center" },
    );
  } else if (isOrderCollection.value) {
    baseColumns.push(
      { key: "order_number", title: "Order #", sortable: true },
      { key: "customer_name", title: "Customer", sortable: true },
      { key: "total", title: "Total", sortable: true, align: "right" },
    );
  }

  baseColumns.push({
    key: "updated_at",
    title: "Last Modified",
    sortable: true,
  });

  return baseColumns;
});

// Methods
const loadEntries = async () => {
  try {
    loading.value = true;

    const params = {
      page: currentPage.value,
      per_page: perPage.value,
      search: searchQuery.value,
      sort_by: sortBy.value,
      sort_direction: sortDirection.value,
      ...filters.value,
    };

    // This would be an API call in real implementation
    await shopperStore.fetchEntries(collectionHandle);
    const allEntries = shopperStore.getEntriesByCollection(collectionHandle);

    // Apply filters and search (simplified)
    let filteredEntries = allEntries;

    if (searchQuery.value) {
      filteredEntries = shopperStore.searchEntries(
        searchQuery.value,
        collectionHandle,
      );
    }

    if (hasActiveFilters.value) {
      filteredEntries = shopperStore.filterEntries(
        filters.value,
        collectionHandle,
      );
    }

    // Sort
    filteredEntries = shopperStore.sortEntries(
      filteredEntries,
      sortBy.value,
      sortDirection.value,
    );

    // Paginate
    totalEntries.value = filteredEntries.length;
    const start = (currentPage.value - 1) * perPage.value;
    entries.value = filteredEntries.slice(start, start + perPage.value);
  } catch (error) {
    shopperStore.addError(error);
  } finally {
    loading.value = false;
  }
};

const loadCollection = () => {
  // Con Inertia, la collection arriva già come prop dal controller
  // Non serve più caricarla qui
};

const performSearch = () => {
  currentPage.value = 1;
  loadEntries();
};

const applyFilters = () => {
  currentPage.value = 1;
  loadEntries();
};

const clearFilters = () => {
  filters.value = {
    status: "",
    site: "",
    stock_status: "",
    order_status: "",
  };
  currentPage.value = 1;
  loadEntries();
};

const handleSort = (sortConfig) => {
  sortBy.value = sortConfig.column;
  sortDirection.value = sortConfig.direction;
  loadEntries();
};

const handlePageChange = (page) => {
  currentPage.value = page;
  loadEntries();
};

const createEntry = () => {
  router.visit(
    route("cp.collections.entries.create", { collection: collectionHandle }),
  );
};

const editEntry = (entry) => {
  router.visit(
    route("cp.collections.entries.edit", {
      collection: collectionHandle,
      entry: entry.id,
    }),
  );
};

const viewEntry = (entry) => {
  if (entry.url) {
    window.open(entry.url, "_blank");
  }
};

const deleteEntry = (entry) => {
  deletingEntry.value = entry;
  showDeleteModal.value = true;
};

const confirmDelete = async () => {
  try {
    await shopperStore.deleteEntryById(deletingEntry.value.id);
    shopperStore.addToast("Entry deleted successfully", "success");
    loadEntries();
  } catch (error) {
    shopperStore.addError(error);
    shopperStore.addToast("Failed to delete entry", "error");
  } finally {
    showDeleteModal.value = false;
    deletingEntry.value = null;
  }
};

const bulkPublish = async () => {
  // Implement bulk publish
  shopperStore.addToast(
    `${selectedEntries.value.length} entries published`,
    "success",
  );
  selectedEntries.value = [];
  loadEntries();
};

const bulkUnpublish = async () => {
  // Implement bulk unpublish
  shopperStore.addToast(
    `${selectedEntries.value.length} entries unpublished`,
    "success",
  );
  selectedEntries.value = [];
  loadEntries();
};

const bulkDelete = async () => {
  // Implement bulk delete
  if (confirm(`Delete ${selectedEntries.value.length} entries?`)) {
    shopperStore.addToast(
      `${selectedEntries.value.length} entries deleted`,
      "success",
    );
    selectedEntries.value = [];
    loadEntries();
  }
};

const exportEntries = () => {
  // Implement export functionality
  shopperStore.addToast("Export started", "info");
};

const changeTab = (tabId) => {
  activeTab.value = tabId;

  if (tabId === "blueprint") {
    router.visit(
      route("cp.collections.blueprint", { collection: collectionHandle }),
    );
  } else if (tabId === "settings") {
    router.visit(
      route("cp.collections.settings", { collection: collectionHandle }),
    );
  }
};

const formatStatus = (status) => {
  return status.charAt(0).toUpperCase() + status.slice(1);
};

const formatPrice = (price) => {
  if (!price) return "-";
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(price / 100);
};

const getInventoryClass = (inventory) => {
  if (inventory === 0) return "out-of-stock";
  if (inventory <= 10) return "low-stock";
  return "in-stock";
};

const getSiteName = (siteHandle) => {
  // This would get the site name from config
  return siteHandle;
};

// Load data on mount
onMounted(() => {
  // Con Inertia non serve più caricare manualmente
  // loadCollection()
  // loadEntries()
});

// Con Inertia, quando cambia la collection, Laravel reinderizza la pagina
// Non serve più il watch per route.params
</script>

<style scoped>
.entries-container {
  background: white;
  border-radius: 0.5rem;
  overflow: hidden;
}

.entries-toolbar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1.5rem;
  border-bottom: 1px solid #e5e7eb;
  flex-wrap: wrap;
  gap: 1rem;
}

.search-section {
  flex: 1;
  min-width: 300px;
}

.search-input-wrapper {
  position: relative;
}

.search-input {
  width: 100%;
  padding: 0.75rem 1rem;
  padding-left: 2.5rem;
  border: 1px solid #d1d5db;
  border-radius: 0.5rem;
  font-size: 0.875rem;
}

.search-input:focus {
  outline: none;
  border-color: #3b82f6;
  ring: 2px solid #3b82f6;
  ring-opacity: 0.2;
}

.search-icon {
  position: absolute;
  left: 0.75rem;
  top: 50%;
  transform: translateY(-50%);
  width: 16px;
  height: 16px;
  color: #9ca3af;
}

.filters-section {
  display: flex;
  align-items: center;
  gap: 1rem;
  flex-wrap: wrap;
}

.filter-group {
  display: flex;
  flex-direction: column;
  gap: 0.25rem;
}

.filter-label {
  font-size: 0.75rem;
  font-weight: 500;
  color: #6b7280;
  text-transform: uppercase;
  letter-spacing: 0.05em;
}

.filter-select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  min-width: 120px;
}

.clear-filters-btn {
  padding: 0.5rem 1rem;
  background: #f3f4f6;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
  color: #6b7280;
  cursor: pointer;
  transition: all 0.2s;
}

.clear-filters-btn:hover {
  background: #e5e7eb;
}

.view-options {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.view-toggle {
  display: flex;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  overflow: hidden;
}

.view-btn {
  padding: 0.5rem 0.75rem;
  background: white;
  border: none;
  cursor: pointer;
  transition: all 0.2s;
}

.view-btn:hover {
  background: #f9fafb;
}

.view-btn.active {
  background: #3b82f6;
  color: white;
}

.per-page-selector select {
  padding: 0.5rem 0.75rem;
  border: 1px solid #d1d5db;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.entry-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.bulk-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
  padding: 0.5rem 1rem;
  background: #fef3c7;
  border: 1px solid #f59e0b;
  border-radius: 0.5rem;
}

.selection-count {
  font-size: 0.875rem;
  font-weight: 500;
  color: #92400e;
}

.regular-actions {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.entries-grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
  padding: 1.5rem;
}

.empty-entries,
.loading-entries {
  text-align: center;
  padding: 4rem 2rem;
}

.empty-icon {
  margin: 0 auto 1.5rem;
  width: 64px;
  height: 64px;
  background: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-state-icon {
  width: 32px;
  height: 32px;
  color: #9ca3af;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #6b7280;
  margin-bottom: 2rem;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.loading-spinner {
  width: 32px;
  height: 32px;
  border: 3px solid #f3f4f6;
  border-top: 3px solid #3b82f6;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin: 0 auto 1rem;
}

@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* Cell Styles */
.entry-title-cell {
  min-width: 200px;
}

.entry-title-link {
  font-weight: 500;
  color: #1f2937;
  text-decoration: none;
  display: block;
  margin-bottom: 0.25rem;
}

.entry-title-link:hover {
  color: #3b82f6;
}

.entry-slug {
  font-size: 0.75rem;
  color: #6b7280;
  font-family: monospace;
}

.status-badge {
  display: inline-flex;
  align-items: center;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  font-size: 0.75rem;
  font-weight: 500;
  text-transform: capitalize;
}

.status-published {
  background: #dcfce7;
  color: #166534;
}

.status-draft {
  background: #f3f4f6;
  color: #374151;
}

.status-scheduled {
  background: #dbeafe;
  color: #1e40af;
}

.status-dot {
  width: 6px;
  height: 6px;
  border-radius: 50%;
  margin-right: 0.5rem;
  background: currentColor;
}

.price-cell {
  font-weight: 500;
  font-family: monospace;
}

.inventory-cell {
  font-weight: 500;
  padding: 0.25rem 0.5rem;
  border-radius: 0.25rem;
  font-size: 0.875rem;
}

.inventory-cell.in-stock {
  background: #dcfce7;
  color: #166534;
}

.inventory-cell.low-stock {
  background: #fef3c7;
  color: #92400e;
}

.inventory-cell.out-of-stock {
  background: #fee2e2;
  color: #dc2626;
}

.total-cell {
  font-weight: 600;
  font-family: monospace;
}

.row-actions {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: flex-end;
}

.action-btn {
  padding: 0.25rem;
  background: none;
  border: none;
  color: #6b7280;
  cursor: pointer;
  border-radius: 0.25rem;
  transition: all 0.2s;
}

.action-btn:hover {
  background: #f3f4f6;
  color: #374151;
}

.action-danger:hover {
  background: #fee2e2;
  color: #dc2626;
}

.btn-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
}
</style>
