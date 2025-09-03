<template>
  <Head :title="t('customers.show.title', 'Dettagli Cliente')" />
  
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow">
      <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center">
          <div>
            <h1 class="text-3xl font-bold text-gray-900">
              {{ customer.name }}
            </h1>
            <p class="text-gray-600">{{ customer.email }}</p>
          </div>
          <div class="flex space-x-3">
            <Button variant="outline" @click="goBack">
              {{ t('common.back', 'Indietro') }}
            </Button>
            <Button @click="editCustomer">
              {{ t('customers.actions.edit', 'Modifica Cliente') }}
            </Button>
          </div>
        </div>
      </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <div class="px-4 py-6 sm:px-0">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
          
          <!-- Customer Details -->
          <div class="lg:col-span-2 space-y-6">
            <!-- Basic Info Card -->
            <Card>
              <CardHeader>
                <CardTitle>{{ t('customers.details.basic_info', 'Informazioni Base') }}</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div>
                    <Label>{{ t('customers.fields.name', 'Nome') }}</Label>
                    <p class="mt-1 text-sm text-gray-900">{{ customer.name }}</p>
                  </div>
                  <div>
                    <Label>{{ t('customers.fields.email', 'Email') }}</Label>
                    <p class="mt-1 text-sm text-gray-900">{{ customer.email }}</p>
                  </div>
                  <div>
                    <Label>{{ t('customers.fields.phone', 'Telefono') }}</Label>
                    <p class="mt-1 text-sm text-gray-900">{{ customer.phone || '-' }}</p>
                  </div>
                  <div>
                    <Label>{{ t('customers.fields.created_at', 'Data Registrazione') }}</Label>
                    <p class="mt-1 text-sm text-gray-900">{{ formatDate(customer.created_at) }}</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <!-- Orders History -->
            <Card>
              <CardHeader>
                <CardTitle>{{ t('customers.orders.title', 'Storico Ordini') }}</CardTitle>
                <CardDescription>
                  {{ t('customers.orders.description', 'Ultimi ordini del cliente') }}
                </CardDescription>
              </CardHeader>
              <CardContent>
                <div v-if="orders.length > 0" class="space-y-3">
                  <div 
                    v-for="order in orders" 
                    :key="order.id"
                    class="flex justify-between items-center p-3 bg-gray-50 rounded-lg"
                  >
                    <div>
                      <p class="font-medium">#{{ order.id }}</p>
                      <p class="text-sm text-gray-500">{{ formatDate(order.created_at) }}</p>
                    </div>
                    <div class="text-right">
                      <p class="font-medium">{{ formatCurrency(order.total) }}</p>
                      <Badge :variant="getOrderStatusVariant(order.status)">
                        {{ t(`orders.status.${order.status}`, order.status) }}
                      </Badge>
                    </div>
                  </div>
                </div>
                <div v-else class="text-center py-8 text-gray-500">
                  {{ t('customers.orders.empty', 'Nessun ordine trovato') }}
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Fidelity Card Widget -->
          <div class="space-y-6">
            <FidelityCardWidget 
              :customer="customer" 
              :lazy-load="true"
              @updated="refreshCustomer"
            />
            
            <!-- Customer Stats -->
            <Card>
              <CardHeader>
                <CardTitle>{{ t('customers.stats.title', 'Statistiche') }}</CardTitle>
              </CardHeader>
              <CardContent class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                  <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ stats.total_orders }}</div>
                    <div class="text-xs text-gray-500">{{ t('customers.stats.total_orders', 'Ordini Totali') }}</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ formatCurrency(stats.total_spent) }}</div>
                    <div class="text-xs text-gray-500">{{ t('customers.stats.total_spent', 'Spesa Totale') }}</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">{{ formatCurrency(stats.average_order) }}</div>
                    <div class="text-xs text-gray-500">{{ t('customers.stats.average_order', 'Ordine Medio') }}</div>
                  </div>
                  <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">{{ stats.last_order_days }}</div>
                    <div class="text-xs text-gray-500">{{ t('customers.stats.last_order_days', 'Giorni fa') }}</div>
                  </div>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'
import Card from '@/components/ui/Card.vue'
import CardHeader from '@/components/ui/CardHeader.vue'
import CardTitle from '@/components/ui/CardTitle.vue'
import CardDescription from '@/components/ui/CardDescription.vue'
import CardContent from '@/components/ui/CardContent.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import Label from '@/components/ui/label/Label.vue'
import FidelityCardWidget from '@/components/Fidelity/FidelityCardWidget.vue'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  },
  orders: {
    type: Array,
    default: () => []
  },
  stats: {
    type: Object,
    default: () => ({
      total_orders: 0,
      total_spent: 0,
      average_order: 0,
      last_order_days: 0
    })
  }
})

const { t } = useTranslations()

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('it-IT', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const getOrderStatusVariant = (status) => {
  const variants = {
    pending: 'secondary',
    processing: 'default',
    shipped: 'outline',
    completed: 'default',
    cancelled: 'destructive'
  }
  return variants[status] || 'outline'
}

const goBack = () => {
  router.visit(route('cp.customers.index'))
}

const editCustomer = () => {
  router.visit(route('cp.customers.edit', props.customer.id))
}

const refreshCustomer = () => {
  router.reload({ only: ['customer'] })
}
</script>
