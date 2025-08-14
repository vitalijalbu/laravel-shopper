<template>
  <div>
    <!-- Page header -->
    <div class="border-b border-gray-200 pb-5 sm:flex sm:items-center sm:justify-between">
      <div>
        <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Products</h1>
        <p class="mt-2 max-w-4xl text-sm text-gray-500">
          Manage your store's product catalog.
        </p>
      </div>
      <div class="mt-3 sm:ml-4 sm:mt-0">
        <Link
          href="/admin/products/create"
          class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
          <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
          Add Product
        </Link>
      </div>
    </div>

    <!-- Filters -->
    <div class="mt-6 bg-white shadow rounded-lg">
      <div class="px-4 py-5 sm:p-6">
        <form @submit.prevent="applyFilters" class="grid grid-cols-1 gap-4 sm:grid-cols-4">
          <div>
            <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
            <input
              id="search"
              v-model="form.search"
              type="text"
              placeholder="Search products..."
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            />
          </div>
          
          <div>
            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
            <select
              id="status"
              v-model="form.status"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Status</option>
              <option value="active">Active</option>
              <option value="draft">Draft</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div>
            <label for="brand" class="block text-sm font-medium text-gray-700">Brand</label>
            <select
              id="brand"
              v-model="form.brand"
              class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
            >
              <option value="">All Brands</option>
              <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                {{ brand.name }}
              </option>
            </select>
          </div>

          <div class="flex items-end">
            <button
              type="submit"
              class="w-full justify-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
              Apply Filters
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- Products table -->
    <div class="mt-8 bg-white shadow rounded-lg overflow-hidden">
      <div v-if="products.data.length === 0" class="text-center py-12">
        <ShoppingBagIcon class="mx-auto h-12 w-12 text-gray-400" />
        <h3 class="mt-2 text-sm font-semibold text-gray-900">No products</h3>
        <p class="mt-1 text-sm text-gray-500">Get started by creating a new product.</p>
        <div class="mt-6">
          <Link
            href="/admin/products/create"
            class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500"
          >
            <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
            New Product
          </Link>
        </div>
      </div>

      <div v-else>
        <table class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Brand
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Stock
              </th>
              <th class="relative px-6 py-3"><span class="sr-only">Actions</span></th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="product in products.data" :key="product.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-12 w-12">
                    <img
                      v-if="product.featured_image"
                      :src="product.featured_image_thumb || product.featured_image"
                      :alt="product.name"
                      class="h-12 w-12 rounded-lg object-cover"
                    />
                    <div
                      v-else
                      class="h-12 w-12 rounded-lg bg-gray-200 flex items-center justify-center"
                    >
                      <PhotoIcon class="h-6 w-6 text-gray-400" />
                    </div>
                  </div>
                  <div class="ml-4">
                    <div class="text-sm font-medium text-gray-900">{{ product.name }}</div>
                    <div class="text-sm text-gray-500">{{ product.sku }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="[
                  'inline-flex px-2 py-1 text-xs font-semibold rounded-full',
                  product.status === 'active' ? 'bg-green-100 text-green-800' :
                  product.status === 'draft' ? 'bg-yellow-100 text-yellow-800' :
                  'bg-red-100 text-red-800'
                ]">
                  {{ product.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ product.brand?.name || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ product.formatted_price || '-' }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                {{ getTotalStock(product) }}
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end space-x-2">
                  <Link
                    :href="`/admin/products/${product.id}`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    View
                  </Link>
                  <Link
                    :href="`/admin/products/${product.id}/edit`"
                    class="text-indigo-600 hover:text-indigo-900"
                  >
                    Edit
                  </Link>
                </div>
              </td>
            </tr>
          </tbody>
        </table>

        <!-- Pagination -->
        <div v-if="products.links" class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
          <div class="flex items-center justify-between">
            <div class="flex items-center">
              <p class="text-sm text-gray-700">
                Showing {{ products.from }} to {{ products.to }} of {{ products.total }} results
              </p>
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="(link, index) in products.links"
                :key="index"
                :href="link.url"
                :class="[
                  'px-3 py-2 text-sm font-medium',
                  link.active
                    ? 'bg-indigo-600 text-white'
                    : 'bg-white text-gray-500 hover:bg-gray-50 hover:text-gray-700',
                  !link.url ? 'opacity-50 cursor-not-allowed' : 'border border-gray-300'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { PlusIcon, ShoppingBagIcon, PhotoIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  products: Object,
  filters: Object,
  brands: Array
})

const form = reactive({
  search: props.filters.search || '',
  status: props.filters.status || '',
  brand: props.filters.brand || ''
})

const applyFilters = () => {
  router.get('/admin/products', form, {
    preserveState: true,
    preserveScroll: true
  })
}

const getTotalStock = (product) => {
  if (!product.variants || product.variants.length === 0) return 0
  return product.variants.reduce((total, variant) => total + variant.quantity, 0)
}
</script>
