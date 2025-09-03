<template>
  <div class="discount-list">
    <!-- Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ $t('discount.labels.discounts') }}
        </h1>
        <p class="mt-1 text-sm text-gray-500">
          Gestisci sconti e codici promozionali per il tuo negozio
        </p>
      </div>
      <ShopperButton
        @click="showCreateModal = true"
        class="btn-primary"
      >
        <PlusIcon class="w-4 h-4 mr-2" />
        {{ $t('discount.actions.create') }}
      </ShopperButton>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border mb-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <!-- Search -->
        <div>
          <ShopperInput
            v-model="filters.search"
            :placeholder="$t('Cerca per nome o codice...')"
            class="w-full"
            @input="debouncedSearch"
          >
            <template #leading>
              <MagnifyingGlassIcon class="w-4 h-4 text-gray-400" />
            </template>
          </ShopperInput>
        </div>

        <!-- Status Filter -->
        <div>
          <ShopperSelect
            v-model="filters.status"
            :placeholder="$t('Tutti gli stati')"
            @change="loadDiscounts"
          >
            <option value="">Tutti gli stati</option>
            <option value="active">{{ $t('discount.statuses.active') }}</option>
            <option value="disabled">{{ $t('discount.statuses.disabled') }}</option>
            <option value="expired">{{ $t('discount.statuses.expired') }}</option>
            <option value="scheduled">{{ $t('discount.statuses.scheduled') }}</option>
          </ShopperSelect>
        </div>

        <!-- Type Filter -->
        <div>
          <ShopperSelect
            v-model="filters.type"
            :placeholder="$t('Tutti i tipi')"
            @change="loadDiscounts"
          >
            <option value="">Tutti i tipi</option>
            <option value="percentage">{{ $t('discount.types.percentage') }}</option>
            <option value="fixed_amount">{{ $t('discount.types.fixed_amount') }}</option>
            <option value="free_shipping">{{ $t('discount.types.free_shipping') }}</option>
          </ShopperSelect>
        </div>

        <!-- Reset Filters -->
        <div class="flex items-end">
          <ShopperButton
            @click="resetFilters"
            variant="outline"
            class="w-full"
          >
            {{ $t('Azzera filtri') }}
          </ShopperButton>
        </div>
      </div>
    </div>

    <!-- Discounts Table -->
    <div class="bg-white rounded-lg shadow-sm border">
      <ShopperTable
        :items="discounts.data"
        :headers="tableHeaders"
        :loading="loading"
        :empty-state="{
          title: $t('Nessun sconto trovato'),
          description: $t('Inizia creando il tuo primo sconto'),
          action: { text: $t('discount.actions.create'), handler: () => showCreateModal = true }
        }"
      >
        <!-- Name & Code -->
        <template #name="{ item }">
          <div>
            <div class="font-medium text-gray-900">{{ item.name }}</div>
            <div class="text-sm text-gray-500 font-mono">{{ item.code }}</div>
          </div>
        </template>

        <!-- Type & Value -->
        <template #type="{ item }">
          <div>
            <div class="text-sm font-medium text-gray-900">
              {{ $t(`discount.types.${item.type}`) }}
            </div>
            <div class="text-sm text-gray-500">{{ item.formatted_value }}</div>
          </div>
        </template>

        <!-- Status -->
        <template #status="{ item }">
          <ShopperBadge :variant="getStatusVariant(item.status)">
            {{ $t(`discount.statuses.${item.status}`) }}
          </ShopperBadge>
        </template>

        <!-- Usage -->
        <template #usage="{ item }">
          <div class="text-sm">
            <div class="text-gray-900">
              {{ item.usage_count }}{{ item.usage_limit ? `/${item.usage_limit}` : '' }}
            </div>
            <div class="text-gray-500">utilizzi</div>
          </div>
        </template>

        <!-- Dates -->
        <template #dates="{ item }">
          <div class="text-sm text-gray-500">
            <div v-if="item.starts_at">
              Inizio: {{ formatDate(item.starts_at) }}
            </div>
            <div v-if="item.expires_at">
              Scadenza: {{ formatDate(item.expires_at) }}
            </div>
            <div v-if="!item.starts_at && !item.expires_at" class="text-gray-400">
              Sempre attivo
            </div>
          </div>
        </template>

        <!-- Actions -->
        <template #actions="{ item }">
          <ShopperDropdown>
            <template #trigger>
              <ShopperButton variant="ghost" size="sm">
                <EllipsisVerticalIcon class="w-4 h-4" />
              </ShopperButton>
            </template>

            <ShopperDropdownItem @click="editDiscount(item)">
              <PencilIcon class="w-4 h-4 mr-2" />
              {{ $t('discount.actions.edit') }}
            </ShopperDropdownItem>

            <ShopperDropdownItem @click="toggleDiscountStatus(item)">
              <component :is="item.is_enabled ? EyeSlashIcon : EyeIcon" class="w-4 h-4 mr-2" />
              {{ item.is_enabled ? $t('discount.actions.disable') : $t('discount.actions.enable') }}
            </ShopperDropdownItem>

            <ShopperDropdownItem @click="duplicateDiscount(item)">
              <DocumentDuplicateIcon class="w-4 h-4 mr-2" />
              {{ $t('discount.actions.duplicate') }}
            </ShopperDropdownItem>

            <ShopperDropdownItem @click="viewStatistics(item)">
              <ChartBarIcon class="w-4 h-4 mr-2" />
              Statistiche
            </ShopperDropdownItem>

            <ShopperDropdownSeparator />

            <ShopperDropdownItem @click="deleteDiscount(item)" class="text-red-600">
              <TrashIcon class="w-4 h-4 mr-2" />
              {{ $t('discount.actions.delete') }}
            </ShopperDropdownItem>
          </ShopperDropdown>
        </template>
      </ShopperTable>

      <!-- Pagination -->
      <div v-if="discounts.data && discounts.data.length > 0" class="p-4 border-t">
        <ShopperPagination
          :current-page="discounts.current_page"
          :total-pages="discounts.last_page"
          :total-items="discounts.total"
          :per-page="discounts.per_page"
          @change="changePage"
        />
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <discount-form-modal
      v-model:show="showCreateModal"
      :discount="selectedDiscount"
      @saved="onDiscountSaved"
    />

    <!-- Statistics Modal -->
    <discount-statistics-modal
      v-model:show="showStatisticsModal"
      :discount="selectedDiscount"
    />
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'
import { useNotifications } from '@/composables/useNotifications'
import { debounce } from '@/lib/utils'
import {
  PlusIcon,
  MagnifyingGlassIcon,
  EllipsisVerticalIcon,
  PencilIcon,
  EyeIcon,
  EyeSlashIcon,
  DocumentDuplicateIcon,
  ChartBarIcon,
  TrashIcon,
} from '@heroicons/vue/24/outline'

