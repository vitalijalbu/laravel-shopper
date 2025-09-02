<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">Ordini</h1>
        <p class="text-sm text-gray-500">Gestisci tutti gli ordini del negozio</p>
      </div>
      <button
        @click="showCreateModal = true"
        class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        <PlusIcon class="h-4 w-4 mr-2" />
        Nuovo Ordine
      </button>
    </div>

    <!-- Filters -->
    <div class="bg-white p-4 rounded-lg shadow-sm border">
      <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Cerca</label>
          <input
            v-model="filters.search"
            type="text"
            placeholder="Numero ordine, cliente..."
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
            <option value="pending">In attesa</option>
            <option value="processing">In elaborazione</option>
            <option value="shipped">Spedito</option>
            <option value="delivered">Consegnato</option>
            <option value="cancelled">Annullato</option>
            <option value="refunded">Rimborsato</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Stato pagamento</label>
          <select
            v-model="filters.payment_status"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          >
            <option value="">Tutti</option>
            <option value="pending">In attesa</option>
            <option value="paid">Pagato</option>
            <option value="failed">Fallito</option>
            <option value="refunded">Rimborsato</option>
            <option value="partially_refunded">Parzialmente rimborsato</option>
          </select>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">Data da</label>
          <input
            v-model="filters.date_from"
            type="date"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
          />
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
        :data="orders.data || []"
        :columns="columns"
        :loading="loading"
        :row-actions="rowActions"
        searchable
        :search-placeholder="'Cerca ordini...'"
        @sort-change="handleSort"
        @row-action="handleRowAction"
      >
        <!-- Custom column: Order number -->
        <template #column-order_number="{ item }">
          #{{ item.order_number }}
        </template>

        <!-- Custom column: Customer -->
        <template #column-customer="{ item }">
          {{ item.customer ? `${item.customer.first_name} ${item.customer.last_name}` : '-' }}
        </template>

        <!-- Custom column: Status -->
        <template #column-status="{ item }">
          <span
            :class="[
              'inline-flex px-2 py-1 text-xs font-medium rounded-full',
              getStatusClass(item.status)
            ]"
          >
            {{ getStatusLabel(item.status) }}
          </span>
        </template>

        <!-- Custom column: Payment Status -->
        <template #column-payment_status="{ item }">
          <span
            :class="[
              'inline-flex px-2 py-1 text-xs font-medium rounded-full',
              getPaymentStatusClass(item.payment_status)
            ]"
          >
            {{ getPaymentStatusLabel(item.payment_status) }}
          </span>
        </template>

        <!-- Custom column: Total amount -->
        <template #column-total_amount="{ item }">
          {{ new Intl.NumberFormat('it-IT', {
            style: 'currency',
            currency: 'EUR'
          }).format(item.total_amount) }}
        </template>

        <!-- Custom column: Created at -->
        <template #column-created_at="{ item }">
          {{ new Date(item.created_at).toLocaleDateString('it-IT') }}
        </template>
      </DataTable>
    </div>

    <!-- Pagination -->
    <div v-if="orders.meta" class="flex items-center justify-between px-4 py-3 bg-white border-t border-gray-200 sm:px-6">
      <div class="flex justify-between flex-1 sm:hidden">
        <button
          @click="changePage(orders.meta.current_page - 1)"
          :disabled="!orders.meta.prev_page_url"
          class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Precedente
        </button>
        <button
          @click="changePage(orders.meta.current_page + 1)"
          :disabled="!orders.meta.next_page_url"
          class="relative ml-3 inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
        >
          Successivo
        </button>
      </div>
      <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
        <div>
          <p class="text-sm text-gray-700">
            Mostrando
            <span class="font-medium">{{ orders.meta.from }}</span>
            a
            <span class="font-medium">{{ orders.meta.to }}</span>
            di
            <span class="font-medium">{{ orders.meta.total }}</span>
            risultati
          </p>
        </div>
        <div>
          <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
            <button
              @click="changePage(orders.meta.current_page - 1)"
              :disabled="!orders.meta.prev_page_url"
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
                  page === orders.meta.current_page
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
              @click="changePage(orders.meta.current_page + 1)"
              :disabled="!orders.meta.next_page_url"
              class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <ChevronRightIcon class="h-5 w-5" />
            </button>
          </nav>
        </div>
      </div>
    </div>

    <!-- Create/Edit Modal -->
    <Modal :show="showCreateModal || showEditModal" @close="closeModal" size="large">
      <div class="px-6 py-4">
        <h3 class="text-lg font-medium text-gray-900 mb-4">
          {{ showCreateModal ? 'Nuovo Ordine' : 'Modifica Ordine' }}
        </h3>
        
        <form @submit.prevent="saveOrder" class="space-y-6">
          <!-- Customer Information -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-900 mb-3">Informazioni Cliente</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                <select
                  v-model="form.customer_id"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="">Seleziona cliente</option>
                  <option v-for="customer in customers" :key="customer.id" :value="customer.id">
                    {{ customer.first_name }} {{ customer.last_name }} - {{ customer.email }}
                  </option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Numero ordine</label>
                <input
                  v-model="form.order_number"
                  type="text"
                  readonly
                  class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 focus:outline-none"
                />
              </div>
            </div>
          </div>

          <!-- Order Status -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-900 mb-3">Stato Ordine</h4>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stato ordine *</label>
                <select
                  v-model="form.status"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="pending">In attesa</option>
                  <option value="processing">In elaborazione</option>
                  <option value="shipped">Spedito</option>
                  <option value="delivered">Consegnato</option>
                  <option value="cancelled">Annullato</option>
                  <option value="refunded">Rimborsato</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Stato pagamento *</label>
                <select
                  v-model="form.payment_status"
                  required
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                >
                  <option value="pending">In attesa</option>
                  <option value="paid">Pagato</option>
                  <option value="failed">Fallito</option>
                  <option value="refunded">Rimborsato</option>
                  <option value="partially_refunded">Parzialmente rimborsato</option>
                </select>
              </div>
            </div>
          </div>

          <!-- Order Items -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <div class="flex items-center justify-between mb-3">
              <h4 class="text-md font-medium text-gray-900">Prodotti</h4>
              <button
                type="button"
                @click="addOrderItem"
                class="inline-flex items-center px-3 py-1 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200"
              >
                <PlusIcon class="h-4 w-4 mr-1" />
                Aggiungi prodotto
              </button>
            </div>
            
            <div v-if="form.items.length === 0" class="text-center py-4 text-gray-500">
              Nessun prodotto aggiunto
            </div>
            
            <div v-else class="space-y-3">
              <div
                v-for="(item, index) in form.items"
                :key="index"
                class="grid grid-cols-12 gap-3 items-end bg-white p-3 rounded border"
              >
                <div class="col-span-5">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Prodotto</label>
                  <select
                    v-model="item.product_id"
                    @change="updateProductPrice(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  >
                    <option value="">Seleziona prodotto</option>
                    <option v-for="product in products" :key="product.id" :value="product.id">
                      {{ product.name }}
                    </option>
                  </select>
                </div>
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Quantità</label>
                  <input
                    v-model.number="item.quantity"
                    type="number"
                    min="1"
                    @input="calculateItemTotal(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Prezzo</label>
                  <input
                    v-model.number="item.unit_price"
                    type="number"
                    step="0.01"
                    @input="calculateItemTotal(index)"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                  />
                </div>
                <div class="col-span-2">
                  <label class="block text-sm font-medium text-gray-700 mb-1">Totale</label>
                  <input
                    :value="(item.quantity * item.unit_price).toFixed(2)"
                    type="text"
                    readonly
                    class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                  />
                </div>
                <div class="col-span-1">
                  <button
                    type="button"
                    @click="removeOrderItem(index)"
                    class="w-full px-3 py-2 text-red-600 hover:text-red-900"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
              </div>
            </div>
          </div>

          <!-- Order Totals -->
          <div class="bg-gray-50 p-4 rounded-lg">
            <h4 class="text-md font-medium text-gray-900 mb-3">Totali</h4>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Subtotale</label>
                <input
                  :value="subtotal.toFixed(2)"
                  type="text"
                  readonly
                  class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Spedizione</label>
                <input
                  v-model.number="form.shipping_amount"
                  type="number"
                  step="0.01"
                  @input="calculateTotal"
                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                />
              </div>
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Totale</label>
                <input
                  :value="form.total_amount"
                  type="text"
                  readonly
                  class="w-full px-3 py-2 border border-gray-300 rounded-md bg-gray-100 font-medium"
                />
              </div>
            </div>
          </div>

          <!-- Notes -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Note</label>
            <textarea
              v-model="form.notes"
              rows="3"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            ></textarea>
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
      @confirm="deleteOrder"
      title="Elimina Ordine"
      message="Sei sicuro di voler eliminare questo ordine? Questa azione non può essere annullata."
    />
  </div>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import DataTable from '@/components/Admin/Table/DataTable.vue'
