<template>
  <PageLayout
    title="Products"
    subtitle="Manage your product catalog"
    :breadcrumbs="breadcrumbs"
    :actions="pageActions"
    :tabs="tabs"
    :loading="loading"
    :success="success"
    :error="error"
    @tab-change="handleTabChange"
  >
    <!-- Products List Tab -->
    <template #tab-list>
      <DataTable
        :data="products"
        :columns="productColumns"
        :actions="tableActions"
        :row-actions="rowActions"
        :loading="loading"
        searchable
        selectable
        :filters="filters"
        @selection-change="handleSelectionChange"
        @sort-change="handleSortChange"
      >
        <!-- Custom column: Product Image & Name -->
        <template #column-name="{ item }">
          <div class="flex items-center">
            <div class="h-10 w-10 flex-shrink-0">
              <img
                class="h-10 w-10 rounded-md object-cover"
                :src="item.image || '/placeholder-product.png'"
                :alt="item.name"
              />
            </div>
            <div class="ml-4">
              <div class="text-sm font-medium text-gray-900">
                {{ item.name }}
              </div>
              <div class="text-sm text-gray-500">SKU: {{ item.sku }}</div>
            </div>
          </div>
        </template>

        <!-- Custom column: Status -->
        <template #column-status="{ item }">
          <span
            :class="[
              'inline-flex px-2 py-1 text-xs font-medium rounded-full',
              item.is_active
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800',
            ]"
          >
            {{ item.is_active ? "Active" : "Inactive" }}
          </span>
        </template>

        <!-- Custom column: Stock -->
        <template #column-stock="{ item }">
          <div class="text-sm text-gray-900">
            {{ item.stock_quantity || 0 }}
            <span
              v-if="item.track_quantity && item.stock_quantity <= 10"
              class="ml-1 text-red-500 text-xs"
            >
              Low stock
            </span>
          </div>
        </template>
      </DataTable>
    </template>

    <!-- Create Product Tab -->
    <template #tab-create>
      <div class="max-w-2xl">
        <FormBuilder
          title="Create New Product"
          description="Add a new product to your catalog"
          :fields="productFields"
          v-model="newProduct"
          :errors="formErrors"
          :loading="submitting"
          submit-text="Create Product"
          @submit="createProduct"
          @cancel="resetForm"
        >
          <!-- Custom field: Category selector -->
          <template #field-category_id="{ field, value, update }">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700"
                >Category</label
              >
              <select
                :value="value"
                @change="update($event.target.value)"
                class="block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
              >
                <option value="">Select a category</option>
                <option
                  v-for="category in categories"
                  :key="category.id"
                  :value="category.id"
                >
                  {{ category.name }}
                </option>
              </select>
            </div>
          </template>

          <!-- Custom field: Image upload -->
          <template #field-image="{ field, value, update }">
            <div class="space-y-2">
              <label class="block text-sm font-medium text-gray-700"
                >Product Image</label
              >
              <div
                class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 border-dashed rounded-md"
              >
                <div class="space-y-1 text-center">
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
                  <div class="flex text-sm text-gray-600">
                    <label
                      for="file-upload"
                      class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500"
                    >
                      <span>Upload a file</span>
                      <input
                        id="file-upload"
                        name="file-upload"
                        type="file"
                        accept="image/*"
                        class="sr-only"
                        @change="handleImageUpload($event, update)"
                      />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-gray-500">PNG, JPG, GIF up to 10MB</p>
                </div>
              </div>
            </div>
          </template>
        </FormBuilder>
      </div>
    </template>

    <!-- Import Tab -->
    <template #tab-import>
      <div class="max-w-xl">
        <div class="bg-white rounded-lg border border-gray-200 p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Import Products
          </h3>
          <p class="text-sm text-gray-500 mb-4">
            Upload a CSV file to import multiple products at once.
          </p>

          <div class="space-y-4">
            <div>
              <input
                ref="importFile"
                type="file"
                accept=".csv"
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
                @change="handleFileSelect"
              />
            </div>

            <div class="flex space-x-3">
              <button
                @click="importProducts"
                :disabled="!selectedFile || importing"
                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
              >
                <svg
                  v-if="importing"
                  class="animate-spin -ml-1 mr-2 h-4 w-4 text-white"
                  xmlns="http://www.w3.org/2000/svg"
                  fill="none"
                  viewBox="0 0 24 24"
                >
                  <circle
                    class="opacity-25"
                    cx="12"
                    cy="12"
                    r="10"
                    stroke="currentColor"
                    stroke-width="4"
                  ></circle>
                  <path
                    class="opacity-75"
                    fill="currentColor"
                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                  ></path>
                </svg>
                {{ importing ? "Importing..." : "Import Products" }}
              </button>

              <Link
                href="/admin/products/export-template"
                class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
              >
                Download Template
              </Link>
            </div>
          </div>
        </div>
      </div>
    </template>
  </PageLayout>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { Link, router, usePage } from "@inertiajs/vue3";
