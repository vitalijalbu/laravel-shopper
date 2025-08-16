<template>
  <div class="min-h-full">
    <!-- Page header -->
    <div class="bg-white shadow-sm">
      <div class="container mx-auto px-4 sm:px-6 lg:px-8">
        <div class="py-6">
          <!-- Breadcrumb -->
          <nav
            v-if="breadcrumbs.length"
            class="flex mb-4"
            aria-label="Breadcrumb"
          >
            <ol role="list" class="flex items-center space-x-4">
              <li v-for="(breadcrumb, index) in breadcrumbs" :key="index">
                <div class="flex items-center">
                  <svg
                    v-if="index > 0"
                    class="flex-shrink-0 h-5 w-5 text-gray-400 mr-4"
                    xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor"
                    viewBox="0 0 20 20"
                    aria-hidden="true"
                  >
                    <path d="M5.555 17.776l8-16 .894.448-8 16-.894-.448z" />
                  </svg>
                  <Link
                    v-if="breadcrumb.href && index < breadcrumbs.length - 1"
                    :href="breadcrumb.href"
                    class="text-sm font-medium text-gray-500 hover:text-gray-700"
                  >
                    {{ breadcrumb.name }}
                  </Link>
                  <span
                    v-else
                    class="text-sm font-medium"
                    :class="
                      index === breadcrumbs.length - 1
                        ? 'text-gray-900'
                        : 'text-gray-500'
                    "
                  >
                    {{ breadcrumb.name }}
                  </span>
                </div>
              </li>
            </ol>
          </nav>

          <!-- Page title & actions -->
          <div class="flex items-center justify-between">
            <div class="min-w-0 flex-1">
              <h1
                class="text-2xl font-bold leading-7 text-gray-900 sm:truncate sm:text-3xl sm:tracking-tight"
              >
                {{ title }}
              </h1>
              <p v-if="subtitle" class="mt-1 text-sm text-gray-500">
                {{ subtitle }}
              </p>
            </div>

            <!-- Actions -->
            <div
              v-if="actions.length || $slots.actions"
              class="flex items-center space-x-3"
            >
              <slot name="actions">
                <component
                  v-for="action in actions"
                  :key="action.key"
                  :is="action.href ? Link : 'button'"
                  :href="action.href"
                  :type="action.href ? undefined : action.type || 'button'"
                  :class="getActionClass(action)"
                  @click="action.onClick"
                >
                  <component
                    v-if="action.icon"
                    :is="action.icon"
                    class="w-4 h-4"
                    :class="{ 'mr-2': action.label }"
                  />
                  {{ action.label }}
                </component>
              </slot>
            </div>
          </div>

          <!-- Tabs/Navigation -->
          <nav
            v-if="tabs.length"
            class="flex space-x-8 mt-6 border-b border-gray-200"
          >
            <button
              v-for="tab in tabs"
              :key="tab.key"
              @click="activeTab = tab.key"
              :class="[
                'py-2 px-1 border-b-2 font-medium text-sm',
                activeTab === tab.key
                  ? 'border-indigo-500 text-indigo-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
              ]"
            >
              {{ tab.name }}
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Page content -->
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Loading state -->
      <div v-if="loading" class="flex items-center justify-center py-12">
        <div
          class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"
        ></div>
        <span class="ml-2 text-gray-600">{{ loadingText }}</span>
      </div>

      <!-- Error state -->
      <div v-else-if="error" class="rounded-md bg-red-50 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg
              class="h-5 w-5 text-red-400"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.28 7.22a.75.75 0 00-1.06 1.06L8.94 10l-1.72 1.72a.75.75 0 101.06 1.06L10 11.06l1.72 1.72a.75.75 0 101.06-1.06L11.06 10l1.72-1.72a.75.75 0 00-1.06-1.06L10 8.94 8.28 7.22z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <h3 class="text-sm font-medium text-red-800">Error</h3>
            <div class="mt-2 text-sm text-red-700">
              {{ error }}
            </div>
          </div>
        </div>
      </div>

      <!-- Success message -->
      <div v-if="success" class="rounded-md bg-green-50 p-4 mb-6">
        <div class="flex">
          <div class="flex-shrink-0">
            <svg
              class="h-5 w-5 text-green-400"
              xmlns="http://www.w3.org/2000/svg"
              viewBox="0 0 20 20"
              fill="currentColor"
            >
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.236 4.53L7.53 10.53a.75.75 0 00-1.06 1.06l2.25 2.25a.75.75 0 001.14-.094l3.75-5.25z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <p class="text-sm font-medium text-green-800">{{ success }}</p>
          </div>
        </div>
      </div>

      <!-- Tab content or main content -->
      <div v-if="!loading && !error">
        <!-- With tabs -->
        <template v-if="tabs.length">
          <div
            v-for="tab in tabs"
            :key="tab.key"
            v-show="activeTab === tab.key"
          >
            <slot :name="`tab-${tab.key}`" :tab="tab">
              <component
                v-if="tab.component"
                :is="tab.component"
                v-bind="tab.props || {}"
              />
            </slot>
          </div>
        </template>

        <!-- Without tabs -->
        <template v-else>
          <slot :loading="loading" :error="error" :success="success" />
        </template>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from "vue";
import { Link } from "@inertiajs/vue3";

// Props
const props = defineProps({
  title: {
    type: String,
    required: true,
  },
  subtitle: String,
  breadcrumbs: {
    type: Array,
    default: () => [],
  },
  actions: {
    type: Array,
    default: () => [],
  },
  tabs: {
    type: Array,
    default: () => [],
  },
  defaultTab: String,
  loading: {
    type: Boolean,
    default: false,
  },
  error: String,
  success: String,
  loadingText: {
    type: String,
    default: "Loading...",
  },
});

// State
const activeTab = ref(
  props.defaultTab || (props.tabs.length ? props.tabs[0].key : null),
);

// Methods
const getActionClass = (action) => {
  const baseClass =
    "inline-flex items-center px-4 py-2 border text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2";

  const variants = {
    primary:
      "border-transparent text-white bg-indigo-600 hover:bg-indigo-700 focus:ring-indigo-500",
    secondary:
      "border-gray-300 text-gray-700 bg-white hover:bg-gray-50 focus:ring-indigo-500",
    danger:
      "border-transparent text-white bg-red-600 hover:bg-red-700 focus:ring-red-500",
    success:
      "border-transparent text-white bg-green-600 hover:bg-green-700 focus:ring-green-500",
  };

  return `${baseClass} ${variants[action.variant] || variants.secondary}`;
};

// Emit tab changes
const emit = defineEmits(["tab-change"]);

// Watch active tab
import { watch } from "vue";
watch(activeTab, (newTab) => {
  emit("tab-change", newTab);
});
</script>
