<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          Ordine #{{ order.order_number }}
        </h1>
        <p class="text-sm text-gray-500">
          Creato il {{ new Date(order.created_at).toLocaleDateString('it-IT') }}
        </p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="editOrder"
          class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <PencilIcon class="h-4 w-4 mr-2" />
          Modifica
        </button>
        <button
          @click="printOrder"
          class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <PrinterIcon class="h-4 w-4 mr-2" />
          Stampa
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Order Details -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Order Status -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">Stato Ordine</h3>
            <button
              @click="showStatusModal = true"
              class="text-sm text-indigo-600 hover:text-indigo-900"
            >
              Modifica stato
            </button>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-500">Stato ordine</label>
              <span
                :class="[
                  'inline-flex mt-1 px-3 py-1 text-sm font-medium rounded-full',
                  getOrderStatusClass(order.status)
                ]"
              >
                {{ getOrderStatusLabel(order.status) }}
              </span>
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-500">Stato pagamento</label>
              <span
                :class="[
                  'inline-flex mt-1 px-3 py-1 text-sm font-medium rounded-full',
                  getPaymentStatusClass(order.payment_status)
                ]"
              >
                {{ getPaymentStatusLabel(order.payment_status) }}
              </span>
            </div>
          </div>
        </div>

        <!-- Customer Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Cliente</h3>
          <div v-if="order.customer" class="space-y-2">
            <p class="text-sm">
              <span class="font-medium">Nome:</span>
              <a
                @click="viewCustomer"
                class="ml-2 text-indigo-600 hover:text-indigo-900 cursor-pointer"
              >
                {{ order.customer.first_name }} {{ order.customer.last_name }}
              </a>
            </p>
            <p class="text-sm">
              <span class="font-medium">Email:</span>
              <span class="ml-2">{{ order.customer.email }}</span>
            </p>
            <p v-if="order.customer.phone_number" class="text-sm">
              <span class="font-medium">Telefono:</span>
              <span class="ml-2">{{ order.customer.phone_number }}</span>
            </p>
          </div>
          <div v-else class="text-sm text-gray-500">
            Cliente non trovato o eliminato
          </div>
        </div>

        <!-- Order Items -->
        <div class="bg-white rounded-lg shadow-sm border">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Prodotti Ordinati</h3>
          </div>
          
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prodotto
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Prezzo unitario
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Quantità
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Totale
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="item in order.items" :key="item.id">
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div class="text-sm font-medium text-gray-900">
                      {{ item.product?.name || 'Prodotto eliminato' }}
                    </div>
                    <div v-if="item.product?.sku" class="text-sm text-gray-500">
                      SKU: {{ item.product.sku }}
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.unit_price) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ item.quantity }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(item.total_price || (item.unit_price * item.quantity)) }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <!-- Order Notes -->
        <div v-if="order.notes" class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Note</h3>
          <p class="text-sm text-gray-700 whitespace-pre-wrap">{{ order.notes }}</p>
        </div>
      </div>

      <!-- Order Summary Sidebar -->
      <div class="space-y-6">
        <!-- Order Summary -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Riepilogo Ordine</h3>
          <dl class="space-y-3">
            <div class="flex justify-between">
              <dt class="text-sm text-gray-500">Subtotale</dt>
              <dd class="text-sm text-gray-900">{{ formatCurrency(subtotal) }}</dd>
            </div>
            <div v-if="order.shipping_amount" class="flex justify-between">
              <dt class="text-sm text-gray-500">Spedizione</dt>
              <dd class="text-sm text-gray-900">{{ formatCurrency(order.shipping_amount) }}</dd>
            </div>
            <div v-if="order.tax_amount" class="flex justify-between">
              <dt class="text-sm text-gray-500">Tasse</dt>
              <dd class="text-sm text-gray-900">{{ formatCurrency(order.tax_amount) }}</dd>
            </div>
            <div v-if="order.discount_amount" class="flex justify-between">
              <dt class="text-sm text-gray-500">Sconto</dt>
              <dd class="text-sm text-red-600">-{{ formatCurrency(order.discount_amount) }}</dd>
            </div>
            <div class="border-t border-gray-200 pt-3">
              <div class="flex justify-between">
                <dt class="text-base font-medium text-gray-900">Totale</dt>
                <dd class="text-base font-medium text-gray-900">{{ formatCurrency(order.total_amount) }}</dd>
              </div>
            </div>
          </dl>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Azioni Rapide</h3>
          <div class="space-y-3">
            <select
              v-model="quickActionStatus"
              @change="updateOrderStatus"
              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
            >
              <option value="">Cambia stato...</option>
              <option value="pending">In attesa</option>
              <option value="processing">In elaborazione</option>
              <option value="shipped">Spedito</option>
              <option value="delivered">Consegnato</option>
              <option value="cancelled">Annullato</option>
              <option value="refunded">Rimborsato</option>
            </select>
            
            <button
              @click="sendOrderEmail"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <EnvelopeIcon class="h-4 w-4 mr-2" />
              Invia email cliente
            </button>
            
            <button
              @click="duplicateOrder"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <DocumentDuplicateIcon class="h-4 w-4 mr-2" />
              Duplica ordine
            </button>
          </div>
        </div>

        <!-- Order Information -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni</h3>
          <dl class="space-y-3">
            <div>
              <dt class="text-sm font-medium text-gray-500">Numero ordine</dt>
              <dd class="mt-1 text-sm text-gray-900">#{{ order.order_number }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Data creazione</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ new Date(order.created_at).toLocaleDateString('it-IT') }}
              </dd>
            </div>
            <div v-if="order.updated_at !== order.created_at">
              <dt class="text-sm font-medium text-gray-500">Ultimo aggiornamento</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ new Date(order.updated_at).toLocaleDateString('it-IT') }}
              </dd>
            </div>
          </dl>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  PencilIcon, 
  PrinterIcon, 
  EnvelopeIcon, 
  DocumentDuplicateIcon 
} from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  order: Object
})

// Reactive data
const showStatusModal = ref(false)
const quickActionStatus = ref('')

// Computed
const subtotal = computed(() => {
  if (!props.order.items) return 0
  return props.order.items.reduce((sum, item) => {
    return sum + (item.total_price || (item.unit_price * item.quantity))
  }, 0)
})

// Methods
const formatCurrency = (amount) => {
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount || 0)
}

const getOrderStatusLabel = (status) => {
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

const getOrderStatusClass = (status) => {
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

const editOrder = () => {
  router.visit(`/admin/orders/${props.order.id}/edit`)
}

const viewCustomer = () => {
  if (props.order.customer) {
    router.visit(`/admin/customers/${props.order.customer.id}`)
  }
}

const updateOrderStatus = () => {
  if (!quickActionStatus.value) return

  router.patch(`/admin/orders/${props.order.id}/status`, {
    status: quickActionStatus.value
  }, {
    onSuccess: () => {
      quickActionStatus.value = ''
    }
  })
}

const printOrder = () => {
  window.print()
}

const sendOrderEmail = () => {
  // This would normally open an email modal
  console.log('Send order email')
}

const duplicateOrder = () => {
  router.post(`/admin/orders/${props.order.id}/duplicate`)
}
</script>
