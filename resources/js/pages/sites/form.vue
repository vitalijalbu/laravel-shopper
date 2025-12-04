<template>
  <div>
    <Head :title="isEditing ? 'Edit Site' : 'Create Site'" />

    <PageHeader
      :title="isEditing ? 'Edit Site' : 'Create Site'"
      :subtitle="isEditing ? 'Update site configuration' : 'Add a new site to your multi-site setup'"
    >
      <template #actions>
        <Link
          :href="route('cp.sites.index')"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-2" />
          Back to Sites
        </Link>
      </template>

      <div class="max-w-5xl mx-auto">

      <form @submit.prevent="submit">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Handle <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.handle"
                type="text"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.handle }"
              />
              <p v-if="form.errors.handle" class="mt-1 text-sm text-red-600">{{ form.errors.handle }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Name <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.name"
                type="text"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.name }"
              />
              <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
              <textarea
                v-model="form.description"
                rows="3"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
              <input
                v-model="form.url"
                type="url"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Domain <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.domain"
                type="text"
                required
                placeholder="example.com"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.domain }"
              />
              <p v-if="form.errors.domain" class="mt-1 text-sm text-red-600">{{ form.errors.domain }}</p>
            </div>
          </div>
        </div>

        <!-- Localization -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Localization</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Locale <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.locale"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="">Select locale</option>
                <option v-for="locale in availableLocales" :key="locale.code" :value="locale.code">
                  {{ locale.name }}
                </option>
              </select>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Language <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.lang"
                type="text"
                required
                placeholder="en"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div class="md:col-span-2">
              <label class="block text-sm font-medium text-gray-700 mb-2">
                Countries <span class="text-red-500">*</span>
              </label>
              <CountrySelector v-model="form.countries" :available-countries="availableCountries" />
              <p v-if="form.errors.countries" class="mt-1 text-sm text-red-600">{{ form.errors.countries }}</p>
            </div>
          </div>
        </div>

        <!-- Currency -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Currency Settings</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Default Currency <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.default_currency"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.default_currency }"
              >
                <option value="">Select currency</option>
                <option v-for="currency in availableCurrencies" :key="currency.code" :value="currency.code">
                  {{ currency.code }} - {{ currency.name }}
                </option>
              </select>
              <p v-if="form.errors.default_currency" class="mt-1 text-sm text-red-600">{{ form.errors.default_currency }}</p>
            </div>
          </div>
        </div>

        <!-- Tax Settings -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Tax Configuration</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="flex items-center">
                <input
                  v-model="form.tax_included_in_prices"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Tax included in prices</span>
              </label>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Tax Region</label>
              <input
                v-model="form.tax_region"
                type="text"
                placeholder="EU, US, etc."
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Catalogs -->
        <div v-if="isEditing" class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Catalogs</h2>
          <CatalogPicker v-model="form.catalogs" :available-catalogs="availableCatalogs" />
        </div>

        <!-- Site Settings -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Site Settings</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
              <input
                v-model.number="form.priority"
                type="number"
                min="0"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
              <p class="mt-1 text-xs text-gray-500">Lower values = higher priority</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
              <select
                v-model="form.status"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              >
                <option value="draft">Draft</option>
                <option value="active">Active</option>
                <option value="archived">Archived</option>
              </select>
            </div>

            <div>
              <label class="flex items-center">
                <input
                  v-model="form.is_default"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Set as default site</span>
              </label>
            </div>
          </div>
        </div>

        <!-- Publishing Schedule -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Publishing Schedule</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Publish At</label>
              <input
                v-model="form.published_at"
                type="datetime-local"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Unpublish At</label>
              <input
                v-model="form.unpublished_at"
                type="datetime-local"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('cp.sites.index')"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors"
          >
            {{ form.processing ? 'Saving...' : isEditing ? 'Update Site' : 'Create Site' }}
          </button>
        </div>
      </form>
      </div>
    </PageHeader>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import PageHeader from '@/components/PageHeader.vue'
import CountrySelector from '@/components/CountrySelector.vue'
import CatalogPicker from '@/components/CatalogPicker.vue'

const props = defineProps({
  site: {
    type: Object,
    default: null,
  },
  availableCountries: {
    type: Array,
    required: true,
  },
  availableLocales: {
    type: Array,
    required: true,
  },
  availableCurrencies: {
    type: Array,
    required: true,
  },
  availableCatalogs: {
    type: Array,
    default: () => [],
  },
})

const isEditing = computed(() => !!props.site)

const form = useForm({
  handle: props.site?.handle || '',
  name: props.site?.name || '',
  description: props.site?.description || '',
  url: props.site?.url || '',
  domain: props.site?.domain || '',
  locale: props.site?.locale || 'en_US',
  lang: props.site?.lang || 'en',
  countries: props.site?.countries || [],
  default_currency: props.site?.default_currency || '',
  tax_included_in_prices: props.site?.tax_included_in_prices || false,
  tax_region: props.site?.tax_region || '',
  priority: props.site?.priority || 0,
  status: props.site?.status || 'draft',
  is_default: props.site?.is_default || false,
  published_at: props.site?.published_at || null,
  unpublished_at: props.site?.unpublished_at || null,
  catalogs: props.site?.catalogs || [],
})

const submit = () => {
  if (isEditing.value) {
    form.put(route('api.admin.sites.update', props.site.id), {
      onSuccess: () => {
        // Redirect handled by Inertia
      },
    })
  } else {
    form.post(route('api.admin.sites.store'), {
      onSuccess: () => {
        // Redirect handled by Inertia
      },
    })
  }
}
</script>