import DataTable from "@/components/Admin/Table/DataTable.vue";
import FormBuilder from "@/components/Admin/Form/FormBuilder.vue";

// Props
const props = defineProps({
  products: Array,
  categories: Array,
  brands: Array,
});

// State
const loading = ref(false);
const submitting = ref(false);
const importing = ref(false);
const success = ref("");
const error = ref("");
const selectedItems = ref([]);
const selectedFile = ref(null);
const newProduct = ref({});
const formErrors = ref({});

// Page configuration
const breadcrumbs = [
  { name: "Dashboard", href: "/admin" },
  { name: "Products" },
];

const tabs = [
  { key: "list", name: "All Products" },
  { key: "create", name: "Create Product" },
  { key: "import", name: "Import" },
];

const pageActions = [
  {
    key: "create",
    label: "New Product",
    variant: "primary",
    href: "#",
    onClick: () => handleTabChange("create"),
  },
  {
    key: "export",
    label: "Export",
    variant: "secondary",
    href: "/admin/products/export",
  },
];

const tableActions = [
  {
    key: "bulk-delete",
    label: "Delete Selected",
    variant: "danger",
    onClick: bulkDelete,
  },
];

const rowActions = [
  {
    key: "edit",
    label: "Edit",
    href: (item) => `/admin/products/${item.id}/edit`,
  },
  {
    key: "duplicate",
    label: "Duplicate",
    onClick: (item) => duplicateProduct(item),
  },
  {
    key: "delete",
    label: "Delete",
    variant: "danger",
    onClick: (item) => deleteProduct(item),
  },
];

const productColumns = [
  {
    key: "name",
    label: "Product",
    sortable: true,
  },
  {
    key: "price",
    label: "Price",
    sortable: true,
    format: "currency",
  },
  {
    key: "stock",
    label: "Stock",
    sortable: true,
  },
  {
    key: "status",
    label: "Status",
    sortable: true,
  },
  {
    key: "created_at",
    label: "Created",
    sortable: true,
    format: "date",
  },
];

const productFields = [
  {
    name: "name",
    label: "Product Name",
    type: "text",
    required: true,
    placeholder: "Enter product name",
  },
  {
    name: "sku",
    label: "SKU",
    type: "text",
    required: true,
    placeholder: "Product SKU",
  },
  {
    name: "description",
    label: "Description",
    type: "textarea",
    rows: 4,
    placeholder: "Product description",
  },
  {
    name: "price",
    label: "Price",
    type: "number",
    required: true,
    placeholder: "0.00",
  },
  {
    name: "category_id",
    label: "Category",
    type: "custom",
    required: true,
  },
  {
    name: "brand_id",
    label: "Brand",
    type: "select",
    options: computed(() =>
      props.brands.map((brand) => ({
        value: brand.id,
        label: brand.name,
      })),
    ),
    placeholder: "Select a brand",
  },
  {
    name: "track_quantity",
    label: "Track Inventory",
    type: "checkbox",
    checkboxLabel: "Track stock quantity for this product",
  },
  {
    name: "stock_quantity",
    label: "Stock Quantity",
    type: "number",
    placeholder: "0",
  },
  {
    name: "is_active",
    label: "Active",
    type: "checkbox",
    checkboxLabel: "Product is active and visible to customers",
  },
  {
    name: "image",
    label: "Product Image",
    type: "custom",
  },
];

