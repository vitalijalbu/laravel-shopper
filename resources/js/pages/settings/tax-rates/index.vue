<template>
  <div>
    <Head :title="page.title" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Aliquote Fiscali</h1>
          <p class="mt-2 text-gray-600">Gestisci le tasse per paese, stato e città</p>
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
            Aggiungi Aliquota
          </button>
        </div>
      </div>

      <!-- Tax Calculator -->
      <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">Calcolatore Tasse</h2>
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Importo</label>
            <div class="relative">
              <input
                v-model.number="calculator.amount"
                type="number"
                step="0.01"
                min="0"
                class="w-full pl-8 border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="100.00"
              />
              <span class="absolute left-3 top-2.5 text-gray-500">€</span>
            </div>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paese</label>
            <select
              v-model="calculator.country"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Seleziona paese</option>
              <option v-for="(name, code) in countries" :key="code" :value="code">{{ name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stato/Regione</label>
            <input
              v-model="calculator.state"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="Lombardia"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">CAP</label>
            <input
              v-model="calculator.zip_code"
              type="text"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              placeholder="20100"
            />
          </div>
          <div>
            <button
              @click="calculateTax"
              :disabled="!calculator.amount || !calculator.country"
              class="w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              Calcola
            </button>
          </div>
        </div>
        
        <!-- Tax Calculation Result -->
        <div v-if="taxCalculation" class="mt-4 p-4 bg-white rounded-lg border border-gray-200">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
            <div>
              <span class="text-gray-500">Subtotale:</span>
              <span class="ml-2 font-medium">€{{ taxCalculation.subtotal.toFixed(2) }}</span>
            </div>
            <div>
              <span class="text-gray-500">Tasse:</span>
              <span class="ml-2 font-medium text-red-600">€{{ taxCalculation.tax_total.toFixed(2) }}</span>
            </div>
            <div>
              <span class="text-gray-500">Totale:</span>
              <span class="ml-2 font-medium text-lg">€{{ taxCalculation.total.toFixed(2) }}</span>
            </div>
            <div>
              <span class="text-gray-500">Aliquote applicate:</span>
              <span class="ml-2 font-medium">{{ taxCalculation.tax_breakdown.length }}</span>
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
              placeholder="Nome aliquota..."
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            />
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Paese</label>
            <select
              v-model="filters.country"
              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
            >
              <option value="">Tutti i paesi</option>
              <option v-for="(name, code) in countries" :key="code" :value="code">{{ name }}</option>
            </select>
          </div>
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
            <select
              v-model="filters.is_active"
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
              <option value="name">Nome</option>
              <option value="rate">Aliquota</option>
              <option value="country">Paese</option>
              <option value="priority">Priorità</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Tax Rates Table -->
      <div class="bg-white shadow rounded-lg">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-gray-200">
          <div class="flex items-center justify-between">
            <h2 class="text-lg font-medium text-gray-900">
              Aliquote Configurate ({{ taxRates.data.length }})
            </h2>
            <div class="text-sm text-gray-500">
              Totale attive: {{ taxRates.data.filter(rate => rate.is_active).length }}
            </div>
          </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Aliquota
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Ubicazione
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tasso
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Tipo
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Priorità
                </th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Stato
                </th>
                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                  Azioni
                </th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="taxRate in taxRates.data" :key="taxRate.id" class="hover:bg-gray-50">
                <!-- Tax Rate Info -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div>
                    <div class="text-sm font-medium text-gray-900">{{ taxRate.name }}</div>
                    <div class="text-sm text-gray-500">{{ taxRate.description }}</div>
                  </div>
                </td>

                <!-- Location -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">
                    <div class="flex items-center">
                      <span class="font-medium">{{ countries[taxRate.country] || taxRate.country }}</span>
                      <span v-if="taxRate.state" class="ml-1 text-gray-500">, {{ taxRate.state }}</span>
                    </div>
                    <div v-if="taxRate.city || taxRate.zip_code" class="text-xs text-gray-500">
                      <span v-if="taxRate.city">{{ taxRate.city }}</span>
                      <span v-if="taxRate.zip_code"> ({{ taxRate.zip_code }})</span>
                    </div>
                  </div>
                </td>

                <!-- Rate -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm font-medium text-gray-900">
                    {{ taxRate.type === 'percentage' ? `${taxRate.rate}%` : `€${taxRate.rate.toFixed(2)}` }}
                  </div>
                  <div v-if="taxRate.is_compound" class="text-xs text-orange-600">Composta</div>
                  <div v-if="taxRate.is_shipping" class="text-xs text-blue-600">Su spedizione</div>
                </td>

                <!-- Type -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <span :class="[
                    taxRate.type === 'percentage' 
                      ? 'bg-blue-100 text-blue-800' 
                      : 'bg-gray-100 text-gray-800',
                    'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium'
                  ]">
                    {{ taxRate.type === 'percentage' ? 'Percentuale' : 'Fissa' }}
                  </span>
                </td>

                <!-- Priority -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <div class="text-sm text-gray-900">{{ taxRate.priority }}</div>
                </td>

                <!-- Status -->
                <td class="px-6 py-4 whitespace-nowrap">
                  <button
                    @click="toggleStatus(taxRate)"
                    :class="[
                      taxRate.is_active 
                        ? 'bg-green-100 text-green-800' 
                        : 'bg-red-100 text-red-800',
                      'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium cursor-pointer hover:opacity-80'
                    ]"
                  >
                    <span class="w-1.5 h-1.5 rounded-full mr-1" :class="taxRate.is_active ? 'bg-green-600' : 'bg-red-600'"></span>
                    {{ taxRate.is_active ? 'Attivo' : 'Inattivo' }}
                  </button>
                </td>

                <!-- Actions -->
                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                  <div class="flex items-center justify-end space-x-2">
                    <!-- Duplicate -->
                    <button
                      @click="duplicateTaxRate(taxRate)"
                      class="text-blue-600 hover:text-blue-900"
                      title="Duplica"
                    >
                      <DocumentDuplicateIcon class="h-5 w-5" />
                    </button>

                    <!-- Edit -->
                    <button
                      @click="editTaxRate(taxRate)"
                      class="text-indigo-600 hover:text-indigo-900"
                      title="Modifica"
                    >
                      <PencilIcon class="h-5 w-5" />
                    </button>

                    <!-- View -->
                    <Link
                      :href="route('cp.settings.tax-rates.show', taxRate.id)"
                      class="text-gray-600 hover:text-gray-900"
                      title="Visualizza"
                    >
                      <EyeIcon class="h-5 w-5" />
                    </Link>

                    <!-- Delete -->
                    <button
                      @click="deleteTaxRate(taxRate)"
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
        <div v-if="taxRates.data.length === 0" class="text-center py-12">
          <CalculatorIcon class="mx-auto h-12 w-12 text-gray-400" />
          <h3 class="mt-2 text-sm font-medium text-gray-900">Nessuna aliquota configurata</h3>
          <p class="mt-1 text-sm text-gray-500">Inizia aggiungendo la tua prima aliquota fiscale</p>
          <div class="mt-6">
            <button
              @click="showCreateModal = true"
              class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700"
            >
              <PlusIcon class="w-4 h-4 mr-2" />
              Aggiungi Aliquota
            </button>
          </div>
        </div>

        <!-- Pagination -->
        <div v-if="taxRates.links.length > 3" class="px-6 py-4 border-t border-gray-200">
          <div class="flex items-center justify-between">
            <div class="text-sm text-gray-700">
              Showing {{ taxRates.from }} to {{ taxRates.to }} of {{ taxRates.total }} results
            </div>
            <div class="flex space-x-1">
              <Link
                v-for="link in taxRates.links"
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

    <!-- TODO: Create/Edit Modal Component needed -->
    <!-- 
    <TaxRateModal
      :show="showCreateModal || showEditModal"
      :tax-rate="selectedTaxRate"
      :countries="countries"
      @close="closeModal"
      @saved="handleTaxRateSaved"
    />
    -->

    <!-- Delete Confirmation Modal -->
    <ConfirmDialog
      :show="confirmState.show"
      :title="confirmState.title"
      :message="confirmState.message"
      :confirm-text="confirmState.confirmText"
      :cancel-text="confirmState.cancelText"
      :confirm-class="confirmState.confirmClass"
      @confirm="confirmState.onConfirm"
      @cancel="confirmState.onCancel"
    />
  </div>
