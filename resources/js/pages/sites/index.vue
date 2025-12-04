<template>
  <div>
    <Head title="Sites" />

    <PageHeader title="Sites" subtitle="Manage your multi-site configuration">
      <template #actions>
        <Link
          :href="route('cp.sites.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
        >
          <PlusIcon class="h-5 w-5 mr-2" />
          Add Site
        </Link>
      </template>

      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search sites..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>

          <!-- Status -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
            <select
              v-model="filters.status"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All</option>
              <option value="draft">Draft</option>
              <option value="active">Active</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <!-- Country -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Country</label>
            <select
              v-model="filters.country"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Countries</option>
              <option v-for="country in availableCountries" :key="country.code" :value="country.code">
                {{ country.name }}
              </option>
            </select>
          </div>

          <!-- Currency -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Currency</label>
            <select
              v-model="filters.currency"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Currencies</option>
              <option v-for="currency in availableCurrencies" :key="currency" :value="currency">
                {{ currency }}
              </option>
            </select>
          </div>
        </div>
      </div>

      <!-- Sites Table -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          Loading sites...
        </div>

        <div v-else-if="sites.data.length === 0" class="p-8 text-center text-gray-500">
          No sites found. Create your first site to get started.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Site
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Domain
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Countries
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Currency
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Channels
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="site in sites.data" :key="site.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div>
                    <div class="text-sm font-medium text-gray-900 flex items-center">
                      {{ site.name }}
                      <span
                        v-if="site.is_default"
                        class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                      >
                        Default
                      </span>
                    </div>
                    <div class="text-sm text-gray-500">{{ site.handle }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ site.domain || '-' }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="country in site.countries?.slice(0, 3)"
                    :key="country"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    {{ country }}
                  </span>
                  <span
                    v-if="site.countries?.length > 3"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    +{{ site.countries.length - 3 }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ site.default_currency }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    site.status === 'active'
                      ? 'bg-green-100 text-green-800'
                      : site.status === 'draft'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ site.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Link
                  :href="route('cp.sites.channels.index', site.id)"
                  class="text-sm text-blue-600 hover:text-blue-700"
                >
                  {{ site.channels_count || 0 }} channels
                </Link>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end items-center gap-2">
                  <Link
                    :href="route('cp.sites.edit', site.id)"
                    class="text-blue-600 hover:text-blue-700"
                  >
                    Edit
                  </Link>
                  <button
                    v-if="!site.is_default"
                    @click="setDefault(site.id)"
                    class="text-gray-600 hover:text-gray-700"
                  >
                    Set Default
                  </button>
                  <button
                    v-if="!site.is_default"
                    @click="confirmDelete(site.id)"
                    class="text-red-600 hover:text-red-700"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="sites.data.length > 0" class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ sites.from }} to {{ sites.to }} of {{ sites.total }} sites
        </div>
        <div class="flex gap-2">
          <Link
            v-for="link in sites.links"
            :key="link.label"
            :href="link.url"
            :class="[
              'px-3 py-2 text-sm rounded-md',
              link.active
                ? 'bg-blue-600 text-white'
                : link.url
                  ? 'bg-white border border-gray-300 text-gray-700 hover:bg-gray-50'
                  : 'bg-gray-100 text-gray-400 cursor-not-allowed',
            ]"
            :disabled="!link.url"
            v-html="link.label"
          />
        </div>
      </div>
    </PageHeader>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { PlusIcon } from '@heroicons/vue/24/outline'
import PageHeader from '@/components/PageHeader.vue'

const props = defineProps({
  sites: {
    type: Object,
    required: true,
  },
  availableCountries: {
    type: Array,
    default: () => [],
  },
  availableCurrencies: {
    type: Array,
    default: () => [],
  },
})

const loading = ref(false)

const filters = reactive({
  search: '',
  status: '',
  country: '',
  currency: '',
})

const applyFilters = () => {
  loading.value = true
  router.get(
    route('cp.sites.index'),
    {
      search: filters.search || undefined,
      status: filters.status || undefined,
      country: filters.country || undefined,
      currency: filters.currency || undefined,
    },
    {
      preserveState: true,
      preserveScroll: true,
      onFinish: () => {
        loading.value = false
      },
    }
  )
}

const setDefault = (siteId) => {
  if (confirm('Set this site as default?')) {
    router.post(
      route('api.admin.sites.set-default', siteId),
      {},
      {
        onSuccess: () => {
          router.reload()
        },
      }
    )
  }
}

const confirmDelete = (siteId) => {
  if (confirm('Are you sure you want to delete this site? This action cannot be undone.')) {
    router.delete(route('api.admin.sites.destroy', siteId), {
      onSuccess: () => {
        router.reload()
      },
    })
  }
}
</script>
