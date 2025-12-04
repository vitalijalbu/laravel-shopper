<template>
  <div>
    <Head :title="isEditing ? 'Edit Channel' : 'Create Channel'" />

    <PageHeader
      :title="isEditing ? 'Edit Channel' : 'Create Channel'"
      :subtitle="site.name"
    >
      <template #actions>
        <Link
          :href="route('cp.sites.channels.index', site.id)"
          class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900"
        >
          <ArrowLeftIcon class="h-4 w-4 mr-2" />
          Back to Channels
        </Link>
      </template>

      <div class="max-w-4xl mx-auto">

      <form @submit.prevent="submit">
        <!-- Basic Information -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Slug <span class="text-red-500">*</span>
              </label>
              <input
                v-model="form.slug"
                type="text"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.slug }"
              />
              <p v-if="form.errors.slug" class="mt-1 text-sm text-red-600">{{ form.errors.slug }}</p>
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
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Type <span class="text-red-500">*</span>
              </label>
              <select
                v-model="form.type"
                required
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                :class="{ 'border-red-500': form.errors.type }"
              >
                <option value="">Select type</option>
                <option value="web">Web</option>
                <option value="mobile">Mobile</option>
                <option value="pos">POS</option>
                <option value="marketplace">Marketplace</option>
                <option value="b2b_portal">B2B Portal</option>
                <option value="social">Social</option>
                <option value="api">API</option>
              </select>
              <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">{{ form.errors.type }}</p>
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">URL</label>
              <input
                v-model="form.url"
                type="url"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
          </div>
        </div>

        <!-- Locales -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Locales</h2>
          <LocaleSelector v-model="form.locales" :available-locales="availableLocales" />
          <p v-if="form.errors.locales" class="mt-2 text-sm text-red-600">{{ form.errors.locales }}</p>
        </div>

        <!-- Currencies -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Currencies</h2>
          <CurrencySelector v-model="form.currencies" :available-currencies="availableCurrencies" />
          <p v-if="form.errors.currencies" class="mt-2 text-sm text-red-600">{{ form.errors.currencies }}</p>
        </div>

        <!-- Settings -->
        <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
          <h2 class="text-lg font-semibold text-gray-900 mb-4">Channel Settings</h2>
          
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
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
              <label class="flex items-center pt-6">
                <input
                  v-model="form.is_default"
                  type="checkbox"
                  class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                />
                <span class="ml-2 text-sm text-gray-700">Set as default channel for this site</span>
              </label>
            </div>
          </div>

          <!-- Custom Settings (JSON) -->
          <div class="mt-6">
            <label class="block text-sm font-medium text-gray-700 mb-1">Custom Settings (JSON)</label>
            <textarea
              v-model="settingsJson"
              rows="6"
              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 font-mono text-sm"
              :class="{ 'border-red-500': jsonError }"
              placeholder='{"theme": "dark", "features": ["cart", "wishlist"]}'
            />
            <p v-if="jsonError" class="mt-1 text-sm text-red-600">{{ jsonError }}</p>
            <p v-else class="mt-1 text-sm text-gray-500">
              Enter valid JSON for channel-specific configuration
            </p>
          </div>
        </div>

        <!-- Actions -->
        <div class="flex justify-end gap-3">
          <Link
            :href="route('cp.sites.channels.index', site.id)"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Cancel
          </Link>
          <button
            type="submit"
            :disabled="form.processing || !!jsonError"
            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 disabled:bg-blue-400 text-white rounded-lg transition-colors"
          >
            {{ form.processing ? 'Saving...' : isEditing ? 'Update Channel' : 'Create Channel' }}
          </button>
        </div>
      </form>
      </div>
    </PageHeader>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'
import PageHeader from '@/components/PageHeader.vue'
import LocaleSelector from '@/components/LocaleSelector.vue'
import CurrencySelector from '@/components/CurrencySelector.vue'

const props = defineProps({
  site: {
    type: Object,
    required: true,
  },
  channel: {
    type: Object,
    default: null,
  },
  availableLocales: {
    type: Array,
    required: true,
  },
  availableCurrencies: {
    type: Array,
    required: true,
  },
})

const isEditing = computed(() => !!props.channel)

const settingsJson = ref(
  props.channel?.settings ? JSON.stringify(props.channel.settings, null, 2) : '{}'
)
const jsonError = ref(null)

const form = useForm({
  site_id: props.site.id,
  name: props.channel?.name || '',
  slug: props.channel?.slug || '',
  description: props.channel?.description || '',
  type: props.channel?.type || '',
  url: props.channel?.url || '',
  locales: props.channel?.locales || [],
  currencies: props.channel?.currencies || [],
  settings: props.channel?.settings || {},
  status: props.channel?.status || 'draft',
  is_default: props.channel?.is_default || false,
})

// Validate and sync JSON settings
watch(settingsJson, (newValue) => {
  try {
    const parsed = JSON.parse(newValue)
    form.settings = parsed
    jsonError.value = null
  } catch (e) {
    jsonError.value = 'Invalid JSON format'
  }
})

const submit = () => {
  if (jsonError.value) return

  if (isEditing.value) {
    form.put(route('api.admin.channels.update', props.channel.id), {
      onSuccess: () => {
        // Redirect handled by Inertia
      },
    })
  } else {
    form.post(route('api.admin.channels.store'), {
      onSuccess: () => {
        // Redirect handled by Inertia
      },
    })
  }
}
</script>
