<template>
  <div>
    <div class="md:flex md:items-center md:justify-between mb-6">
      <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl">
          Edit Product Type
        </h2>
      </div>
    </div>

    <form @submit.prevent="submit" class="space-y-8">
      <!-- Basic Information -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="space-y-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Basic Information
            </h3>

            <!-- Name -->
            <div>
              <label for="name" class="block text-sm font-medium text-gray-700">
                Name *
              </label>
              <div class="mt-1">
                <input
                  v-model="form.name"
                  type="text"
                  id="name"
                  :class="[
                    'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                    form.errors.name ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="Enter product type name"
                  required
                />
                <p v-if="form.errors.name" class="mt-2 text-sm text-red-600">
                  {{ form.errors.name }}
                </p>
              </div>
            </div>

            <!-- Slug -->
            <div>
              <label for="slug" class="block text-sm font-medium text-gray-700">
                Slug *
              </label>
              <div class="mt-1">
                <input
                  v-model="form.slug"
                  type="text"
                  id="slug"
                  :class="[
                    'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                    form.errors.slug ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="product-type-slug"
                  required
                />
                <p v-if="form.errors.slug" class="mt-2 text-sm text-red-600">
                  {{ form.errors.slug }}
                </p>
                <p class="mt-2 text-sm text-gray-500">
                  The slug is used in URLs and must be unique.
                </p>
              </div>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="block text-sm font-medium text-gray-700">
                Description
              </label>
              <div class="mt-1">
                <textarea
                  v-model="form.description"
                  id="description"
                  rows="4"
                  :class="[
                    'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                    form.errors.description ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="Enter product type description"
                />
                <p v-if="form.errors.description" class="mt-2 text-sm text-red-600">
                  {{ form.errors.description }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Settings -->
      <div class="bg-white shadow rounded-lg">
        <div class="px-4 py-5 sm:p-6">
          <div class="space-y-6">
            <h3 class="text-lg font-medium leading-6 text-gray-900">
              Settings
            </h3>

            <!-- Status -->
            <div class="flex items-start">
              <div class="flex items-center h-5">
                <input
                  v-model="form.is_enabled"
                  id="is_enabled"
                  type="checkbox"
                  class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded"
                />
              </div>
              <div class="ml-3 text-sm">
                <label for="is_enabled" class="font-medium text-gray-700">
                  Enabled
                </label>
                <p class="text-gray-500">
                  Enable this product type to make it available for products.
                </p>
              </div>
            </div>

            <!-- Sort Order -->
            <div>
              <label for="sort_order" class="block text-sm font-medium text-gray-700">
                Sort Order
              </label>
              <div class="mt-1">
                <input
                  v-model.number="form.sort_order"
                  type="number"
                  id="sort_order"
                  min="0"
                  :class="[
                    'block w-full rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm',
                    form.errors.sort_order ? 'border-red-300' : 'border-gray-300'
                  ]"
                  placeholder="0"
                />
                <p v-if="form.errors.sort_order" class="mt-2 text-sm text-red-600">
                  {{ form.errors.sort_order }}
                </p>
                <p class="mt-2 text-sm text-gray-500">
                  Lower numbers appear first in lists.
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Form Actions -->
      <div class="flex justify-end space-x-3">
        <Link
          :href="route('cp.product-types.index')"
          class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Cancel
        </Link>
        <button
          type="submit"
          :disabled="form.processing"
          class="rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
        >
          {{ form.processing ? 'Updating...' : 'Update Product Type' }}
        </button>
      </div>
    </form>
  </div>
</template>

<script setup>
import { onMounted, watch } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'

const props = defineProps({
  productType: {
    type: Object,
    required: true
  }
})

const form = useForm({
  name: '',
  slug: '',
  description: '',
  is_enabled: true,
  sort_order: 0
})

const submit = () => {
  form.put(route('cp.product-types.update', props.productType.id))
}

const fillForm = () => {
  if (props.productType) {
    form.name = props.productType.name || ''
    form.slug = props.productType.slug || ''
    form.description = props.productType.description || ''
    form.is_enabled = props.productType.is_enabled !== false
    form.sort_order = props.productType.sort_order || 0
  }
}

onMounted(() => {
  fillForm()
})

watch(() => props.productType, () => {
  fillForm()
}, { deep: true })
</script>

<script setup>
import { ref, reactive, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { debounce } from 'lodash'

// Components
import PageLayout from '@/components/Admin/Layout/PageLayout.vue'

// Props
const props = defineProps({
  page: Object,
  navigation: Object,
  productType: {
    type: Object,
    required: true
  }
})

// Composables
import { useForm } from '@inertiajs/vue3'

// Data
const form = useForm({
  name: '',
  slug: '',
  description: '',
  is_enabled: true,
  sort_order: 0
})

// Methods
const generateSlug = debounce(() => {
  // Only auto-generate slug if user hasn't manually modified it
  if (form.name && form.slug === slugify(props.productType.name)) {
    form.slug = slugify(form.name)
  }
}, 300)

const slugify = (text) => {
  return text
    .toLowerCase()
    .replace(/[^a-z0-9\s-]/g, '')
    .replace(/\s+/g, '-')
    .replace(/-+/g, '-')
    .trim('-')
}

const submit = async () => {
  loading.value = true
  errors.value = {}
  error.value = ''

  try {
    const response = await put(`/cp/product-types/${props.productType.id}`, form)

    success('Product type updated successfully')

    if (response.redirect) {
      router.visit(response.redirect)
    } else {
      router.visit('/cp/product-types')
    }
  } catch (err) {
    if (err.response?.status === 422) {
      errors.value = err.response.data.errors || {}
      error.value = err.response.data.message || 'Validation failed'
    } else {
      error.value = 'An error occurred while updating the product type'
    }
  } finally {
    loading.value = false
  }
}

const fillForm = () => {
  if (props.productType) {
    form.name = props.productType.name || ''
    form.slug = props.productType.slug || ''
    form.description = props.productType.description || ''
    form.is_enabled = props.productType.is_enabled !== false
    form.sort_order = props.productType.sort_order || 0
  }
}

// Lifecycle
onMounted(() => {
  fillForm()
})

// Watchers
watch(() => props.productType, () => {
  fillForm()
}, { deep: true })
</script>
