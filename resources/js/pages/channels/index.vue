<template>
  <div>
    <Head :title="`${site.name} - Channels`" />

    <PageHeader :title="`${site.name} - Channels`" subtitle="Manage sales channels for this site">
      <template #actions>
        <div class="flex items-center gap-3">
          <Link
            :href="route('cp.sites.index')"
            class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
          >
            <ArrowLeftIcon class="h-4 w-4 mr-2" />
            Back to Sites
          </Link>
          <Link
            :href="route('cp.sites.channels.create', site.id)"
            class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors"
          >
            <PlusIcon class="h-5 w-5 mr-2" />
            Add Channel
          </Link>
        </div>
      </template>

      <!-- Filters -->
      <div class="mb-6 bg-white rounded-lg border border-gray-200 p-4">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Search -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Search channels..."
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @input="applyFilters"
            />
          </div>

          <!-- Type -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
            <select
              v-model="filters.type"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              @change="applyFilters"
            >
              <option value="">All Types</option>
              <option value="web">Web</option>
              <option value="mobile">Mobile</option>
              <option value="pos">POS</option>
              <option value="marketplace">Marketplace</option>
              <option value="b2b_portal">B2B Portal</option>
              <option value="social">Social</option>
              <option value="api">API</option>
            </select>
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
        </div>
      </div>

      <!-- Channels Table -->
      <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div v-if="loading" class="p-8 text-center text-gray-500">
          Loading channels...
        </div>

        <div v-else-if="channels.data.length === 0" class="p-8 text-center text-gray-500">
          No channels found. Create your first channel to get started.
        </div>

        <table v-else class="min-w-full divide-y divide-gray-200">
          <thead class="bg-gray-50">
            <tr>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Channel
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Type
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Locales
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Currencies
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200">
            <tr v-for="channel in channels.data" :key="channel.id" class="hover:bg-gray-50">
              <td class="px-6 py-4 whitespace-nowrap">
                <div>
                  <div class="text-sm font-medium text-gray-900 flex items-center">
                    {{ channel.name }}
                    <span
                      v-if="channel.is_default"
                      class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800"
                    >
                      Default
                    </span>
                  </div>
                  <div class="text-sm text-gray-500">{{ channel.slug }}</div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    getTypeColor(channel.type),
                  ]"
                >
                  {{ formatType(channel.type) }}
                </span>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="locale in channel.locales?.slice(0, 2)"
                    :key="locale"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    {{ locale }}
                  </span>
                  <span
                    v-if="channel.locales?.length > 2"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    +{{ channel.locales.length - 2 }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex flex-wrap gap-1">
                  <span
                    v-for="currency in channel.currencies?.slice(0, 2)"
                    :key="currency"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    {{ currency }}
                  </span>
                  <span
                    v-if="channel.currencies?.length > 2"
                    class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-800"
                  >
                    +{{ channel.currencies.length - 2 }}
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    channel.status === 'active'
                      ? 'bg-green-100 text-green-800'
                      : channel.status === 'draft'
                        ? 'bg-yellow-100 text-yellow-800'
                        : 'bg-gray-100 text-gray-800',
                  ]"
                >
                  {{ channel.status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex justify-end items-center gap-2">
                  <Link
                    :href="route('cp.sites.channels.edit', [site.id, channel.id])"
                    class="text-blue-600 hover:text-blue-700"
                  >
                    Edit
                  </Link>
                  <button
                    v-if="!channel.is_default"
                    @click="setDefault(channel.id)"
                    class="text-gray-600 hover:text-gray-700"
                  >
                    Set Default
                  </button>
                  <button
                    v-if="!channel.is_default"
                    @click="confirmDelete(channel.id)"
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
      <div v-if="channels.data.length > 0" class="mt-6 flex items-center justify-between">
        <div class="text-sm text-gray-700">
          Showing {{ channels.from }} to {{ channels.to }} of {{ channels.total }} channels
        </div>
        <div class="flex gap-2">
          <Link
            v-for="link in channels.links"
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
import { PlusIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'
import PageHeader from '@/components/PageHeader.vue'

const props = defineProps({
  site: {
    type: Object,
    required: true,
  },
  channels: {
    type: Object,
    required: true,
  },
})

const loading = ref(false)

const filters = reactive({
  search: '',
  type: '',
  status: '',
})

const applyFilters = () => {
  loading.value = true
  router.get(
    route('cp.sites.channels.index', props.site.id),
    {
      search: filters.search || undefined,
      type: filters.type || undefined,
      status: filters.status || undefined,
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

const formatType = (type) => {
  const typeMap = {
    web: 'Web',
    mobile: 'Mobile',
    pos: 'POS',
    marketplace: 'Marketplace',
    b2b_portal: 'B2B Portal',
    social: 'Social',
    api: 'API',
  }
  return typeMap[type] || type
}

const getTypeColor = (type) => {
  const colorMap = {
    web: 'bg-blue-100 text-blue-800',
    mobile: 'bg-purple-100 text-purple-800',
    pos: 'bg-green-100 text-green-800',
    marketplace: 'bg-orange-100 text-orange-800',
    b2b_portal: 'bg-indigo-100 text-indigo-800',
    social: 'bg-pink-100 text-pink-800',
    api: 'bg-gray-100 text-gray-800',
  }
  return colorMap[type] || 'bg-gray-100 text-gray-800'
}

const setDefault = (channelId) => {
  if (confirm('Set this channel as default for this site?')) {
    router.post(
      route('api.admin.channels.set-default', channelId),
      {},
      {
        onSuccess: () => {
          router.reload()
        },
      }
    )
  }
}

const confirmDelete = (channelId) => {
  if (confirm('Are you sure you want to delete this channel? This action cannot be undone.')) {
    router.delete(route('api.admin.channels.destroy', channelId), {
      onSuccess: () => {
        router.reload()
      },
    })
  }
}
</script>
