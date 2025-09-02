<template>
  <div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-semibold text-gray-900">
          {{ customer.first_name }} {{ customer.last_name }}
        </h1>
        <p class="text-sm text-gray-500">{{ customer.email }}</p>
      </div>
      <div class="flex space-x-3">
        <button
          @click="editCustomer"
          class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
        >
          <PencilIcon class="h-4 w-4 mr-2" />
          Modifica
        </button>
        <button
          @click="showDeleteModal = true"
          class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
        >
          <TrashIcon class="h-4 w-4 mr-2" />
          Elimina
        </button>
      </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Customer Information -->
      <div class="lg:col-span-2 space-y-6">
        <!-- Basic Info -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Informazioni Cliente</h3>
          <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Nome completo</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ customer.first_name }} {{ customer.last_name }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Email</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ customer.email }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Telefono</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ customer.phone_number || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Data di nascita</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ customer.date_of_birth ? new Date(customer.date_of_birth).toLocaleDateString('it-IT') : '-' }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Gruppo cliente</dt>
              <dd class="mt-1 text-sm text-gray-900">{{ customer.customer_group?.name || '-' }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Stato</dt>
              <dd class="mt-1">
                <span
                  :class="[
                    'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                    customer.is_active
                      ? 'bg-green-100 text-green-800'
                      : 'bg-red-100 text-red-800',
                  ]"
                >
                  {{ customer.is_active ? 'Attivo' : 'Inattivo' }}
                </span>
              </dd>
            </div>
          </dl>
        </div>

        <!-- Order History -->
        <div class="bg-white rounded-lg shadow-sm border">
          <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-medium text-gray-900">Cronologia Ordini</h3>
          </div>
          
          <div v-if="customer.orders && customer.orders.length === 0" class="p-6 text-center text-gray-500">
            Nessun ordine trovato
          </div>
          
          <div v-else class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Ordine
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Stato
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Totale
                  </th>
                  <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Azioni
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr v-for="order in customer.orders" :key="order.id">
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                    #{{ order.order_number }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ new Date(order.created_at).toLocaleDateString('it-IT') }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                        getOrderStatusClass(order.status)
                      ]"
                    >
                      {{ getOrderStatusLabel(order.status) }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                    {{ formatCurrency(order.total_amount) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                    <button
                      @click="viewOrder(order.id)"
                      class="text-indigo-600 hover:text-indigo-900"
                    >
                      Visualizza
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <!-- Stats Sidebar -->
      <div class="space-y-6">
        <!-- Customer Stats -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Statistiche</h3>
          <dl class="space-y-4">
            <div>
              <dt class="text-sm font-medium text-gray-500">Totale ordini</dt>
              <dd class="mt-1 text-2xl font-semibold text-gray-900">{{ customer.orders?.length || 0 }}</dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Totale speso</dt>
              <dd class="mt-1 text-2xl font-semibold text-gray-900">
                {{ formatCurrency(totalSpent) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Ordine medio</dt>
              <dd class="mt-1 text-2xl font-semibold text-gray-900">
                {{ formatCurrency(averageOrderValue) }}
              </dd>
            </div>
            <div>
              <dt class="text-sm font-medium text-gray-500">Cliente dal</dt>
              <dd class="mt-1 text-sm text-gray-900">
                {{ new Date(customer.created_at).toLocaleDateString('it-IT') }}
              </dd>
            </div>
          </dl>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-lg shadow-sm border p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Azioni Rapide</h3>
          <div class="space-y-3">
            <button
              @click="createOrder"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <PlusIcon class="h-4 w-4 mr-2" />
              Nuovo Ordine
            </button>
            <button
              @click="sendEmail"
              class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <EnvelopeIcon class="h-4 w-4 mr-2" />
              Invia Email
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Confirm Delete Modal -->
    <AlertDialog
      :show="showDeleteModal"
      @close="showDeleteModal = false"
      @confirm="deleteCustomer"
      title="Elimina Cliente"
      message="Sei sicuro di voler eliminare questo cliente? Questa azione non può essere annullata."
    />
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import { router } from '@inertiajs/vue3'
import AlertDialog from "@/components/ui/AlertDialog.vue"
import { useConfirm } from "@/composables/useConfirm.js"
import { PencilIcon, TrashIcon, PlusIcon, EnvelopeIcon } from '@heroicons/vue/24/outline'

// Props
const props = defineProps({
  customer: Object
})

// Use confirm composable
const { confirmState, confirmDelete } = useConfirm()

// Reactive data
const showDeleteModal = ref(false)

// Computed
const totalSpent = computed(() => {
  if (!props.customer.orders) return 0
  return props.customer.orders.reduce((sum, order) => sum + order.total_amount, 0)
})

const averageOrderValue = computed(() => {
  if (!props.customer.orders || props.customer.orders.length === 0) return 0
  return totalSpent.value / props.customer.orders.length
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

const editCustomer = () => {
  // This would normally open an edit modal or navigate to edit page
  console.log('Edit customer', props.customer.id)
}

const deleteCustomer = () => {
  router.delete(`/admin/customers/${props.customer.id}`, {
    onSuccess: () => {
      router.visit('/admin/customers')
    }
  })
}

const viewOrder = (orderId) => {
  router.visit(`/admin/orders/${orderId}`)
}

const createOrder = () => {
  router.visit(`/admin/orders/create?customer_id=${props.customer.id}`)
}

const sendEmail = () => {
  // This would normally open an email modal
  console.log('Send email to', props.customer.email)
}
</script>
