<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router, useForm } from '@inertiajs/vue3'
import Card from '../../Components/ui/Card.vue'
import CardHeader from '../../Components/ui/CardHeader.vue'
import CardTitle from '../../Components/ui/CardTitle.vue'
import CardContent from '../../Components/ui/CardContent.vue'
import AdminLayout from '@/layouts/admin-layout.vue'
import MetaobjectDefinitionForm from '@/components/metaobjects/metaobject-definition-form.vue'
import MetaobjectInstanceForm from '@/components/metaobjects/metaobject-instance-form.vue'
import DataTable from '@/components/ui/data-table.vue'
import SearchInput from '@/components/ui/search-input.vue'
import { formatDate } from '@/utils/formatters'

interface MetafieldDefinition {
  id: number
  key: string
  name: string
  type: string
  required: boolean
  validation_rules: Record<string, any>
}

interface MetaobjectDefinition {
  id: number
  handle: string
  name: string
  description: string | null
  metafield_definitions: MetafieldDefinition[]
  created_at: string
  updated_at: string
}

interface MetaobjectInstance {
  id: number
  handle: string
  metaobject_definition_id: number
  metafields: Array<{
    id: number
    key: string
    value: any
    metafield_definition: MetafieldDefinition
  }>
  created_at: string
  updated_at: string
}

