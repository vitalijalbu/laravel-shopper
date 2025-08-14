<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, useForm } from '@inertiajs/vue3'
import { Card, CardHeader, CardTitle, CardContent } from '@reka-ui/core'
import AdminLayout from '@/layouts/admin-layout.vue'
import { Switch } from '@reka-ui/core'

interface PreferenceOption {
  name: string
  type: 'boolean' | 'string' | 'number' | 'array' | 'select'
  default: any
  options?: Record<string, string> | Record<number, string>
  min?: number
  max?: number
}

interface PreferenceGroup {
  name: string
  description: string
  preferences: Record<string, PreferenceOption>
}

interface UserPreference {
  id: number
  type: string
  key: string
  value: any
  created_at: string
  updated_at: string
}

interface Props {
  preferences: Record<string, UserPreference[]>
  default_preferences: Record<string, PreferenceGroup>
}

const props = defineProps<Props>()

const form = useForm({
  preferences: [] as Array<{
    type: string
    key: string
    value: any
  }>
})

const activeTab = ref('dashboard')
const hasUnsavedChanges = ref(false)
const showResetModal = ref(false)
const resetType = ref<string | null>(null)

// Build current preferences state
const currentPreferences = ref<Record<string, Record<string, any>>>({})

// Initialize current preferences
const initializePreferences = () => {
  Object.keys(props.default_preferences).forEach(type => {
    currentPreferences.value[type] = {}
    
    Object.keys(props.default_preferences[type].preferences).forEach(key => {
      const defaultValue = props.default_preferences[type].preferences[key].default
      
      // Check if user has a preference for this
      const userPref = props.preferences[type]?.find(p => p.key === key)
      currentPreferences.value[type][key] = userPref ? userPref.value : defaultValue
    })
  })
}

// Initialize preferences on mount
initializePreferences()

// Watch for changes to detect unsaved changes
watch(currentPreferences, () => {
  hasUnsavedChanges.value = true
}, { deep: true })

// Get preference groups as array
const preferenceGroups = computed(() => {
  return Object.entries(props.default_preferences).map(([key, group]) => ({
    key,
    ...group
  }))
})

// Handle preference change
const updatePreference = (type: string, key: string, value: any) => {
  if (!currentPreferences.value[type]) {
    currentPreferences.value[type] = {}
  }
  currentPreferences.value[type][key] = value
}

// Handle array preference toggle (like dashboard widgets)
const toggleArrayPreference = (type: string, key: string, option: string) => {
  const current = currentPreferences.value[type]?.[key] || []
  const newValue = current.includes(option)
    ? current.filter((item: string) => item !== option)
    : [...current, option]
  
  updatePreference(type, key, newValue)
}

// Check if array preference option is selected
const isArrayOptionSelected = (type: string, key: string, option: string) => {
  const current = currentPreferences.value[type]?.[key] || []
  return current.includes(option)
}

// Save preferences
const savePreferences = () => {
  const preferencesToSave = []
  
  Object.entries(currentPreferences.value).forEach(([type, preferences]) => {
    Object.entries(preferences).forEach(([key, value]) => {
      preferencesToSave.push({ type, key, value })
    })
  })
  
  form.preferences = preferencesToSave
  
  form.post('/admin/user-preferences/bulk-update', {
    onSuccess: () => {
      hasUnsavedChanges.value = false
    }
  })
}

// Reset preferences
const confirmReset = (type?: string) => {
  resetType.value = type || null
  showResetModal.value = true
}

const performReset = () => {
  const url = resetType.value 
    ? `/admin/user-preferences/reset?type=${resetType.value}`
    : '/admin/user-preferences/reset'
    
  form.post(url, {
    onSuccess: () => {
      // Reinitialize preferences
      initializePreferences()
      hasUnsavedChanges.value = false
      showResetModal.value = false
      resetType.value = null
    }
  })
}

// Export preferences
const exportPreferences = () => {
  window.open('/admin/user-preferences/export', '_blank')
}

// Import preferences
const importPreferences = (event: Event) => {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]
  
  if (file) {
    const reader = new FileReader()
    reader.onload = (e) => {
      try {
        const data = JSON.parse(e.target?.result as string)
        
        form.post('/admin/user-preferences/import', {
          data: {
            preferences: data.preferences,
            overwrite: true
          },
          onSuccess: () => {
            initializePreferences()
            hasUnsavedChanges.value = false
          }
        })
      } catch (error) {
        console.error('Error importing preferences:', error)
      }
    }
    reader.readAsText(file)
  }
}

// Get tab classes
const getTabClasses = (tabKey: string) => {
  return [
    'px-4 py-2 text-sm font-medium rounded-lg transition-colors',
    tabKey === activeTab.value
      ? 'bg-blue-100 text-blue-900'
      : 'text-gray-600 hover:text-gray-900 hover:bg-gray-100'
  ]
}

// Before unload warning
if (typeof window !== 'undefined') {
  window.addEventListener('beforeunload', (e) => {
    if (hasUnsavedChanges.value) {
      e.preventDefault()
      e.returnValue = ''
    }
  })
}
</script>

