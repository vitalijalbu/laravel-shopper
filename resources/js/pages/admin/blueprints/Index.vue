<template>
  <Page title="Blueprint System">
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="md:flex md:items-center md:justify-between mb-8">
        <div class="flex-1 min-w-0">
          <h2 class="text-2xl font-bold leading-7 text-gray-900 sm:text-3xl sm:truncate">
            CMS Blueprint System
          </h2>
          <p class="mt-1 text-sm text-gray-500">
            YAML-based content modeling and form generation
          </p>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Available Blueprints
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ Object.keys(blueprints).length }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Field Types
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ Object.keys(fieldTypes).length }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <div class="bg-white overflow-hidden shadow rounded-lg">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.998 1.998 0 013 12V7a4 4 0 014-4z" />
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    Categories
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ categories.length }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Blueprints Grid -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md mb-8">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Available Blueprints
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Click on a blueprint to view its structure or create a form
          </p>
        </div>
        <ul class="divide-y divide-gray-200">
          <li v-for="(blueprint, handle) in blueprints" :key="handle" class="px-4 py-4 hover:bg-gray-50">
            <div class="flex items-center justify-between">
              <div class="flex items-center">
                <div class="flex-shrink-0">
                  <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                  </div>
                </div>
                <div class="ml-4">
                  <div class="flex items-center">
                    <div class="text-sm font-medium text-gray-900">
                      {{ blueprint.title || handle }}
                    </div>
                    <div class="ml-2 flex-shrink-0 flex">
                      <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                        {{ handle }}
                      </span>
                    </div>
                  </div>
                  <div class="text-sm text-gray-500">
                    {{ Object.keys(blueprint.sections || {}).length }} sections, 
                    {{ countFields(blueprint) }} fields
                  </div>
                </div>
              </div>
              <div class="flex space-x-2">
                <router-link
                  :href="route('cp.blueprints.show', handle)"
                  class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  View Structure
                </router-link>
                <router-link
                  :href="route('cp.blueprints.form', handle)"
                  class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                >
                  Create Form
                </router-link>
              </div>
            </div>
          </li>
        </ul>
      </div>

      <!-- Field Types by Category -->
      <div class="bg-white shadow overflow-hidden sm:rounded-md">
        <div class="px-4 py-5 sm:px-6 border-b border-gray-200">
          <h3 class="text-lg leading-6 font-medium text-gray-900">
            Available Field Types
          </h3>
          <p class="mt-1 max-w-2xl text-sm text-gray-500">
            Field types available for use in blueprints
          </p>
        </div>
        <div class="px-4 py-5 sm:p-6">
          <div v-for="category in categories" :key="category" class="mb-6 last:mb-0">
            <h4 class="text-sm font-medium text-gray-900 mb-3 capitalize">
              {{ category }} Fields
            </h4>
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-3">
              <div
                v-for="(config, type) in getFieldsByCategory(category)"
                :key="type"
                class="bg-gray-50 rounded-lg p-3 text-center hover:bg-gray-100 transition-colors"
              >
                <div class="text-sm font-medium text-gray-900">{{ type }}</div>
                <div class="text-xs text-gray-500 mt-1">{{ config.component || 'Default' }}</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </Page>
</template>

<script setup>
import { computed } from 'vue'
import Page from '@/components/page.vue'

const props = defineProps({
  blueprints: Object,
  fieldTypes: Object,
  categories: Array,
})

// Count total fields in a blueprint
const countFields = (blueprint) => {
  let count = 0
  
  Object.values(blueprint.sections || {}).forEach(section => {
    count += (section.fields || []).length
  })
  
  return count
}

// Get field types by category
const getFieldsByCategory = (category) => {
  return Object.fromEntries(
    Object.entries(props.fieldTypes).filter(([type, config]) => 
      (config.category || 'misc') === category
    )
  )
}
</script>