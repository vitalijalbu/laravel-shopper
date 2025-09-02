<template>
  <div>
    <Head :title="page.title" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Gateway di Pagamento</h1>
          <p class="mt-2 text-gray-600">Gestisci i metodi di pagamento disponibili nel tuo negozio</p>
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
            Aggiungi Gateway
          </button>
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
              placeholder="Nome gateway..."
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Provider</label>
            <select
              v-model="filters.provider"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tutti i provider</option>
              <option v-for="(label, value) in providers" :key="value" :value="value">{{ label }}</option>
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
            <label class="block text-sm font-medium text-gray-700 mb-1">Modalità</label>
            <select
              v-model="filters.test_mode"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tutti</option>
              <option value="1">Test</option>
              <option value="0">Produzione</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Gateways List -->
      <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              Gateway Configurati ({{ gateways.data.length }})
            </h2>
            <div class="flex items-center space-x-2">
              <span class="text-sm text-gray-500">Ordinamento:</span>
              <select
                v-model="filters.sort"
                class="border-gray-300 rounded-md text-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="name">Nome</option>
                <option value="provider">Provider</option>
                <option value="sort_order">Ordine</option>
                <option value="created_at">Data creazione</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Gateway
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Provider
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stato
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Modalità
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Valute
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Azioni
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="gateway in gateways.data" :key="gateway.id" class="hover:bg-gray-50">
                <!-- Gateway Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <div class="flex-shrink-0 h-10 w-10">
                      <div class="h-10 w-10 rounded-full bg-gradient-to-r from-blue-500 to-purple-600 flex items-center justify-center">
                        <CreditCardIcon class="h-6 w-6 text-white" />
                      </div>
                    </div>
                    <div class="ml-4">
                      <div class="flex items-center">
                        <div class="text-sm font-medium text-gray-900">{{ gateway.name }}</div>
                        <span v-if="gateway.is_default" class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                          Predefinito
                        </span>
                      </div>
                      <div class="text-sm text-gray-500">{{ gateway.description }}</div>
                    </div>
                  </div>
                </td>

                <!-- Provider -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="flex items-center">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                      {{ providers[gateway.provider] || gateway.provider }}
                    </span>
                  </div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStatus(gateway)"
                    :class="[
                      gateway.is_enabled 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800',
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:opacity-80'
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1" :class="gateway.is_enabled ? 'bg-green-600' : 'bg-red-600'"></span>
                    {{ gateway.is_enabled ? 'Attivo' : 'Inattivo' }}
                  </button>
                </td>

                <!-- Test Mode -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    gateway.test_mode 
                      ? 'bg-yellow-100 text-yellow-800' 
                      : 'bg-gray-100 text-gray-800',
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]">
                    {{ gateway.test_mode ? 'Test' : 'Produzione' }}
                  </span>
                </td>

                <!-- Supported Currencies -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <span v-if="gateway.supported_currencies && gateway.supported_currencies.length">
                      {{ gateway.supported_currencies.slice(0, 3).join(', ') }}
                      <span v-if="gateway.supported_currencies.length > 3" class="text-gray-500">
                        +{{ gateway.supported_currencies.length - 3 }}
                      </span>
                    </span>
                    <span v-else class="text-gray-500">Tutte</span>
                  </div>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- Set as Default -->
                    <button
                      v-if="!gateway.is_default"
                      @click="setAsDefault(gateway)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Imposta come predefinito"
                    >
                      <StarIcon class="h-5 w-5" />
                    </button>

                    <!-- Edit -->
                    <button
                      @click="editGateway(gateway)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Modifica"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>

                    <!-- Configure -->
                    <Link
                      :href="route('cp.settings.payment-gateways.show', gateway.id)"
                      class="text-gray-600 hover:text-gray-900"
                      title="Configura"
                    >
                      <CogIcon class="h-5 w-5" />
                    </Link>

                    <!-- Delete -->
                    <button
                      @click="deleteGateway(gateway)"
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
        <div v-if="gateways.data.length === 0" class="text-center py-12">
          <CreditCardIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nessun gateway configurato</h3>
          <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo il tuo primo gateway di pagamento</p>
          <div class="mt-6">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Aggiungi Gateway
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="gateways.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ gateways.from }} to {{ gateways.to }} of {{ gateways.total }} results
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in gateways.links"
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
    <GatewayModal
      :show="showCreateModal || showEditModal"
      :gateway="selectedGateway"
      :providers="providers"
      @close="closeModal"
      @saved="handleGatewaySaved"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      title="Elimina Gateway"
      :message="`Sei sicuro di voler eliminare il gateway '${selectedGateway?.name}'? Questa azione non può essere annullata.`"
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
  CogIcon,
  CreditCardIcon,
  PencilIcon,
  PlusIcon,
  StarIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import GatewayModal from './GatewayModal.vue'
import ConfirmModal from '@/Components/ConfirmModal.vue'

const props = defineProps({
  page: Object,
  navigation: Object,
  gateways: Object,
  providers: Object,
  filters: Object
})

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedGateway = ref(null)

// Filters
const filters = ref({
  search: props.filters.search || '',
  provider: props.filters.provider || '',
  is_enabled: props.filters.is_enabled || '',
  test_mode: props.filters.test_mode || '',
  sort: props.filters.sort || 'name',
  direction: props.filters.direction || 'asc'
})

// Watch filters and update URL
watch(filters, (newFilters) => {
  router.get(route('cp.settings.payment-gateways.index'), newFilters, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

// Gateway actions
const editGateway = (gateway) => {
  selectedGateway.value = gateway
  showEditModal.value = true
}

const deleteGateway = (gateway) => {
  selectedGateway.value = gateway
  showDeleteModal.value = true
}

const toggleStatus = (gateway) => {
  router.patch(route('cp.settings.payment-gateways.toggle-status', gateway.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Gateway status updated
    }
  })
}

const setAsDefault = (gateway) => {
  router.patch(route('cp.settings.payment-gateways.set-default', gateway.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Gateway set as default
    }
  })
}

const confirmDelete = () => {
  router.delete(route('cp.settings.payment-gateways.destroy', selectedGateway.value.id), {
    preserveScroll: true,
    onSuccess: () => {
      showDeleteModal.value = false
      selectedGateway.value = null
    }
  })
}

// Modal handlers
const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedGateway.value = null
}

const handleGatewaySaved = () => {
  closeModal()
  // Refresh the page data
  router.reload()
}
</script>