import Modal from '@/components/modal.vue'
import ConfirmModal from '@/components/confirm-modal.vue'
import { PlusIcon, ChevronLeftIcon, ChevronRightIcon, TrashIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  orders: Object,
  customers: Array,
  products: Array,
  filters: Object
})

// Reactive data
const loading = ref(false)
const saving = ref(false)
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedOrder = ref(null)

const filters = reactive({
  search: props.filters?.search || '',
  status: props.filters?.status || '',
  payment_status: props.filters?.payment_status || '',
  date_from: props.filters?.date_from || '',
  date_to: props.filters?.date_to || '',
  sort: props.filters?.sort || 'created_at',
  direction: props.filters?.direction || 'desc',
  page: props.filters?.page || 1
})

const form = reactive({
  customer_id: '',
  order_number: '',
  status: 'pending',
  payment_status: 'pending',
  items: [],
  shipping_amount: 0,
  total_amount: 0,
  notes: ''
})

// Computed
const columns = computed(() => [
  {
    key: 'order_number',
    label: 'Numero ordine',
    sortable: true
  },
  {
    key: 'customer',
    label: 'Cliente',
    sortable: false
  },
  {
    key: 'status',
    label: 'Stato',
    sortable: true
  },
  {
    key: 'payment_status',
    label: 'Pagamento',
    sortable: true
  },
  {
    key: 'total_amount',
    label: 'Totale',
    sortable: true
  },
  {
    key: 'created_at',
    label: 'Data',
    sortable: true
  }
])