<template>
  <Head title="User Preferences" />
  
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">User Preferences</h1>
          <p class="text-gray-600 mt-1">Customize your admin experience and settings</p>
        </div>
        
        <div class="flex items-center gap-3">
          <input
            type="file"
            ref="fileInput"
            @change="importPreferences"
            accept=".json"
            class="hidden"
          />
          
          <button
            @click="$refs.fileInput.click()"
            class="btn btn-outline flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10" />
            </svg>
            Import
          </button>
          
          <button
            @click="exportPreferences"
            class="btn btn-outline flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
            </svg>
            Export
          </button>
          
          <button
            @click="confirmReset()"
            class="btn btn-outline text-red-600 hover:bg-red-50"
          >
            Reset All
          </button>
          
          <button
            @click="savePreferences"
            :disabled="!hasUnsavedChanges || form.processing"
            class="btn btn-primary"
          >
            <span v-if="form.processing">Saving...</span>
            <span v-else>Save Changes</span>
          </button>
        </div>
      </div>

      <!-- Unsaved Changes Warning -->
      <div
        v-if="hasUnsavedChanges"
        class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"
      >
        <div class="flex items-center">
          <svg class="w-5 h-5 text-yellow-400 mr-2" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
          <span class="text-yellow-800">You have unsaved changes</span>
        </div>
      </div>

      <div class="flex gap-6">
        <!-- Sidebar Navigation -->
        <div class="w-64 flex-shrink-0">
          <Card>
            <CardContent class="p-4">
              <nav class="space-y-2">
                <button
                  v-for="group in preferenceGroups"
                  :key="group.key"
                  @click="activeTab = group.key"
                  :class="getTabClasses(group.key)"
                  class="w-full text-left"
                >
                  {{ group.name }}
                </button>
              </nav>
            </CardContent>
          </Card>
        </div>

        <!-- Main Content -->
        <div class="flex-1">
          <div
            v-for="group in preferenceGroups"
            :key="group.key"
            v-show="activeTab === group.key"
          >
            <Card>
              <CardHeader>
                <div class="flex items-center justify-between">
                  <div>
                    <CardTitle>{{ group.name }}</CardTitle>
                    <p class="text-gray-600 mt-1">{{ group.description }}</p>
                  </div>
                  
                  <button
                    @click="confirmReset(group.key)"
                    class="btn btn-outline btn-sm text-red-600 hover:bg-red-50"
                  >
                    Reset Section
                  </button>
                </div>
              </CardHeader>
              <CardContent class="space-y-6">
                <div
                  v-for="(preference, key) in group.preferences"
                  :key="key"
                  class="space-y-2"
                >
                  <label class="block text-sm font-medium text-gray-700">
                    {{ preference.name }}
                  </label>
                  
                  <!-- Boolean preferences -->
                  <div v-if="preference.type === 'boolean'">
                    <Switch
                      :checked="currentPreferences[group.key]?.[key] || false"
                      @update:checked="(value: boolean) => updatePreference(group.key, key, value)"
                    />
                  </div>
                  
                  <!-- Select preferences -->
                  <select
                    v-else-if="preference.type === 'select'"
                    :value="currentPreferences[group.key]?.[key]"
                    @change="(e: Event) => updatePreference(group.key, key, (e.target as HTMLSelectElement).value)"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  >
                    <option
                      v-for="(label, value) in preference.options"
                      :key="value"
                      :value="value"
                    >
                      {{ label }}
                    </option>
                  </select>
                  
                  <!-- Number preferences -->
                  <input
                    v-else-if="preference.type === 'number'"
                    type="number"
                    :value="currentPreferences[group.key]?.[key]"
                    :min="preference.min"
                    :max="preference.max"
                    @input="(e: Event) => updatePreference(group.key, key, parseInt((e.target as HTMLInputElement).value))"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                  
                  <!-- String preferences -->
                  <input
                    v-else-if="preference.type === 'string'"
                    type="text"
                    :value="currentPreferences[group.key]?.[key]"
                    @input="(e: Event) => updatePreference(group.key, key, (e.target as HTMLInputElement).value)"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                  />
                  
                  <!-- Array preferences (checkboxes) -->
                  <div v-else-if="preference.type === 'array'" class="space-y-2">
                    <div
                      v-for="(label, option) in preference.options"
                      :key="option"
                      class="flex items-center"
                    >
                      <input
                        :id="`${group.key}_${key}_${option}`"
                        type="checkbox"
                        :checked="isArrayOptionSelected(group.key, key, String(option))"
                        @change="toggleArrayPreference(group.key, key, String(option))"
                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                      />
                      <label
                        :for="`${group.key}_${key}_${option}`"
                        class="ml-2 text-sm text-gray-700"
                      >
                        {{ label }}
                      </label>
                    </div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>

      <!-- Reset Confirmation Modal -->
      <div
        v-if="showResetModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showResetModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Confirm Reset
          </h3>
          
          <p class="text-gray-600 mb-6">
            Are you sure you want to reset 
            {{ resetType ? `${props.default_preferences[resetType]?.name.toLowerCase()} preferences` : 'all preferences' }}
            to their default values? This action cannot be undone.
          </p>
          
          <div class="flex justify-end gap-3">
            <button
              @click="showResetModal = false"
              class="btn btn-outline"
              :disabled="form.processing"
            >
              Cancel
            </button>
            <button
              @click="performReset"
              class="btn btn-danger"
              :disabled="form.processing"
            >
              <span v-if="form.processing">Resetting...</span>
              <span v-else>Reset</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