// Components
import ShopperButton from '@/components/ui/ShopperButton.vue'
import ShopperInput from '@/components/ui/ShopperInput.vue'
import ShopperSelect from '@/components/ui/ShopperSelect.vue'
import ShopperTable from '@/components/ui/ShopperTable.vue'
import ShopperBadge from '@/components/ui/ShopperBadge.vue'
import ShopperDropdown from '@/components/ui/ShopperDropdown.vue'
import ShopperDropdownItem from '@/components/ui/ShopperDropdownItem.vue'
import ShopperDropdownSeparator from '@/components/ui/ShopperDropdownSeparator.vue'
import ShopperPagination from '@/components/ui/ShopperPagination.vue'

// Lazy load modals
const DiscountFormModal = defineAsyncComponent(() => 
  import('./discount-form-modal.vue')
)
const DiscountStatisticsModal = defineAsyncComponent(() => 
  import('./discount-statistics-modal.vue')
)

// Composables
const { $t } = useTranslations()
const { api } = useApi()
const { notify } = useNotifications()

// State
const loading = ref(false)
const discounts = ref({})
const showCreateModal = ref(false)
const showStatisticsModal = ref(false)
const selectedDiscount = ref(null)

const filters = reactive({
  search: '',
  status: '',
  type: '',
  page: 1,
})

// Table configuration
const tableHeaders = computed(() => [
  { key: 'name', label: $t('discount.labels.name'), sortable: true },
  { key: 'type', label: $t('discount.labels.type') },
  { key: 'status', label: $t('discount.labels.status') },
  { key: 'usage', label: 'Utilizzi' },
  { key: 'dates', label: 'Date' },
  { key: 'actions', label: '', align: 'right' },
])

// Methods
const loadDiscounts = async (page = 1) => {
  loading.value = true
  try {
    const params = { ...filters, page }
    const response = await api.get('/admin/discounts', { params })
    discounts.value = response.data
  } catch (error) {
    notify.error('Errore nel caricamento degli sconti')
  } finally {
    loading.value = false
  }
}

const debouncedSearch = debounce(() => {
  filters.page = 1
  loadDiscounts()
}, 300)

const resetFilters = () => {
  Object.assign(filters, {
    search: '',
    status: '',
    type: '',
    page: 1,
  })
  loadDiscounts()
}

const changePage = (page) => {
  filters.page = page
  loadDiscounts(page)
}

const editDiscount = (discount) => {
  selectedDiscount.value = discount
  showCreateModal.value = true
}

const toggleDiscountStatus = async (discount) => {
  try {
    await api.post(`/admin/discounts/${discount.id}/toggle`)
    notify.success(
      discount.is_enabled 
        ? $t('discount.messages.disabled_successfully')
        : $t('discount.messages.enabled_successfully')
    )
    loadDiscounts(filters.page)
  } catch (error) {
    notify.error('Errore nel cambio stato')
  }
}

const duplicateDiscount = async (discount) => {
  try {
    await api.post(`/admin/discounts/${discount.id}/duplicate`)
    notify.success($t('discount.messages.duplicated_successfully'))
    loadDiscounts(filters.page)
  } catch (error) {
    notify.error('Errore nella duplicazione')
  }
}

const deleteDiscount = async (discount) => {
  if (confirm('Sei sicuro di voler eliminare questo sconto?')) {
    try {
      await api.delete(`/admin/discounts/${discount.id}`)
      notify.success($t('discount.messages.deleted_successfully'))
      loadDiscounts(filters.page)
    } catch (error) {
      notify.error('Errore nell\'eliminazione')
    }
  }
}

const viewStatistics = (discount) => {
  selectedDiscount.value = discount
  showStatisticsModal.value = true
}

const onDiscountSaved = () => {
  showCreateModal.value = false
  selectedDiscount.value = null
  loadDiscounts(filters.page)
}

// Utilities
const getStatusVariant = (status) => {
  const variants = {
    active: 'success',
    disabled: 'secondary',
    expired: 'destructive',
    scheduled: 'warning',
    exhausted: 'destructive',
  }
  return variants[status] || 'secondary'
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('it-IT', {
    day: '2-digit',
    month: '2-digit', 
    year: 'numeric'
  })
}

// Lifecycle
onMounted(() => {
  loadDiscounts()
})
</script>
