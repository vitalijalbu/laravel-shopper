<template>
  <ShopperModal
    :show="show"
    @close="$emit('update:show', false)"
    :max-width="'3xl'"
  >
    <template #header>
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-medium text-gray-900">
          Statistiche sconto
        </h3>
        <ShopperBadge :variant="getStatusVariant(discount?.status)">
          {{ $t(`discount.statuses.${discount?.status}`) }}
        </ShopperBadge>
      </div>
    </template>

    <div v-if="discount" class="space-y-6">
      <!-- Discount Info -->
      <div class="bg-gray-50 rounded-lg p-4">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <h4 class="font-medium text-gray-900">{{ discount.name }}</h4>
            <p class="text-sm text-gray-500 font-mono">{{ discount.code }}</p>
          </div>
          <div class="text-right">
            <div class="text-lg font-semibold text-gray-900">
              {{ discount.formatted_value }}
            </div>
            <div class="text-sm text-gray-500">
              {{ $t(`discount.types.${discount.type}`) }}
            </div>
          </div>
        </div>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center py-8">
        <ShopperSpinner size="lg" />
      </div>

      <!-- Statistics -->
      <div v-else-if="statistics" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Applications -->
        <div class="bg-white p-4 rounded-lg border">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm text-gray-500">Applicazioni totali</p>
              <p class="text-2xl font-semibold text-gray-900">
                {{ statistics.total_applications || 0 }}
              </p>
            </div>
            <div class="flex-shrink-0">
              <ReceiptPercentIcon class="w-8 h-8 text-blue-500" />
            </div>
          </div>
        </div>

        <!-- Total Discount Amount -->
        <div class="bg-white p-4 rounded-lg border">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm text-gray-500">Sconto totale</p>
              <p class="text-2xl font-semibold text-gray-900">
                €{{ formatMoney(statistics.total_discount_amount || 0) }}
              </p>
            </div>
            <div class="flex-shrink-0">
              <CurrencyEuroIcon class="w-8 h-8 text-green-500" />
            </div>
          </div>
        </div>

        <!-- Unique Customers -->
        <div class="bg-white p-4 rounded-lg border">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm text-gray-500">Clienti unici</p>
              <p class="text-2xl font-semibold text-gray-900">
                {{ statistics.unique_customers || 0 }}
              </p>
            </div>
            <div class="flex-shrink-0">
              <UsersIcon class="w-8 h-8 text-purple-500" />
            </div>
          </div>
        </div>

        <!-- Usage Percentage -->
        <div class="bg-white p-4 rounded-lg border">
          <div class="flex items-center">
            <div class="flex-1">
              <p class="text-sm text-gray-500">Utilizzo</p>
              <div class="flex items-baseline space-x-1">
                <p class="text-2xl font-semibold text-gray-900">
                  {{ Math.round(statistics.usage_percentage || 0) }}%
                </p>
                <p class="text-sm text-gray-500">
                  ({{ discount.usage_count }}/{{ discount.usage_limit || '∞' }})
                </p>
              </div>
            </div>
            <div class="flex-shrink-0">
              <ChartBarIcon class="w-8 h-8 text-orange-500" />
            </div>
          </div>
        </div>
      </div>

      <!-- Usage Progress -->
      <div v-if="discount.usage_limit" class="space-y-2">
        <div class="flex justify-between text-sm">
          <span class="text-gray-500">Utilizzi</span>
          <span class="font-medium">{{ discount.usage_count }} / {{ discount.usage_limit }}</span>
        </div>
        <ShopperProgress 
          :value="(discount.usage_count / discount.usage_limit) * 100"
          :color="getUsageProgressColor(discount.usage_count, discount.usage_limit)"
        />
      </div>

      <!-- Time Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <!-- Created -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h5 class="font-medium text-gray-900 mb-2">Data creazione</h5>
          <p class="text-sm text-gray-600">
            {{ formatDateTime(discount.created_at) }}
          </p>
        </div>

        <!-- Schedule -->
        <div class="bg-gray-50 rounded-lg p-4">
          <h5 class="font-medium text-gray-900 mb-2">Programmazione</h5>
          <div class="space-y-1 text-sm text-gray-600">
            <div v-if="discount.starts_at">
              <span class="font-medium">Inizio:</span> {{ formatDateTime(discount.starts_at) }}
            </div>
            <div v-if="discount.expires_at">
              <span class="font-medium">Scadenza:</span> {{ formatDateTime(discount.expires_at) }}
            </div>
            <div v-if="!discount.starts_at && !discount.expires_at">
              Sempre attivo
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Activity -->
      <div v-if="statistics?.recent_activity?.length" class="space-y-4">
        <h5 class="font-medium text-gray-900">Attività recente</h5>
        <div class="bg-white rounded-lg border">
          <div class="divide-y divide-gray-200">
            <div
              v-for="activity in statistics.recent_activity.slice(0, 5)"
              :key="activity.id"
              class="p-4 flex items-center justify-between"
            >
              <div class="flex-1">
                <p class="text-sm font-medium text-gray-900">
                  Ordine #{{ activity.applicable?.id || 'N/A' }}
                </p>
                <p class="text-sm text-gray-500">
                  Cliente: {{ activity.applicable?.customer?.name || 'N/A' }}
                </p>
              </div>
              <div class="text-right">
                <p class="text-sm font-medium text-green-600">
                  -€{{ formatMoney(activity.discount_amount) }}
                </p>
                <p class="text-xs text-gray-500">
                  {{ formatDate(activity.applied_at) }}
                </p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- No Data -->
      <div v-else-if="!loading" class="text-center py-8">
        <ChartBarIcon class="w-12 h-12 text-gray-400 mx-auto mb-4" />
        <p class="text-gray-500">Nessuna attività registrata per questo sconto</p>
      </div>
    </div>

    <template #footer>
      <div class="flex justify-end">
        <ShopperButton
          variant="outline"
          @click="$emit('update:show', false)"
        >
          Chiudi
        </ShopperButton>
      </div>
    </template>
  </ShopperModal>
