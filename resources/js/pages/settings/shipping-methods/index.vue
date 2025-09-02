<template>
  <div>
    <Head :title="page.title" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Metodi di Spedizione</h1>
          <p class="mt-2 text-gray-600">Configura spedizioni, zone di consegna e calcolo dei costi</p>
        </div>
        <div class="flex space-x-3">
          <Link 
            :href="route('cp.settings.index')"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            <ArrowLeftIcon class="w-4 h-4 mr-2" />
            Torna alle Impostazioni
          </Link>
          <button
            @click="showCreateModal = true"
            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
          >
            <PlusIcon class="w-4 h-4 mr-2" />
            Aggiungi Metodo
          </button>
        </div>
      </div>

      <!-- Shipping Calculator -->
      <div class="mb-6 bg-gradient-to-r from-orange-50 to-red-50 border border-orange-200 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Calcolatore Spedizione</h2>
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Peso (kg)</label>
            <input
              v-model.number="calculator.weight"
              type="number"
              step="0.1"
              min="0"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
              placeholder="1.5"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paese</label>
            <select
              v-model="calculator.destination.country"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Seleziona paese</option>
              <option v-for="zone in zones" :key="zone.code" :value="zone.code">{{ zone.name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CAP</label>
            <input
              v-model="calculator.destination.zip_code"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
              placeholder="20100"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Valore ordine</label>
            <div class="relative">
              <input
                v-model.number="calculator.order_total"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-8 border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
                placeholder="100.00"
              />
              <span class="absolute left-3 top-2.5 text-gray-500">€</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Metodo</label>
            <select
              v-model="calculator.method_id"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500"
            >
              <option value="">Tutti i metodi</option>
              <option v-for="method in shippingMethods.data.filter(m => m.is_enabled)" :key="method.id" :value="method.id">
                {{ method.name }}
              </option>
            </select>
          </div>
          <div>
            <button
              @click="calculateShipping"
              :disabled="!calculator.destination.country"
              class="w-full px-4 py-2 text-sm font-medium text-white bg-orange-600 border border-transparent rounded-md hover:bg-orange-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Calcola
            </button>
          </div>
        </div>
        
        <!-- Shipping Calculation Result -->
        <div v-if="shippingCalculation" class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
          <div class="text-sm">
            <div class="font-medium text-gray-900 mb-2">Costi di Spedizione Calcolati:</div>
            <div v-if="Array.isArray(shippingCalculation)" class="space-y-2">
              <div v-for="(cost, index) in shippingCalculation" :key="index" class="flex justify-between items-center p-3 bg-gray-50 rounded">
                <div>
                  <span class="font-medium">{{ cost.method_name }}</span>
                  <span v-if="cost.processing_time" class="ml-2 text-gray-500">({{ cost.processing_time }})</span>
                </div>
                <div class="text-right">
                  <div class="font-medium">€{{ cost.cost.toFixed(2) }}</div>
                  <div v-if="cost.free_threshold" class="text-xs text-green-600">
                    Gratuito sopra €{{ cost.free_threshold }}
                  </div>
                </div>
              </div>
            </div>
            <div v-else class="flex justify-between items-center">
              <span>Costo spedizione:</span>
              <span class="font-medium">€{{ shippingCalculation.cost.toFixed(2) }}</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Filters -->
      <div class="mb-6 bg-white p-4 rounded-lg shadow-sm border border-gray-200">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
            <input
              v-model="filters.search"
              type="text"
              placeholder="Nome metodo..."
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tipo</label>
            <select
              v-model="filters.type"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tutti i tipi</option>
              <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
            <select
              v-model="filters.is_enabled"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tutti</option>
              <option value="1">Attivo</option>
              <option value="0">Inattivo</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Ordinamento</label>
            <select
              v-model="filters.sort"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="sort_order">Ordine</option>
              <option value="name">Nome</option>
              <option value="type">Tipo</option>
              <option value="cost">Costo</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Shipping Methods Table -->
      <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              Metodi Configurati ({{ shippingMethods.data.length }})
            </h2>
            <div class="text-sm text-gray-500">
              Attivi: {{ shippingMethods.data.filter(method => method.is_enabled).length }}
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Metodo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Costo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Zone
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ordine
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Azioni
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="method in shippingMethods.data" :key="method.id" class="hover:bg-gray-50">
                <!-- Method Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-r from-orange-500 to-red-600 flex items-center justify-center">
                        <TruckIcon class="h-6 w-6 text-white" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="text-sm font-medium text-gray-900">{{ method.name }}</div>
                      <div class="text-sm text-gray-500">{{ method.description }}</div>
                      <div v-if="method.processing_time" class="text-xs text-gray-400">{{ method.processing_time }}</div>
                    </div>
                  </div>
                </td>

                <!-- Type -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    getTypeColor(method.type),
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]">
                    {{ getTypeLabel(method.type) }}
                  </span>
                </td>

                <!-- Cost -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <div v-if="method.type === 'free'" class="text-green-600 font-medium">Gratuito</div>
                    <div v-else-if="method.type === 'local_pickup'" class="text-blue-600 font-medium">Ritiro</div>
                    <div v-else class="font-medium">€{{ method.cost.toFixed(2) }}</div>
                    <div v-if="method.minimum_order" class="text-xs text-gray-500">
                      Min. ordine: €{{ method.minimum_order.toFixed(2) }}
                    </div>
                  </div>
                </td>

                <!-- Zones -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <span v-if="method.zones && method.zones.length">
                      {{ method.zones.slice(0, 2).join(', ') }}
                      <span v-if="method.zones.length > 2" class="text-gray-500">
                        +{{ method.zones.length - 2 }}
                      </span>
                    </span>
                    <span v-else class="text-gray-500">Tutte le zone</span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStatus(method)"
                    :class="[
                      method.is_enabled 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800',
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:opacity-80'
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1" :class="method.is_enabled ? 'bg-green-600' : 'bg-red-600'"></span>
                    {{ method.is_enabled ? 'Attivo' : 'Inattivo' }}
                  </button>
                </td>

                <!-- Sort Order -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ method.sort_order }}</div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- Duplicate -->
                    <button
                      @click="duplicateMethod(method)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Duplica"
                    >
                      <DocumentDuplicateIcon class="h-5 w-5" />
                    </button>

                    <!-- Edit -->
                    <button
                      @click="editMethod(method)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Modifica"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>

                    <!-- View -->
                    <Link
                      :href="route('cp.settings.shipping-methods.show', method.id)"
                      class="text-gray-600 hover:text-gray-900"
                      title="Visualizza"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </Link>

                    <!-- Delete -->
                    <button
                      @click="deleteMethod(method)"
                      class="text-red-600 hover:text-red-900"
                      title="Elimina"
                    >
                      <TrashIcon class="h-5 w-5" />
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- Empty State -->
        <div v-if="shippingMethods.data.length === 0" class="text-center py-12">
          <TruckIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun metodo configurato</h3>
          <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo il tuo primo metodo di spedizione</p>
          <div class="mt-6">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Aggiungi Metodo
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="shippingMethods.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ shippingMethods.from }} to {{ shippingMethods.to }} of {{ shippingMethods.total }} results
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in shippingMethods.links"
                :key="link.label"
                :href="link.url"
                :class="[
                  link.active
                    ? 'bg-blue-600 text-white'
                    : 'bg-white text-gray-700 hover:bg-gray-50',
                  'px-3 py-2 text-sm border rounded-md'
                ]"
                v-html="link.label"
              />
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <ShippingMethodModal
      :show="showCreateModal || showEditModal"
      :shipping-method="selectedMethod"
      :zones="zones"
      :types="types"
      @close="closeModal"
      @saved="handleMethodSaved"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Elimina Metodo di Spedizione"
      :message="`Sei sicuro di voler eliminare il metodo '${selectedMethod?.name}'? Questa azione non può essere annullata.`"
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import {
  ArrowLeftIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'
import ShippingMethodModal from './ShippingMethodModal.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({
  page: Object,
  navigation: Object,
  shippingMethods: Object,
  zones: Object,
  types: Object,
  filters: Object
})

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedMethod = ref(null)

