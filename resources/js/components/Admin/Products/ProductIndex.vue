<template>
  <div class="product-index">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-bold text-gray-900">
          {{ t('products.title') }}
        </h1>
        <p class="text-gray-600 mt-1">
          {{ t('products.list') }}
        </p>
      </div>
      
      <div class="flex space-x-3">
        <button 
          @click="importProducts"
          class="btn btn-secondary"
        >
          {{ t('admin.actions.import') }}
        </button>
        
        <button 
          @click="exportProducts"
          class="btn btn-secondary"
        >
          {{ t('admin.actions.export') }}
        </button>
        
        <router-link 
          to="/cp/products/create"
          class="btn btn-primary"
        >
          {{ t('products.create') }}
        </router-link>
      </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow-sm mb-6">
      <div class="p-4 border-b border-gray-200">
        <div class="flex items-center space-x-4">
          <!-- Search -->
          <div class="flex-1">
            <input
              v-model="filters.search"
              type="text"
              :placeholder="t('products.search_placeholder')"
              class="input"
            />
          </div>
          
          <!-- Status Filter -->
          <select v-model="filters.status" class="select">
            <option value="">{{ t('products.filters.all_products') }}</option>
            <option value="published">{{ t('products.filters.published') }}</option>
            <option value="draft">{{ t('products.filters.draft') }}</option>
            <option value="archived">{{ t('products.filters.archived') }}</option>
          </select>
          
          <!-- Category Filter -->
          <select v-model="filters.category" class="select">
            <option value="">{{ t('products.filters.category') }}</option>
            <option v-for="category in categories" :key="category.id" :value="category.id">
              {{ category.name }}
            </option>
          </select>
          
          <!-- Clear Filters -->
          <button 
            @click="clearFilters"
            class="btn btn-ghost"
            v-if="hasActiveFilters"
          >
            {{ t('admin.filters.clear_filters') }}
          </button>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm">
      <!-- Bulk Actions -->
      <div 
        v-if="selectedProducts.length > 0"
        class="flex items-center justify-between p-4 bg-blue-50 border-b"
      >
        <span class="text-sm text-gray-700">
          {{ tc('admin.pagination.selected_items', selectedProducts.length, { count: selectedProducts.length }) }}
        </span>
        
        <div class="flex space-x-2">
          <button 
            @click="bulkAction('publish')"
            class="btn btn-sm btn-secondary"
          >
            {{ t('products.bulk_actions.publish') }}
          </button>
          
          <button 
            @click="bulkAction('unpublish')"
            class="btn btn-sm btn-secondary"
          >
            {{ t('products.bulk_actions.unpublish') }}
          </button>
          
          <button 
            @click="bulkAction('delete')"
            class="btn btn-sm btn-danger"
          >
            {{ t('products.bulk_actions.delete') }}
          </button>
        </div>
      </div>

      <!-- Table -->
      <div class="overflow-x-auto">
        <table class="table">
          <thead>
            <tr>
              <th class="w-12">
                <input
                  type="checkbox"
                  @change="toggleSelectAll"
                  :checked="allSelected"
                  :indeterminate="someSelected"
                />
              </th>
              <th>{{ t('products.fields.name') }}</th>
              <th>{{ t('products.fields.sku') }}</th>
              <th>{{ t('products.fields.price') }}</th>
              <th>{{ t('products.fields.category') }}</th>
              <th>{{ t('products.fields.stock_status') }}</th>
              <th>{{ t('admin.status.status') }}</th>
              <th class="w-32">{{ t('admin.actions.actions') }}</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="product in products.data" :key="product.id">
              <td>
                <input
                  type="checkbox"
                  :value="product.id"
                  v-model="selectedProducts"
                />
              </td>
              <td>
                <div class="flex items-center">
                  <img
                    v-if="product.featured_image"
                    :src="product.featured_image"
                    :alt="product.name"
                    class="w-10 h-10 rounded-lg object-cover mr-3"
                  />
                  <div>
                    <router-link 
                      :to="`/cp/products/${product.id}`"
                      class="font-medium text-gray-900 hover:text-blue-600"
                    >
                      {{ product.name }}
                    </router-link>
                    <p class="text-sm text-gray-500">{{ product.handle }}</p>
                  </div>
                </div>
              </td>
              <td class="font-mono text-sm">{{ product.sku }}</td>
              <td>{{ formatCurrency(product.price) }}</td>
              <td>{{ product.category?.name || '—' }}</td>
              <td>
                <span 
                  :class="stockStatusClass(product.stock_status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ t(`products.stock_status.${product.stock_status}`) }}
                </span>
              </td>
              <td>
                <span 
                  :class="statusClass(product.status)"
                  class="inline-flex px-2 py-1 text-xs font-semibold rounded-full"
                >
                  {{ t(`admin.status.${product.status}`) }}
                </span>
              </td>
              <td>
                <div class="flex items-center space-x-2">
                  <router-link 
                    :to="`/cp/products/${product.id}/edit`"
                    class="text-blue-600 hover:text-blue-700"
                    :title="t('admin.actions.edit')"
                  >
                    <PencilIcon class="w-4 h-4" />
                  </router-link>
                  
                  <button
                    @click="duplicateProduct(product)"
                    class="text-gray-600 hover:text-gray-700"
                    :title="t('admin.actions.duplicate')"
                  >
                    <DocumentDuplicateIcon class="w-4 h-4" />
                  </button>
                  
                  <button
                    @click="deleteProduct(product)"
                    class="text-red-600 hover:text-red-700"
                    :title="t('admin.actions.delete')"
                  >
                    <TrashIcon class="w-4 h-4" />
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div class="px-4 py-3 border-t border-gray-200">
        <div class="flex items-center justify-between">
          <div class="text-sm text-gray-700">
            {{ t('admin.pagination.showing') }} 
            {{ products.from }} 
            {{ t('admin.pagination.to') }} 
            {{ products.to }} 
            {{ t('admin.pagination.of') }} 
            {{ products.total }} 
            {{ t('admin.pagination.results') }}
          </div>
          
          <pagination 
            :data="products"
            @page-change="loadProducts"
          />
        </div>
      </div>
    </div>

    <!-- No Results -->
    <div 
      v-if="products.data.length === 0 && !loading"
      class="text-center py-12"
    >
      <div class="text-gray-500 text-lg">{{ t('products.no_products') }}</div>
    </div>

    <!-- Loading -->
    <div 
      v-if="loading"
      class="text-center py-12"
    >
      <div class="text-gray-500">{{ t('admin.messages.loading') }}</div>
    </div>
  </div>
