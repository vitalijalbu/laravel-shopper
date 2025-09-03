<template>
  <div>
    <div class="md:flex md:items-center md:justify-between mb-6">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl">
          {{ productType.name }}
          <span
            :class="[
              'ml-3 inline-flex px-2 py-1 text-xs font-medium rounded-full',
              productType.is_enabled
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800'
            ]"
          >
            {{ productType.is_enabled ? 'Enabled' : 'Disabled' }}
          </span>
        </h2>
        <p class="mt-1 text-sm text-gray-500">{{ productType.slug }}</p>
        <p v-if="productType.description" class="mt-2 text-sm text-gray-700">
          {{ productType.description }}
        </p>
      </div>
      <div class="mt-4 flex md:ml-4 md:mt-0 space-x-3">
        <Link
          :href="route('cp.product-types.edit', productType.id)"
          class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          Edit Product Type
        </Link>
        <Link
          :href="route('cp.product-types.destroy', productType.id)"
          method="delete"
          as="button"
          class="inline-flex items-center px-4 py-2 border border-red-300 shadow-sm text-sm font-medium rounded-md text-red-700 bg-white hover:bg-red-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
          :onBefore="() => confirm('Are you sure you want to delete this product type?')"
        >
          Delete
        </Link>
      </div>
    </div>

    <!-- Statistics and Information -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3 mb-8">
      <!-- Products Count -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-blue-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Products</dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ productType.products_count || 0 }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
        <div class="bg-gray-50 px-5 py-3">
          <div class="text-sm">
            <Link
              :href="route('cp.products.index', { product_type: productType.id })"
              class="font-medium text-indigo-700 hover:text-indigo-900"
            >
              View all products
            </Link>
          </div>
        </div>
      </div>

      <!-- Sort Order -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-green-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Sort Order</dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ productType.sort_order || 0 }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>

      <!-- Created Date -->
      <div class="bg-white overflow-hidden shadow rounded-lg">
        <div class="p-5">
          <div class="flex items-center">
            <div class="flex-shrink-0">
              <div class="w-8 h-8 bg-purple-100 rounded-md flex items-center justify-center">
                <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
            </div>
            <div class="ml-5 w-0 flex-1">
              <dl>
                <dt class="text-sm font-medium text-gray-500 truncate">Created</dt>
                <dd class="text-lg font-medium text-gray-900">
                  {{ formatDate(productType.created_at) }}
                </dd>
              </dl>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Recent Products (if any) -->
    <div v-if="recentProducts && recentProducts.length" class="bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <h3 class="text-lg font-medium leading-6 text-gray-900 mb-4">
          Recent Products
        </h3>
        <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
          <table class="min-w-full divide-y divide-gray-300">
            <thead class="bg-gray-50">
              <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Product
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Status
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Price
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Created
                </th>
                <th scope="col" class="relative px-6 py-3">
                  <span class="sr-only">Actions</span>
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="product in recentProducts" :key="product.id">
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <img
                        v-if="product.image"
                        class="h-10 w-10 rounded-lg object-cover"
                        :src="product.image"
                        :alt="product.name"
                      />
                      <div v-else class="h-10 w-10 rounded-lg bg-gray-200 flex items-center justify-center">
                        <svg class="h-6 w-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">
                        <Link :href="route('cp.products.show', product.id)" class="hover:text-indigo-600">
                          {{ product.name }}
                        </Link>
                      </div>
                      <div class="text-sm text-gray-500">{{ product.sku }}</div>
                    </div>
                  </div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap">
                  <span
                    :class="[
                      'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                      product.is_enabled
                        ? 'bg-green-100 text-green-800'
                        : 'bg-red-100 text-red-800'
                    ]"
                  >
                    {{ product.is_enabled ? 'Active' : 'Inactive' }}
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                  {{ formatPrice(product.price) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                  {{ formatDate(product.created_at) }}
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <Link
                    :href="route('cp.products.show', product.id)"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    View
                  </Link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
        <div class="mt-4">
          <Link
            :href="route('cp.products.index', { product_type: productType.id })"
            class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
          >
            View all {{ productType.products_count }} products →
          </Link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'

const props = defineProps({
  productType: {
    type: Object,
    required: true
  },
  recentProducts: Array
})

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  if (!price) return '—'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price / 100) // Assuming price is in cents
}
</script>

<script setup>
import { ref, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'

// Components
import PageLayout from '@/components/Admin/Layout/PageLayout.vue'
import Modal from '@/components/modal.vue'

// Props
const props = defineProps({
  page: Object,
  navigation: Object,
  productType: {
    type: Object,
    required: true
  },
  recentProducts: Array
})

// Composables
import { useApi } from '@/composables/useApi'
import { useNotification } from '@/composables/use-notification'

// Data
const loading = ref(false)
const success = ref('')
const error = ref('')
const showDeleteModal = ref(false)
const deleting = ref(false)

// API
const { post, delete: deleteApi } = useApi()
const { success, error } = useNotification()

// Methods
const duplicateProductType = async () => {
  try {
    const response = await post(`/cp/product-types/${props.productType.id}/duplicate`)
    success('Product type duplicated successfully')
    if (response.redirect) {
      router.visit(response.redirect)
    } else {
      router.visit('/cp/product-types')
    }
  } catch (err) {
    error.value = 'Failed to duplicate product type'
  }
}

const deleteProductType = () => {
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    await deleteApi(`/cp/product-types/${props.productType.id}`)
    success('Product type deleted successfully')
    router.visit('/cp/product-types')
  } catch (err) {
    if (err.response?.status === 422) {
      error.value = err.response.data.error
    } else {
      error.value = 'Failed to delete product type'
    }
    showDeleteModal.value = false
  } finally {
    deleting.value = false
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

const formatPrice = (price) => {
  if (!price) return '—'
  return new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD'
  }).format(price / 100) // Assuming price is in cents
}
</script>
