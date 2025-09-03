<template>
  <div>
    <div class="md:flex md:items-center md:justify-between mb-6">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl">
          Product Types
        </h2>
      </div>
      <div class="mt-4 flex md:ml-4 md:mt-0">
        <Link
          :href="route('cp.product-types.create')"
          class="ml-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
          Add Product Type
        </Link>
      </div>
    </div>

    <!-- Filter Bar -->
    <div class="bg-white shadow rounded-lg p-6 mb-6">
      <form @submit.prevent="search" class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="lg:col-span-2">
          <input
            v-model="filters.search"
            type="text"
            placeholder="Search product types..."
            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
          />
        </div>
        <div>
          <select
            v-model="filters.status"
            class="block w-full rounded-md border-0 py-1.5 text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6"
          >
            <option value="">All Status</option>
            <option value="1">Enabled</option>
            <option value="0">Disabled</option>
          </select>
        </div>
        <div>
          <button
            type="submit"
            class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
          >
            Search
          </button>
        </div>
      </form>
    </div>

    <!-- Table -->
    <div class="bg-white shadow rounded-lg overflow-hidden">
      <table class="min-w-full divide-y divide-gray-300">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Name
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Status
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Products
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Sort Order
            </th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
              Created
            </th>
            <th class="relative px-6 py-3">
              <span class="sr-only">Actions</span>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <tr v-for="productType in productTypes.data" :key="productType.id">
            <td class="px-6 py-4 whitespace-nowrap">
              <div>
                <div class="text-sm font-medium text-gray-900">
                  <Link :href="route('cp.product-types.show', productType.id)" class="hover:text-indigo-600">
                    {{ productType.name }}
                  </Link>
                </div>
                <div class="text-sm text-gray-500">{{ productType.slug }}</div>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap">
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                  productType.is_enabled
                    ? 'bg-green-100 text-green-800'
                    : 'bg-red-100 text-red-800'
                ]"
              >
                {{ productType.is_enabled ? 'Enabled' : 'Disabled' }}
              </span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              <Link 
                v-if="productType.products_count > 0"
                :href="route('cp.products.index', { product_type: productType.id })" 
                class="text-indigo-600 hover:text-indigo-900"
              >
                {{ productType.products_count }}
              </Link>
              <span v-else class="text-gray-500">0</span>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
              {{ productType.sort_order || '—' }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
              {{ formatDate(productType.created_at) }}
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
              <div class="flex items-center justify-end space-x-2">
                <Link
                  :href="route('cp.product-types.edit', productType.id)"
                  class="text-indigo-600 hover:text-indigo-900"
                >
                  Edit
                </Link>
                <Link
                  :href="route('cp.product-types.destroy', productType.id)"
                  method="delete"
                  as="button"
                  class="text-red-600 hover:text-red-900"
                  :data="{ id: productType.id }"
                  :onBefore="() => confirm('Are you sure you want to delete this product type?')"
                >
                  Delete
                </Link>
              </div>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="productTypes.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-700">
              Showing {{ productTypes.from }} to {{ productTypes.to }} of {{ productTypes.total }} results
            </p>
          </div>
          <div class="flex space-x-1">
            <Link
              v-for="link in productTypes.links"
              :key="link.label"
              :href="link.url"
              :class="[
                'px-3 py-2 text-sm',
                link.active
                  ? 'bg-indigo-600 text-white'
                  : 'bg-white text-gray-500 hover:text-gray-700',
                !link.url ? 'opacity-50 cursor-not-allowed' : ''
              ]"
              v-html="link.label"
            />
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'

const props = defineProps({
  productTypes: Object,
  filters: Object
})

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || ''
})