// Shipping calculator
const calculator = ref({
  weight: null,
  destination: {
    country: '',
    zip_code: ''
  },
  order_total: null,
  method_id: '',
  dimensions: {
    length: 0,
    width: 0,
    height: 0
  }
})

const shippingCalculation = ref(null)

// Filters
const filters = ref({
  search: props.filters.search || '',
  type: props.filters.type || '',
  zone: props.filters.zone || '',
  is_enabled: props.filters.is_enabled || '',
  sort: props.filters.sort || 'sort_order',
  direction: props.filters.direction || 'asc'
})

// Watch filters and update URL
watch(filters, (newFilters) => {
  router.get(route('cp.settings.shipping-methods.index'), newFilters, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

// Helper methods
const getTypeLabel = (type) => {
  const typeObj = props.types.find(t => t.value === type)
  return typeObj ? typeObj.label : type
}

const getTypeColor = (type) => {
  const colors = {
    'flat_rate': 'bg-blue-100 text-blue-800',
    'free': 'bg-green-100 text-green-800',
    'local_pickup': 'bg-purple-100 text-purple-800',
    'weight_based': 'bg-orange-100 text-orange-800',
    'zone_based': 'bg-indigo-100 text-indigo-800'
  }
  return colors[type] || 'bg-gray-100 text-gray-800'
}

// Shipping Method actions
const editMethod = (method) => {
  selectedMethod.value = method
  showEditModal.value = true
}

const deleteMethod = (method) => {
  selectedMethod.value = method
  showDeleteModal.value = true
}

const duplicateMethod = (method) => {
  router.post(route('cp.settings.shipping-methods.duplicate', method.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Method duplicated
    }
  })
}

const toggleStatus = (method) => {
  router.patch(route('cp.settings.shipping-methods.toggle-status', method.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Method status updated
    }
  })
}

const confirmDelete = () => {
  router.delete(route('cp.settings.shipping-methods.destroy', selectedMethod.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      selectedMethod.value = null
    }
  })
}

// Shipping calculation
const calculateShipping = () => {
  router.post(route('cp.settings.shipping-methods.calculate'), calculator.value, {
    preserveState: true,
    onSuccess: (page) => {
      shippingCalculation.value = page.props.cost
    }
  })
}

// Modal handlers
const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedMethod.value = null
}

const handleMethodSaved = () => {
  closeModal()
  // Refresh the page data
  router.reload()
}
</script>
