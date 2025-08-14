<template>
  <div :class="pageClasses">
    <!-- Page Header -->
    <div v-if="hasHeader" class="page-header">
      <div class="page-header-content">
        <div class="page-title-wrapper">
          <div v-if="breadcrumbs" class="breadcrumbs">
            <nav class="breadcrumb-nav">
              <ol class="breadcrumb-list">
                <li v-for="(crumb, index) in breadcrumbs" :key="index" class="breadcrumb-item">
                  <component 
                    :is="crumb.href ? 'router-link' : 'span'"
                    :to="crumb.href"
                    :class="{ 'breadcrumb-link': crumb.href }"
                  >
                    {{ crumb.title }}
                  </component>
                  <span v-if="index < breadcrumbs.length - 1" class="breadcrumb-separator">/</span>
                </li>
              </ol>
            </nav>
          </div>
          
          <div class="page-title-section">
            <h1 v-if="title" class="page-title">{{ title }}</h1>
            <p v-if="subtitle" class="page-subtitle">{{ subtitle }}</p>
          </div>
        </div>

        <div v-if="hasActions" class="page-actions">
          <slot name="actions" />
        </div>
      </div>

      <div v-if="hasTabs" class="page-tabs">
        <nav class="tab-navigation">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            :class="tabClasses(tab)"
            @click="$emit('tab-change', tab.id)"
          >
            {{ tab.title }}
          </button>
        </nav>
      </div>
    </div>

    <!-- Page Content -->
    <div class="page-content">
      <div v-if="fullWidth" class="page-content-full">
        <slot />
      </div>
      <div v-else class="page-content-container">
        <slot />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  title: String,
  subtitle: String,
  breadcrumbs: Array,
  tabs: Array,
  activeTab: String,
  fullWidth: {
    type: Boolean,
    default: false
  },
  loading: {
    type: Boolean,
    default: false
  }
})

const emit = defineEmits(['tab-change'])

const hasHeader = computed(() => {
  return props.title || props.subtitle || props.breadcrumbs || props.tabs
})

const hasActions = computed(() => {
  // Check if actions slot has content
  return true // This would need to be properly implemented
})

const hasTabs = computed(() => {
  return props.tabs && props.tabs.length > 0
})

const pageClasses = computed(() => {
  return [
    'shopper-page',
    {
      'page-full-width': props.fullWidth,
      'page-loading': props.loading,
      'page-with-tabs': hasTabs.value
    }
  ]
})

const tabClasses = (tab) => {
  return [
    'tab-button',
    {
      'tab-active': tab.id === props.activeTab,
      'tab-disabled': tab.disabled
    }
  ]
}
</script>

<style scoped>
.shopper-page {
  @apply min-h-screen bg-gray-50;
}

.page-header {
  @apply bg-white border-b border-gray-200;
}

.page-header-content {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6;
  @apply flex items-center justify-between;
}

.page-title-wrapper {
  @apply flex-1 min-w-0;
}

.breadcrumbs {
  @apply mb-2;
}

.breadcrumb-nav {
  @apply flex;
}

.breadcrumb-list {
  @apply flex items-center space-x-1 text-sm text-gray-500;
}

.breadcrumb-item {
  @apply flex items-center;
}

.breadcrumb-link {
  @apply text-blue-600 hover:text-blue-700;
}

.breadcrumb-separator {
  @apply mx-2 text-gray-400;
}

.page-title-section {
  @apply flex flex-col;
}

.page-title {
  @apply text-2xl font-bold text-gray-900 leading-tight;
}

.page-subtitle {
  @apply mt-1 text-sm text-gray-500;
}

.page-actions {
  @apply ml-4 flex items-center space-x-3;
}

.page-tabs {
  @apply border-t border-gray-200 bg-white;
}

.tab-navigation {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8;
  @apply flex space-x-8;
}

.tab-button {
  @apply py-4 px-1 border-b-2 font-medium text-sm;
  @apply border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300;
  @apply transition-colors duration-200;
}

.tab-active {
  @apply border-blue-500 text-blue-600;
}

.tab-disabled {
  @apply opacity-50 cursor-not-allowed;
}

.page-content {
  @apply flex-1;
}

.page-content-container {
  @apply max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8;
}

.page-content-full {
  @apply w-full py-8;
}

.page-loading {
  @apply opacity-75 pointer-events-none;
}
</style>