const search = () => {
  router.get(route('cp.product-types.index'), filters, {
    preserveState: true,
    replace: true
  })
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

import { ref, reactive, onMounted, computed } from 'vue'
import { Link } from '@inertiajs/vue3'
import { debounce } from 'lodash'

// Components
import PageLayout from '@/components/Admin/Layout/PageLayout.vue'
import DataTable from '@/components/data-table.vue'
import Modal from '@/components/modal.vue'

// Props
const props = defineProps({
  page: Object,
  navigation: Object,
})

// Composables
import { useApi } from '@/composables/useApi'
import { useNotification } from '@/composables/use-notification'

// Data
const loading = ref(false)
const success = ref('')
const error = ref('')
const showDeleteModal = ref(false)
const productTypeToDelete = ref(null)
const deleting = ref(false)

const productTypes = ref([])
const pagination = ref({})

const filters = reactive({
  search: '',
  is_enabled: '',
  sort_by: 'sort_order',
  sort_direction: 'asc',
  page: 1,
  per_page: 20
})

// Computed
const tableActions = computed(() => [
  {
    label: 'Bulk Enable',
    action: () => bulkAction('enable'),
    icon: 'check'
  },
  {
    label: 'Bulk Disable',
    action: () => bulkAction('disable'),
    icon: 'x'
  },
  {
    label: 'Bulk Delete',
    action: () => bulkAction('delete'),
    icon: 'trash',
    destructive: true
  }
])

const rowActions = computed(() => [
  {
    label: 'View',
    action: (item) => window.location.href = `/cp/product-types/${item.id}`,
    icon: 'eye'
  },
  {
    label: 'Edit',
    action: (item) => window.location.href = `/cp/product-types/${item.id}/edit`,
    icon: 'pencil'
  },
  {
    label: 'Duplicate',
    action: (item) => duplicateProductType(item),
    icon: 'duplicate'
  },
  {
    label: 'Delete',
    action: (item) => deleteProductType(item),
    icon: 'trash',
    destructive: true
  }
])

const columns = computed(() => [
  {
    key: 'name',
    label: 'Name',
    sortable: true
  },
  {
    key: 'description',
    label: 'Description',
    sortable: false
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false
  },
  {
    key: 'products_count',
    label: 'Products',
    sortable: true
  },
  {
    key: 'sort_order',
    label: 'Sort Order',
    sortable: true
  },
  {
    key: 'created_at',
    label: 'Created',
    sortable: true
  }
])

// API
const { get, post, delete: deleteApi } = useApi()
const { success, error, info } = useNotification()

// Methods
const loadProductTypes = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '' && value !== null) {
        params.append(key, value)
      }
    })

    const response = await get(`/cp/product-types?${params.toString()}`)
    productTypes.value = response.data
    pagination.value = response.meta || {}
  } catch (err) {
    error.value = 'Failed to load product types'
  } finally {
    loading.value = false
  }
}

const debounceSearch = debounce(() => {
  filters.page = 1
  loadProductTypes()
}, 300)

const applyFilters = () => {
  filters.page = 1
  loadProductTypes()
}

const handleSelectionChange = (selected) => {
  // Handle bulk selection
}

const handleSortChange = (sort) => {
  filters.sort_by = sort.column
  filters.sort_direction = sort.direction
  loadProductTypes()
}

const handlePageChange = (page) => {
  filters.page = page
  loadProductTypes()
}

const deleteProductType = (productType) => {
  productTypeToDelete.value = productType
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    await deleteApi(`/cp/product-types/${productTypeToDelete.value.id}`)
    success('Product type deleted successfully')
    showDeleteModal.value = false
    loadProductTypes()
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data.error
    } else {
      error('Failed to delete product type')
    }
  } finally {
    deleting.value = false
  }
}

const duplicateProductType = async (productType) => {
  try {
    const response = await post(`/cp/product-types/${productType.id}/duplicate`)
    success('Product type duplicated successfully')
    if (response.redirect) {
      window.location.href = response.redirect
    } else {
      loadProductTypes()
    }
  } catch (err) {
    error.value = 'Failed to duplicate product type'
  }
}

const bulkAction = async (action) => {
  // Implement bulk actions
  info(`Bulk ${action} action executed`)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const truncate = (text, length) => {
  if (!text) return ''
  return text.length > length ? text.substring(0, length) + '...' : text
}

// Lifecycle
onMounted(() => {
  loadProductTypes()
})
</script>