</template>

<script setup>
import { ref, watch, computed } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'
import {
  ReceiptPercentIcon,
  CurrencyEuroIcon,
  UsersIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

// Components
import ShopperModal from '@/components/ui/ShopperModal.vue'
import ShopperBadge from '@/components/ui/ShopperBadge.vue'
import ShopperButton from '@/components/ui/ShopperButton.vue'
import ShopperSpinner from '@/components/ui/ShopperSpinner.vue'
import ShopperProgress from '@/components/ui/ShopperProgress.vue'

// Props & Emits
const props = defineProps({
  show: Boolean,
  discount: Object,
})

const emit = defineEmits(['update:show'])

// Composables
const { $t } = useTranslations()
const { api } = useApi()

// State
const loading = ref(false)
const statistics = ref(null)

// Methods
const loadStatistics = async () => {
  if (!props.discount?.id) return

  loading.value = true
  try {
    const response = await api.get(`/admin/discounts/${props.discount.id}`)
    statistics.value = response.data.statistics
  } catch (error) {
    console.error('Error loading statistics:', error)
  } finally {
    loading.value = false
  }
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

const getUsageProgressColor = (used, limit) => {
  const percentage = (used / limit) * 100
  if (percentage >= 90) return 'red'
  if (percentage >= 75) return 'orange'
  if (percentage >= 50) return 'yellow'
  return 'green'
}

const formatMoney = (amount) => {
  return new Intl.NumberFormat('it-IT', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2,
  }).format(amount)
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('it-IT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
  })
}

const formatDateTime = (date) => {
  return new Date(date).toLocaleString('it-IT', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  })
}

// Watchers
watch(() => props.show, (show) => {
  if (show && props.discount) {
    loadStatistics()
  }
})

watch(() => props.discount, (discount) => {
  if (props.show && discount) {
    loadStatistics()
  }
})
</script>
