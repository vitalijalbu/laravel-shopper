<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Clienti</h1>
        <p class="text-sm text-gray-500">Gestisci tutti i clienti registrati</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <PlusIcon class="h-4 w-4 mr-2" />
        Nuovo Cliente
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Nome, email, telefono..."
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          />
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stato</label>
          <select
            v-model="filters.status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Tutti</option>
            <option value="active">Attivo</option>
            <option value="inactive">Inattivo</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Gruppo</label>
          <select
            v-model="filters.customer_group_id"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Tutti i gruppi</option>
            <option v-for="group in customer_groups" :key="group.id" :value="group.id">
              {{ group.name }}
            </option>
          </select>
        </div>
        <div class="flex items-end">
          <button
            @click="resetFilters"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
          >
            Reset
          </button>
        </div>
      </div>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-lg shadow-sm border overflow-hidden">
      <DataTable
        :data="customers.data || []"
        :columns="columns"
        :loading="loading"
        :row-actions="rowActions"
        searchable
        :search-placeholder="'Cerca clienti...'"
        @sort-change="handleSort"
        @row-action="handleRowAction"
      >
        <!-- Custom column: Full name -->
        <template #column-full_name="{ item }">
          {{ `${item.first_name} ${item.last_name}` }}
        </template>

        <!-- Custom column: Status -->
        <template #column-is_active="{ item }">
          <span
            :class="[
              'inline-flex px-2 py-1 text-xs font-medium rounded-full',
              item.is_active
                ? 'bg-green-100 text-green-800'
                : 'bg-red-100 text-red-800',
            ]"
          >
            {{ item.is_active ? 'Attivo' : 'Inattivo' }}
          </span>
        </template>

        <!-- Custom column: Total spent -->
        <template #column-total_spent="{ item }">
          {{ new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR'
          }).format(item.total_spent || 0) }}
        </template>

        <!-- Custom column: Customer group -->
        <template #column-customer_group="{ item }">
          {{ item.customer_group?.name || '-' }}
        </template>

        <!-- Custom column: Created at -->
        <template #column-created_at="{ item }">
          {{ new Date(item.created_at).toLocaleDateString('it-IT') }}
        </template>
      </DataTable>
    </div>

    <!-- Pagination -->
    <div v-if="customers.meta" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
      <div class="flex justify-between flex-1 sm:hidden">
        <button
          @click="changePage(customers.meta.current_page - 1)"
          :disabled="!customers.meta.prev_page_url"
          class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Precedente
        </button>
        <button
          @click="changePage(customers.meta.current_page + 1)"
          :disabled="!customers.meta.next_page_url"
          class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Successivo
        </button>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Mostrando
            <span class="font-medium">{{ customers.meta.from }}</span>
            a
            <span class="font-medium">{{ customers.meta.to }}</span>
            di
            <span class="font-medium">{{ customers.meta.total }}</span>
            risultati
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              @click="changePage(customers.meta.current_page - 1)"
              :disabled="!customers.meta.prev_page_url"
              class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronLeftIcon class="h-5 w-5" />
            </button>
            <template v-for="page in paginationPages" :key="page">
              <button
                v-if="page !== '...'"
                @click="changePage(page)"
                :class="[
                  'relative inline-flex items-center px-4 py-2 border text-sm font-medium',
                  page === customers.meta.current_page
                    ? 'z-10 bg-indigo-50 border-indigo-500 text-indigo-600'
                    : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'
                ]"
              >
                {{ page }}
              </button>
              <span
                v-else
                class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700"
              >
                ...
              </span>
            </template>
            <button
              @click="changePage(customers.meta.current_page + 1)"
              :disabled="!customers.meta.next_page_url"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronRightIcon class="h-5 w-5" />
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal">
      <div class="px-6 py-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showCreateModal ? 'Nuovo Cliente' : 'Modifica Cliente' }}
        </h3>
        
        <form @submit.prevent="saveCustomer" class="space-y-4">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Nome *</label>
              <input
                v-model="form.first_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">Cognome *</label>
              <input
                v-model="form.last_name"
                type="text"
                required
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
              />
            </div>
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
            <input
              v-model="form.email"
              type="email"
              required
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Telefono</label>
            <input
              v-model="form.phone_number"
              type="tel"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Data di nascita</label>
            <input
              v-model="form.date_of_birth"
              type="date"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            />
          </div>
          
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Gruppo Cliente</label>
            <select
              v-model="form.customer_group_id"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Seleziona gruppo</option>
              <option v-for="group in customer_groups" :key="group.id" :value="group.id">
                {{ group.name }}
              </option>
            </select>
          </div>
          
          <div class="flex items-center">
            <input
              v-model="form.is_active"
              type="checkbox"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
            />
            <label class="ml-2 block text-sm text-gray-900">Cliente attivo</label>
          </div>
          
          <div class="flex justify-end space-x-3 pt-4 border-t">
            <button
              type="button"
              @click="closeModal"
              class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              Annulla
            </button>
            <button
              type="submit"
              :disabled="saving"
              class="px-4 py-2 text-sm font-medium text-white bg-indigo-600 border border-transparent rounded-md hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
            >
              {{ saving ? 'Salvataggio...' : 'Salva' }}
            </button>
          </div>
        </form>
      </div>
    </Modal>

    <!-- Confirm Delete Modal -->
    <ConfirmModal
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteCustomer"
      title="Elimina Cliente"
      message="Sei sicuro di voler eliminare questo cliente? Questa azione non può essere annullata."
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DataTable from '@/components/Admin/Table/DataTable.vue'
import Modal from '@/components/modal.vue'
import ConfirmModal from '@/components/confirm-modal.vue'
import { PlusIcon, ChevronLeftIcon, ChevronRightIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  customers: Object,
  customer_groups: Array,
  filters: Object
})

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedCustomer = ref(null)

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  customer_group_id: props.filters?.customer_group_id || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
  page: props.filters?.page || 1
})