</template>

<script setup>
import { Head, Link, router } from '@inertiajs/vue3'
import { ref, watch } from 'vue'
import {
  ArrowLeftIcon,
  CalculatorIcon,
  DocumentDuplicateIcon,
  EyeIcon,
  PencilIcon,
  PlusIcon,
  TrashIcon
} from '@heroicons/vue/24/outline'
import ConfirmDialog from '@/components/ConfirmDialog.vue'
import { useConfirm } from '@/composables/useConfirm.js'

const props = defineProps({
  page: Object,
  navigation: Object,
  taxRates: Object,
  countries: Object,
  filters: Object
})

// Use confirm composable
const { confirmState, confirmDelete: confirmDeleteComposable } = useConfirm()

// Modal states
const showCreateModal = ref(false)
const showEditModal = ref(false)
const selectedTaxRate = ref(null)

// Tax calculator
const calculator = ref({
  amount: null,
  country: '',
  state: '',
  zip_code: '',
  city: ''
})

const taxCalculation = ref(null)

// Filters
const filters = ref({
  search: props.filters.search || '',
  country: props.filters.country || '',
  state: props.filters.state || '',
  is_active: props.filters.is_active || '',
  sort: props.filters.sort || 'name',
  direction: props.filters.direction || 'asc'
})

// Watch filters and update URL
watch(filters, (newFilters) => {
  router.get(route('cp.settings.tax-rates.index'), newFilters, {
    preserveState: true,
    replace: true
  })
}, { deep: true })

// Tax Rate actions
const editTaxRate = (taxRate) => {
  selectedTaxRate.value = taxRate
  showEditModal.value = true
}

const deleteTaxRate = (taxRate) => {
  confirmDeleteComposable(`l'aliquota '${taxRate.name}'`, () => {
    router.delete(route('cp.settings.tax-rates.destroy', taxRate.id), {
      preserveScroll: true,
      onSuccess: () => {
        // Tax rate deleted successfully
      }
    })
  })
}

const duplicateTaxRate = (taxRate) => {
  router.post(route('cp.settings.tax-rates.duplicate', taxRate.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Tax rate duplicated
    }
  })
}

const toggleStatus = (taxRate) => {
  router.patch(route('cp.settings.tax-rates.toggle-status', taxRate.id), {}, {
    preserveScroll: true,
    onSuccess: () => {
      // Tax rate status updated
    }
  })
}

// Tax calculation
const calculateTax = () => {
  router.post(route('cp.settings.tax-rates.calculate'), calculator.value, {
    preserveState: true,
    onSuccess: (page) => {
      taxCalculation.value = page.props.calculation
    }
  })
}

// Modal handlers
const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedTaxRate.value = null
}

const handleTaxRateSaved = () => {
  closeModal()
  // Refresh the page data
  router.reload()
}
</script>