interface Props {
  definitions?: {
    data: MetaobjectDefinition[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  instances?: {
    data: MetaobjectInstance[]
    current_page: number
    last_page: number
    per_page: number
    total: number
  }
  definition?: MetaobjectDefinition
  instance?: MetaobjectInstance
  mode: 'definitions' | 'instances' | 'definition-form' | 'instance-form'
  definition_id?: number
}

const props = defineProps<Props>()

const searchTerm = ref('')
const showDeleteModal = ref(false)
const itemToDelete = ref<MetaobjectDefinition | MetaobjectInstance | null>(null)

// Navigation helpers
const goToDefinitions = () => {
  router.get('/admin/metaobjects')
}

const goToInstances = (definitionId: number) => {
  router.get(`/admin/metaobjects/${definitionId}/instances`)
}

const createDefinition = () => {
  router.get('/admin/metaobjects/create')
}

const editDefinition = (definition: MetaobjectDefinition) => {
  router.get(`/admin/metaobjects/${definition.id}/edit`)
}

const createInstance = (definitionId: number) => {
  router.get(`/admin/metaobjects/${definitionId}/instances/create`)
}

const editInstance = (instance: MetaobjectInstance) => {
  router.get(`/admin/metaobjects/${instance.metaobject_definition_id}/instances/${instance.id}/edit`)
}

// Delete functionality
const confirmDelete = (item: MetaobjectDefinition | MetaobjectInstance) => {
  itemToDelete.value = item
  showDeleteModal.value = true
}

const deleteForm = useForm({})

const performDelete = () => {
  if (!itemToDelete.value) return
  
  const isDefinition = 'metafield_definitions' in itemToDelete.value
  const url = isDefinition 
    ? `/admin/metaobjects/${itemToDelete.value.id}` 
    : `/admin/metaobjects/${(itemToDelete.value as MetaobjectInstance).metaobject_definition_id}/instances/${itemToDelete.value.id}`
  
  deleteForm.delete(url, {
    onSuccess: () => {
      showDeleteModal.value = false
      itemToDelete.value = null
    },
  })
}

// Get metafield type badge class
const getMetafieldTypeBadgeClass = (type: string) => {
  const classes = {
    'single_line_text': 'bg-blue-100 text-blue-800',
    'multi_line_text': 'bg-blue-100 text-blue-800',
    'rich_text': 'bg-purple-100 text-purple-800',
    'number_integer': 'bg-green-100 text-green-800',
    'number_decimal': 'bg-green-100 text-green-800',
    'boolean': 'bg-yellow-100 text-yellow-800',
    'date': 'bg-indigo-100 text-indigo-800',
    'url': 'bg-pink-100 text-pink-800',
    'json': 'bg-gray-100 text-gray-800',
    'file': 'bg-orange-100 text-orange-800',
  }
  return classes[type as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

// Format metafield value for display
const formatMetafieldValue = (value: any, type: string) => {
  if (value === null || value === undefined) return '-'
  
  switch (type) {
    case 'boolean':
      return value ? 'Yes' : 'No'
    case 'date':
      return formatDate(value)
    case 'json':
      return typeof value === 'object' ? JSON.stringify(value) : value
    case 'file':
      return value.filename || value.name || 'File'
    default:
      return String(value)
  }
}

// Table columns for definitions
const definitionColumns = [
  { key: 'name', label: 'Name', sortable: true },
  { key: 'handle', label: 'Handle', sortable: true },
  { key: 'metafield_count', label: 'Fields', sortable: false },
  { key: 'created_at', label: 'Created', sortable: true },
  { key: 'actions', label: 'Actions', sortable: false },
]

// Table columns for instances
const instanceColumns = [
  { key: 'handle', label: 'Handle', sortable: true },
  { key: 'metafields', label: 'Values', sortable: false },
  { key: 'created_at', label: 'Created', sortable: true },
  { key: 'actions', label: 'Actions', sortable: false },
]

// Page title
const pageTitle = computed(() => {
  switch (props.mode) {
    case 'definitions':
      return 'Metaobject Definitions'
    case 'instances':
      return `Metaobject Instances - ${props.definition?.name}`
    case 'definition-form':
      return props.definition ? 'Edit Definition' : 'Create Definition'
    case 'instance-form':
      return props.instance ? 'Edit Instance' : 'Create Instance'
    default:
      return 'Metaobjects'
  }
})
</script>

<template>
  <Head :title="pageTitle" />
  
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header with breadcrumbs -->
      <div class="flex items-center justify-between">
        <div>
          <nav class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
            <button
              @click="goToDefinitions"
              class="hover:text-gray-900"
              :class="{ 'text-gray-900': mode === 'definitions' }"
            >
              Definitions
            </button>
            <template v-if="mode !== 'definitions'">
              <span>/</span>
              <span v-if="mode === 'instances'" class="text-gray-900">
                {{ definition?.name }} Instances
              </span>
              <span v-else-if="mode === 'definition-form'" class="text-gray-900">
                {{ definition ? 'Edit' : 'Create' }} Definition
              </span>
              <span v-else-if="mode === 'instance-form'" class="text-gray-900">
                {{ instance ? 'Edit' : 'Create' }} Instance
              </span>
            </template>
          </nav>
          
          <h1 class="text-3xl font-bold text-gray-900">{{ pageTitle }}</h1>
          <p v-if="mode === 'definitions'" class="text-gray-600 mt-1">
            Define custom data structures for your store
          </p>
          <p v-else-if="mode === 'instances'" class="text-gray-600 mt-1">
            Manage instances of {{ definition?.name }}
          </p>
        </div>
        
        <!-- Action buttons -->
        <div class="flex items-center gap-3">
          <button
            v-if="mode === 'definitions'"
            @click="createDefinition"
            class="btn btn-primary flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Definition
          </button>
          
          <button
            v-else-if="mode === 'instances' && definition"
            @click="createInstance(definition.id)"
            class="btn btn-primary flex items-center gap-2"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Create Instance
          </button>
        </div>
      </div>

      <!-- Definitions List -->
      <template v-if="mode === 'definitions' && definitions">
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>
                Definitions ({{ definitions.total.toLocaleString() }})
              </CardTitle>
              
              <SearchInput
                v-model="searchTerm"
                placeholder="Search definitions..."
                class="max-w-sm"
              />
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Name
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Handle
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Fields
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Created
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="definition in definitions.data"
                    :key="definition.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4">
                      <div>
                        <div class="font-medium text-gray-900">{{ definition.name }}</div>
                        <div v-if="definition.description" class="text-sm text-gray-500">
                          {{ definition.description }}
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <code class="bg-gray-100 px-2 py-1 rounded">{{ definition.handle }}</code>
                    </td>
                    <td class="px-6 py-4">
                      <div class="flex flex-wrap gap-1">
                        <span
                          v-for="field in definition.metafield_definitions.slice(0, 3)"
                          :key="field.id"
                          :class="[
                            'inline-block px-2 py-1 text-xs font-medium rounded-full',
                            getMetafieldTypeBadgeClass(field.type)
                          ]"
                        >
                          {{ field.name }}
                        </span>
                        <span
                          v-if="definition.metafield_definitions.length > 3"
                          class="inline-block px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800"
                        >
                          +{{ definition.metafield_definitions.length - 3 }}
                        </span>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(definition.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                      <div class="flex items-center gap-2">
                        <button
                          @click="goToInstances(definition.id)"
                          class="text-blue-600 hover:text-blue-900"
                        >
                          Instances
                        </button>
                        <button
                          @click="editDefinition(definition)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </button>
                        <button
                          @click="confirmDelete(definition)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Pagination for definitions -->
            <div v-if="definitions.last_page > 1" class="px-6 py-4 border-t border-gray-200">
              <div class="flex items-center justify-between">
                <div class="text-sm text-gray-700">
                  Showing {{ (definitions.current_page - 1) * definitions.per_page + 1 }} to 
                  {{ Math.min(definitions.current_page * definitions.per_page, definitions.total) }} 
                  of {{ definitions.total.toLocaleString() }} results
                </div>
                
                <div class="flex items-center gap-2">
                  <button
                    :disabled="definitions.current_page === 1"
                    @click="router.get(`/admin/metaobjects?page=${definitions.current_page - 1}`)"
                    class="btn btn-outline btn-sm"
                  >
                    Previous
                  </button>
                  
                  <span class="text-sm text-gray-700">
                    Page {{ definitions.current_page }} of {{ definitions.last_page }}
                  </span>
                  
                  <button
                    :disabled="definitions.current_page === definitions.last_page"
                    @click="router.get(`/admin/metaobjects?page=${definitions.current_page + 1}`)"
                    class="btn btn-outline btn-sm"
                  >
                    Next
                  </button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>
      </template>

      <!-- Instances List -->
      <template v-if="mode === 'instances' && instances && definition">
        <Card>
          <CardHeader>
            <div class="flex items-center justify-between">
              <CardTitle>
                Instances ({{ instances.total.toLocaleString() }})
              </CardTitle>
              
              <SearchInput
                v-model="searchTerm"
                placeholder="Search instances..."
                class="max-w-sm"
              />
            </div>
          </CardHeader>
          <CardContent class="p-0">
            <div class="overflow-x-auto">
              <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Handle
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Values
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Created
                    </th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                      Actions
                    </th>
                  </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                  <tr
                    v-for="instance in instances.data"
                    :key="instance.id"
                    class="hover:bg-gray-50"
                  >
                    <td class="px-6 py-4 text-sm text-gray-900">
                      <code class="bg-gray-100 px-2 py-1 rounded">{{ instance.handle }}</code>
                    </td>
                    <td class="px-6 py-4">
                      <div class="space-y-1">
                        <div
                          v-for="metafield in instance.metafields.slice(0, 3)"
                          :key="metafield.id"
                          class="text-sm"
                        >
                          <span class="font-medium text-gray-700">{{ metafield.metafield_definition.name }}:</span>
                          <span class="text-gray-900 ml-1">
                            {{ formatMetafieldValue(metafield.value, metafield.metafield_definition.type) }}
                          </span>
                        </div>
                        <div v-if="instance.metafields.length > 3" class="text-sm text-gray-500">
                          +{{ instance.metafields.length - 3 }} more fields
                        </div>
                      </div>
                    </td>
                    <td class="px-6 py-4 text-sm text-gray-500">
                      {{ formatDate(instance.created_at) }}
                    </td>
                    <td class="px-6 py-4 text-sm font-medium">
                      <div class="flex items-center gap-2">
                        <button
                          @click="editInstance(instance)"
                          class="text-indigo-600 hover:text-indigo-900"
                        >
                          Edit
                        </button>
                        <button
                          @click="confirmDelete(instance)"
                          class="text-red-600 hover:text-red-900"
                        >
                          Delete
                        </button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </CardContent>
        </Card>
      </template>

      <!-- Definition Form -->
      <template v-if="mode === 'definition-form'">
        <MetaobjectDefinitionForm :definition="definition" />
      </template>

      <!-- Instance Form -->
      <template v-if="mode === 'instance-form' && definition">
        <MetaobjectInstanceForm
          :definition="definition"
          :instance="instance"
        />
      </template>

      <!-- Delete Confirmation Modal -->
      <div
        v-if="showDeleteModal"
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50"
        @click.self="showDeleteModal = false"
      >
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
          <h3 class="text-lg font-medium text-gray-900 mb-4">
            Confirm Deletion
          </h3>
          
          <p class="text-gray-600 mb-6">
            Are you sure you want to delete this 
            {{ itemToDelete && 'metafield_definitions' in itemToDelete ? 'definition' : 'instance' }}?
            This action cannot be undone.
          </p>
          
          <div class="flex justify-end gap-3">
            <button
              @click="showDeleteModal = false"
              class="btn btn-outline"
              :disabled="deleteForm.processing"
            >
              Cancel
            </button>
            <button
              @click="performDelete"
              class="btn btn-danger"
              :disabled="deleteForm.processing"
            >
              <span v-if="deleteForm.processing">Deleting...</span>
              <span v-else>Delete</span>
            </button>
          </div>
        </div>
      </div>
    </div>
  </AdminLayout>
</template>