const rowActions = computed(() => [
  {
    label: 'Visualizza',
    action: 'view',
    icon: 'eye',
    class: 'text-blue-600 hover:text-blue-900'
  },
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

const subtotal = computed(() => {
  return form.items.reduce((sum, item) => {
    return sum + (item.quantity * item.unit_price)
  }, 0)
})

const paginationPages = computed(() => {
  if (!props.orders?.meta) return []
  
  const current = props.orders.meta.current_page
  const last = props.orders.meta.last_page
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
const loadOrders = () => {
  loading.value = true
  router.get('/admin/orders', filters, {
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
  loadOrders()
}

const handleRowAction = (action, order) => {
  if (action === 'view') {
    router.get(`/admin/orders/${order.id}`)
  } else if (action === 'edit') {
    editOrder(order)
  } else if (action === 'delete') {
    selectedOrder.value = order
    showDeleteModal.value = true
  }
}

const getStatusLabel = (status) => {
  const statusMap = {
    pending: 'In attesa',
    processing: 'In elaborazione',
    shipped: 'Spedito',
    delivered: 'Consegnato',
    cancelled: 'Annullato',
    refunded: 'Rimborsato'
  }
  return statusMap[status] || status
}

const getStatusClass = (status) => {
  const statusMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    processing: 'bg-blue-100 text-blue-800',
    shipped: 'bg-purple-100 text-purple-800',
    delivered: 'bg-green-100 text-green-800',
    cancelled: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800'
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const getPaymentStatusLabel = (status) => {
  const statusMap = {
    pending: 'In attesa',
    paid: 'Pagato',
    failed: 'Fallito',
    refunded: 'Rimborsato',
    partially_refunded: 'Parz. rimborsato'
  }
  return statusMap[status] || status
}

const getPaymentStatusClass = (status) => {
  const statusMap = {
    pending: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
    failed: 'bg-red-100 text-red-800',
    refunded: 'bg-gray-100 text-gray-800',
    partially_refunded: 'bg-orange-100 text-orange-800'
  }
  return statusMap[status] || 'bg-gray-100 text-gray-800'
}

const changePage = (page) => {
  if (page >= 1 && page <= props.orders.meta.last_page) {
    filters.page = page
    loadOrders()
  }
}

const resetFilters = () => {
  Object.assign(filters, {
    search: '',
    status: '',
    payment_status: '',
    date_from: '',
    date_to: '',
    sort: 'created_at',
    direction: 'desc',
    page: 1
  })
  loadOrders()
}

const editOrder = (order) => {
  selectedOrder.value = order
  Object.assign(form, {
    customer_id: order.customer_id,
    order_number: order.order_number,
    status: order.status,
    payment_status: order.payment_status,
    items: order.items ? order.items.map(item => ({
      product_id: item.product_id,
      quantity: item.quantity,
      unit_price: item.unit_price
    })) : [],
    shipping_amount: order.shipping_amount || 0,
    total_amount: order.total_amount,
    notes: order.notes || ''
  })
  showEditModal.value = true
}

const addOrderItem = () => {
  form.items.push({
    product_id: '',
    quantity: 1,
    unit_price: 0
  })
}

const removeOrderItem = (index) => {
  form.items.splice(index, 1)
  calculateTotal()
}

const updateProductPrice = (index) => {
  const product = props.products.find(p => p.id == form.items[index].product_id)
  if (product) {
    form.items[index].unit_price = product.price
    calculateItemTotal(index)
  }
}

const calculateItemTotal = (index) => {
  // This will be automatically calculated in the template
  calculateTotal()
}

const calculateTotal = () => {
  form.total_amount = subtotal.value + (form.shipping_amount || 0)
}

const generateOrderNumber = () => {
  if (showCreateModal.value) {
    form.order_number = 'ORD-' + Date.now()
  }
}

const saveOrder = () => {
  saving.value = true
  
  const url = showCreateModal.value ? '/admin/orders' : `/admin/orders/${selectedOrder.value.id}`
  const method = showCreateModal.value ? 'post' : 'put'
  
  router[method](url, form, {
    preserveState: true,
    onSuccess: () => {
      closeModal()
      loadOrders()
    },
    onFinish: () => {
      saving.value = false
    }
  })
}

const deleteOrder = () => {
  router.delete(`/admin/orders/${selectedOrder.value.id}`, {
    preserveState: true,
    onSuccess: () => {
      showDeleteModal.value = false
      selectedOrder.value = null
      loadOrders()
    }
  })
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  selectedOrder.value = null
  
  // Reset form
  Object.assign(form, {
    customer_id: '',
    order_number: '',
    status: 'pending',
    payment_status: 'pending',
    items: [],
    shipping_amount: 0,
    total_amount: 0,
    notes: ''
  })
}

// Watch filters for auto-search
watch(() => filters.search, () => {
  if (filters.search.length >= 3 || filters.search.length === 0) {
    filters.page = 1
    loadOrders()
  }
}, { debounce: 500 })

watch(() => [filters.status, filters.payment_status, filters.date_from, filters.date_to], () => {
  filters.page = 1
  loadOrders()
})

watch(() => showCreateModal.value, (newVal) => {
  if (newVal) {
    generateOrderNumber()
  }
})

onMounted(() => {
  // Initial load is handled by Inertia
})
</script>