const form = reactive({
  first_name: '',
  last_name: '',
  email: '',
  phone_number: '',
  date_of_birth: '',
  customer_group_id: '',
  is_active: true
})

// Computed
const columns = computed(() => [
  {
    key: 'full_name',
    label: 'Nome',
    sortable: false
  },
  {
    key: 'email',
    label: 'Email',
    sortable: true
  },
  {
    key: 'phone_number',
    label: 'Telefono',
    sortable: false
  },
  {
    key: 'customer_group',
    label: 'Gruppo',
    sortable: false
  },
  {
    key: 'orders_count',
    label: 'Ordini',
    sortable: true
  },
  {
    key: 'total_spent',
    label: 'Totale speso',
    sortable: true
  },
  {
    key: 'is_active',
    label: 'Stato',
    sortable: true
  },
  {
    key: 'created_at',
    label: 'Creato',
    sortable: true
  }
])

const rowActions = computed(() => [
  {
    label: 'Modifica',
    action: 'edit',
    icon: 'pencil',
    class: 'text-indigo-600 hover:text-indigo-900'
  },
  {
    label: 'Elimina',
    action: 'delete',
    icon: 'trash',
    class: 'text-red-600 hover:text-red-900'
  }
])

const paginationPages = computed(() => {
  if (!props.customers?.meta) return []
  
  const current = props.customers.meta.current_page
  const last = props.customers.meta.last_page
  const pages = []
  
  if (last <= 7) {
    for (let i = 1; i <= last; i++) {
      pages.push(i)
    }
  } else {
    if (current <= 4) {
      for (let i = 1; i <= 5; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    } else if (current >= last - 3) {
      pages.push(1)
      pages.push('...')
      for (let i = last - 4; i <= last; i++) {
        pages.push(i)
      }
    } else {
      pages.push(1)
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(last)
    }
  }
  
  return pages
})

// Methods
const loadCustomers = () => {
  loading.value = true
  router.get('/admin/customers', filters, {
    preserveState: true,
    preserveScroll: true,
    onFinish: () => {
      loading.value = false
    }
  })
}

const handleSort = (sort) => {
  filters.sort = sort.column
  filters.direction = sort.direction
  loadCustomers()
}

const handleRowAction = (action, customer) => {
  if (action === 'edit') {
    editCustomer(customer)
  } else if (action === 'delete') {
    selectedCustomer.value = customer
    showDeleteModal.value = true
  }
}

const changePage = (page) => {
  if (page >= 1 && page <= props.customers.meta.last_page) {
    filters.page = page
    loadCustomers()
  }
}

const resetFilters = () => {
  Object.assign(filters, {
    search: '',
    status: '',
    customer_group_id: '',
    sort: 'created_at',
    direction: 'desc',
    page: 1
  })
  loadCustomers()
}

const editCustomer = (customer) => {
  selectedCustomer.value = customer
  Object.assign(form, {
    first_name: customer.first_name,
    last_name: customer.last_name,
    email: customer.email,
    phone_number: customer.phone_number || '',
    date_of_birth: customer.date_of_birth || '',
    customer_group_id: customer.customer_group_id || '',
    is_active: customer.is_active
  })
  showEditModal.value = true
}

const saveCustomer = () => {
  saving.value = true
  
  const url = showCreateModal.value ? '/admin/customers' : `/admin/customers/${selectedCustomer.value.id}`
  const method = showCreateModal.value ? 'post' : 'put'
  
  router[method](url, form, {
    preserveState: true,
    onSuccess: () => {
      closeModal()
      loadCustomers()
    },
    onFinish: () => {
      saving.value = false
    }
  })
}

const deleteCustomer = () => {
  router.delete(`/admin/customers/${selectedCustomer.value.id}`, {
    preserveState: true,
    onSuccess: () => {
      showDeleteModal.value = false
      selectedCustomer.value = null
      loadCustomers()
    }
  })
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedCustomer.value = null
  
  // Reset form
  Object.assign(form, {
    first_name: '',
    last_name: '',
    email: '',
    phone_number: '',
    date_of_birth: '',
    customer_group_id: '',
    is_active: true
  })
}

// Watch filters for auto-search
watch(() => filters.search, () => {
  if (filters.search.length >= 3 || filters.search.length === 0) {
    filters.page = 1
    loadCustomers()
  }
}, { debounce: 500 })

watch(() => [filters.status, filters.customer_group_id], () => {
  filters.page = 1
  loadCustomers()
})

onMounted(() => {
  // Initial load is handled by Inertia
})
</script>
