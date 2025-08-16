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
  min-height: 100vh;
  background-color: #f9fafb;
}

.page-header {
  background-color: white;
  border-bottom: 1px solid #e5e7eb;
}

.page-header-content {
  max-width: 80rem;
  margin: 0 auto;
  padding: 1.5rem 1rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.page-title-wrapper {
  flex: 1;
  min-width: 0;
}

.breadcrumbs {
  margin-bottom: 0.5rem;
}

.breadcrumb-nav {
  display: flex;
}

.breadcrumb-list {
  display: flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.breadcrumb-item {
  display: flex;
  align-items: center;
}

.breadcrumb-link {
  color: #2563eb;
}

.breadcrumb-link:hover {
  color: #1d4ed8;
}

.breadcrumb-separator {
  margin: 0 0.5rem;
  color: #9ca3af;
}

.page-title-section {
  display: flex;
  flex-direction: column;
}

.page-title {
  font-size: 1.5rem;
  font-weight: 700;
  color: #111827;
  line-height: 1.25;
}

.page-subtitle {
  margin-top: 0.25rem;
  font-size: 0.875rem;
  color: #6b7280;
}

.page-actions {
  margin-left: 1rem;
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.page-tabs {
  border-top: 1px solid #e5e7eb;
  background-color: white;
}

.tab-navigation {
  max-width: 80rem;
  margin: 0 auto;
  padding: 0 1rem;
  display: flex;
  gap: 2rem;
}

.tab-button {
  padding: 1rem 0.25rem;
  border-bottom: 2px solid transparent;
  font-weight: 500;
  font-size: 0.875rem;
  color: #6b7280;
  transition: all 0.2s;
}

.tab-button:hover {
  color: #374151;
  border-bottom-color: #d1d5db;
}

.tab-active {
  border-bottom-color: #3b82f6;
  color: #2563eb;
}

.tab-disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.page-content {
  flex: 1;
}

.page-content-container {
  max-width: 80rem;
  margin: 0 auto;
  padding: 2rem 1rem;
}

.page-content-full {
  width: 100%;
  padding: 2rem 0;
}

.page-loading {
  opacity: 0.75;
  pointer-events: none;
}
</style>
