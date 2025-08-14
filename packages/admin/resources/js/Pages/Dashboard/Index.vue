<template>
  <div>
    <!-- Page header -->
    <div class="border-b border-gray-200 pb-5">
      <h1 class="text-3xl font-bold leading-tight tracking-tight text-gray-900">Dashboard</h1>
      <p class="mt-2 max-w-4xl text-sm text-gray-500">
        Overview of your store's performance and recent activity.
      </p>
    </div>

    <!-- Stats overview -->
    <div class="mt-8">
      <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Total Orders -->
        <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
          <dt>
            <div class="absolute bg-indigo-500 rounded-md p-3">
              <ShoppingCartIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <p class="ml-16 text-sm font-medium text-gray-500 truncate">Total Orders</p>
          </dt>
          <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
            <p class="text-2xl font-semibold text-gray-900">{{ stats.orders.total }}</p>
            <p class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
              <ArrowUpIcon class="self-center flex-shrink-0 h-5 w-5 text-green-500" aria-hidden="true" />
              <span class="sr-only">Increased by</span>
              +{{ stats.orders.today }} today
            </p>
          </dd>
        </div>

        <!-- Total Products -->
        <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
          <dt>
            <div class="absolute bg-green-500 rounded-md p-3">
              <ShoppingBagIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <p class="ml-16 text-sm font-medium text-gray-500 truncate">Total Products</p>
          </dt>
          <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
            <p class="text-2xl font-semibold text-gray-900">{{ stats.products.total }}</p>
            <p class="ml-2 flex items-baseline text-sm font-semibold text-blue-600">
              {{ stats.products.active }} active
            </p>
          </dd>
        </div>

        <!-- Total Customers -->
        <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
          <dt>
            <div class="absolute bg-yellow-500 rounded-md p-3">
              <UserGroupIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <p class="ml-16 text-sm font-medium text-gray-500 truncate">Total Customers</p>
          </dt>
          <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
            <p class="text-2xl font-semibold text-gray-900">{{ stats.customers.total }}</p>
            <p class="ml-2 flex items-baseline text-sm font-semibold text-green-600">
              +{{ stats.customers.new_this_month }} this month
            </p>
          </dd>
        </div>

        <!-- Revenue -->
        <div class="relative bg-white pt-5 px-4 pb-12 sm:pt-6 sm:px-6 shadow rounded-lg overflow-hidden">
          <dt>
            <div class="absolute bg-red-500 rounded-md p-3">
              <CurrencyDollarIcon class="h-6 w-6 text-white" aria-hidden="true" />
            </div>
            <p class="ml-16 text-sm font-medium text-gray-500 truncate">Revenue Today</p>
          </dt>
          <dd class="ml-16 pb-6 flex items-baseline sm:pb-7">
            <p class="text-2xl font-semibold text-gray-900">${{ stats.revenue.today }}</p>
            <p class="ml-2 flex items-baseline text-sm font-semibold text-gray-500">
              ${{ stats.revenue.this_month }} this month
            </p>
          </dd>
        </div>
      </div>
    </div>

    <!-- Recent orders -->
    <div class="mt-8">
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <h3 class="text-base font-semibold leading-6 text-gray-900">Recent Orders</h3>
          <div class="mt-4">
            <div class="text-center py-8">
              <ShoppingCartIcon class="mx-auto h-12 w-12 text-gray-400" />
              <h3 class="mt-2 text-sm font-semibold text-gray-900">No orders yet</h3>
              <p class="mt-1 text-sm text-gray-500">Get started by creating your first product.</p>
              <div class="mt-6">
                <Link
                  href="/admin/products/create"
                  class="inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
                >
                  <PlusIcon class="-ml-0.5 mr-1.5 h-5 w-5" aria-hidden="true" />
                  New Product
                </Link>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Link } from '@inertiajs/vue3'
import {
  ShoppingCartIcon,
  ShoppingBagIcon,
  UserGroupIcon,
  CurrencyDollarIcon,
  ArrowUpIcon,
  PlusIcon
} from '@heroicons/vue/24/outline'

defineProps({
  stats: {
    type: Object,
    required: true
  }
})
</script>