</template>

<script>
import { ref, reactive, computed, onMounted } from 'vue'
import { useTranslator } from '@/Utils/translator'
import { PencilIcon, DocumentDuplicateIcon, TrashIcon } from '@heroicons/vue/24/outline'

export default {
  name: 'ProductIndex',
  components: {
    PencilIcon,
    DocumentDuplicateIcon,
    TrashIcon
  },
  
  props: {
    page: Object,
    navigation: Array,
    categories: Array
  },

  setup(props) {
    const { t, tc, locale } = useTranslator()
    
    // State
    const products = ref({ data: [], total: 0, from: 0, to: 0 })
    const selectedProducts = ref([])
    const loading = ref(false)
    
    const filters = reactive({
      search: '',
      status: '',
      category: '',
      sort: 'name',
      direction: 'asc'
    })

    // Computed
    const hasActiveFilters = computed(() => {
      return filters.search || filters.status || filters.category
    })
    
    const allSelected = computed(() => {
      return selectedProducts.value.length === products.value.data.length && products.value.data.length > 0
    })
    
    const someSelected = computed(() => {
      return selectedProducts.value.length > 0 && selectedProducts.value.length < products.value.data.length
    })

    // Methods
    const loadProducts = async (page = 1) => {
      loading.value = true
      try {
        const response = await axios.get('/cp/products', {
          params: { ...filters, page }
        })
        products.value = response.data
      } catch (error) {
        console.error('Error loading products:', error)
      } finally {
        loading.value = false
      }
    }

    const clearFilters = () => {
      Object.assign(filters, {
        search: '',
        status: '',
        category: '',
        sort: 'name',
        direction: 'asc'
      })
      loadProducts()
    }

    const toggleSelectAll = () => {
      if (allSelected.value) {
        selectedProducts.value = []
      } else {
        selectedProducts.value = products.value.data.map(p => p.id)
      }
    }

    const bulkAction = async (action) => {
      if (selectedProducts.value.length === 0) return
      
      try {
        await axios.post('/cp/products/bulk', {
          action,
          ids: selectedProducts.value
        })
        
        selectedProducts.value = []
        await loadProducts()
      } catch (error) {
        console.error('Bulk action error:', error)
      }
    }

    const deleteProduct = async (product) => {
      if (!confirm(t('admin.messages.confirm_delete'))) return
      
      try {
        await axios.delete(`/cp/products/${product.id}`)
        await loadProducts()
      } catch (error) {
        console.error('Delete error:', error)
      }
    }

    const duplicateProduct = async (product) => {
      try {
        await axios.post(`/cp/products/${product.id}/duplicate`)
        await loadProducts()
      } catch (error) {
        console.error('Duplicate error:', error)
      }
    }

    const stockStatusClass = (status) => {
      const classes = {
        'in_stock': 'bg-green-100 text-green-800',
        'out_of_stock': 'bg-red-100 text-red-800',
        'low_stock': 'bg-yellow-100 text-yellow-800',
        'on_backorder': 'bg-blue-100 text-blue-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const statusClass = (status) => {
      const classes = {
        'published': 'bg-green-100 text-green-800',
        'draft': 'bg-yellow-100 text-yellow-800',
        'archived': 'bg-gray-100 text-gray-800'
      }
      return classes[status] || 'bg-gray-100 text-gray-800'
    }

    const formatCurrency = (amount) => {
      return new Intl.NumberFormat(locale.value === 'it' ? 'it-IT' : 'en-US', {
        style: 'currency',
        currency: 'EUR'
      }).format(amount)
    }

    const importProducts = () => {
      // TODO: Open import modal
      console.log('Import products')
    }

    const exportProducts = () => {
      // TODO: Open export modal
      console.log('Export products')
    }

    // Lifecycle
    onMounted(() => {
      loadProducts()
    })

    return {
      t,
      tc,
      products,
      selectedProducts,
      loading,
      filters,
      hasActiveFilters,
      allSelected,
      someSelected,
      loadProducts,
      clearFilters,
      toggleSelectAll,
      bulkAction,
      deleteProduct,
      duplicateProduct,
      stockStatusClass,
      statusClass,
      formatCurrency,
      importProducts,
      exportProducts
    }
  }
}
</script>

<style scoped>
.btn {
  @apply inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2;
}

.btn-primary {
  @apply text-white bg-blue-600 hover:bg-blue-700 focus:ring-blue-500;
}

.btn-secondary {
  @apply text-gray-700 bg-white hover:bg-gray-50 border-gray-300 focus:ring-blue-500;
}

.btn-danger {
  @apply text-white bg-red-600 hover:bg-red-700 focus:ring-red-500;
}

.btn-ghost {
  @apply text-gray-500 hover:text-gray-700;
}

.btn-sm {
  @apply px-3 py-1.5 text-xs;
}

.input {
  @apply block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.select {
  @apply block px-3 py-2 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500;
}

.table {
  @apply min-w-full divide-y divide-gray-200;
}

.table th {
  @apply px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider;
}

.table td {
  @apply px-6 py-4 whitespace-nowrap text-sm text-gray-900;
}
</style>