const filters = [
  {
    key: "category_id",
    placeholder: "All Categories",
    options: computed(() =>
      props.categories.map((cat) => ({
        value: cat.id,
        label: cat.name,
      })),
    ),
  },
  {
    key: "is_active",
    placeholder: "All Status",
    options: [
      { value: "1", label: "Active" },
      { value: "0", label: "Inactive" },
    ],
  },
];

// Methods
const handleTabChange = (tab) => {
  if (tab === "create") {
    resetForm();
  }
};

const handleSelectionChange = (selection) => {
  selectedItems.value = selection;
};

const handleSortChange = ({ column, direction }) => {
  // Handle sorting - could trigger API call
  console.log("Sort:", column, direction);
};

const createProduct = async (data) => {
  submitting.value = true;
  formErrors.value = {};

  try {
    await router.post("/admin/products", data);
    success.value = "Product created successfully!";
    resetForm();
    // Switch back to list tab
    handleTabChange("list");
  } catch (error) {
    if (error.response?.data?.errors) {
      formErrors.value = error.response.data.errors;
    } else {
      error.value = "Failed to create product. Please try again.";
    }
  } finally {
    submitting.value = false;
  }
};

const duplicateProduct = async (product) => {
  loading.value = true;
  try {
    await router.post(`/admin/products/${product.id}/duplicate`);
    success.value = "Product duplicated successfully!";
  } catch (err) {
    error.value = "Failed to duplicate product.";
  } finally {
    loading.value = false;
  }
};

const deleteProduct = async (product) => {
  if (confirm("Are you sure you want to delete this product?")) {
    loading.value = true;
    try {
      await router.delete(`/admin/products/${product.id}`);
      success.value = "Product deleted successfully!";
    } catch (err) {
      error.value = "Failed to delete product.";
    } finally {
      loading.value = false;
    }
  }
};

const bulkDelete = async () => {
  if (selectedItems.value.length === 0) return;

  if (confirm(`Delete ${selectedItems.value.length} selected products?`)) {
    loading.value = true;
    try {
      await router.delete("/admin/products/bulk", {
        data: { ids: selectedItems.value },
      });
      success.value = `${selectedItems.value.length} products deleted successfully!`;
      selectedItems.value = [];
    } catch (err) {
      error.value = "Failed to delete selected products.";
    } finally {
      loading.value = false;
    }
  }
};

const handleImageUpload = (event, update) => {
  const file = event.target.files[0];
  if (file) {
    update(file);
  }
};

const handleFileSelect = (event) => {
  selectedFile.value = event.target.files[0];
};

const importProducts = async () => {
  if (!selectedFile.value) return;

  importing.value = true;
  const formData = new FormData();
  formData.append("file", selectedFile.value);

  try {
    await router.post("/admin/products/import", formData);
    success.value = "Products imported successfully!";
    selectedFile.value = null;
    // Reset file input
    if (importFile.value) {
      importFile.value.value = "";
    }
  } catch (err) {
    error.value = "Failed to import products. Please check your file format.";
  } finally {
    importing.value = false;
  }
};

const resetForm = () => {
  newProduct.value = {
    is_active: true,
    track_quantity: true,
    stock_quantity: 0,
  };
  formErrors.value = {};
};

// Initialize
onMounted(() => {
  resetForm();
});
</script>
