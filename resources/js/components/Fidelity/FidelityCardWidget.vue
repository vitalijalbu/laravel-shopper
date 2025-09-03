<template>
  <Card class="fidelity-card-widget">
    <CardHeader v-if="!customer.fidelity_card_number">
      <div class="flex items-center justify-between">
        <div class="flex items-center">
          <svg class="w-8 h-8 text-gray-400 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
          </svg>
          <div>
            <CardTitle class="text-sm">{{ t('fidelity.title', 'Carta Fedeltà') }}</CardTitle>
            <CardDescription>{{ t('fidelity.messages.card_not_found', 'Carta fedeltà non trovata') }}</CardDescription>
          </div>
        </div>
        <Button 
          @click="createFidelityCard"
          :disabled="creating"
          size="sm"
        >
          {{ creating ? t('common.creating', 'Creazione') + '...' : t('fidelity.actions.create_card', 'Crea Carta') }}
        </Button>
      </div>
    </CardHeader>

    <template v-else>
      <!-- Card Header with Points -->
      <CardHeader class="bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-t-xl">
        <div class="flex justify-between items-start">
          <div>
            <CardTitle class="text-white">{{ t('fidelity.title', 'Carta Fedeltà') }}</CardTitle>
            <p class="text-blue-100 text-sm mt-1">{{ customer.fidelity_card_number }}</p>
          </div>
          <div class="text-right">
            <div class="text-2xl font-bold">{{ customer.fidelity_points || 0 }}</div>
            <div class="text-sm text-blue-100">{{ t('fidelity.points', 'Punti') }}</div>
          </div>
        </div>

        <!-- Tier Badge -->
        <div v-if="customer.fidelity_tier" class="mt-3">
          <Badge variant="secondary" class="bg-white/20 text-white border-white/30">
            {{ getTierName(customer.fidelity_tier) }}
            <span class="ml-1">{{ customer.fidelity_tier.rate }}x</span>
          </Badge>
        </div>
      </CardHeader>

      <CardContent class="p-4">
        <!-- Card Stats -->
        <div class="grid grid-cols-2 gap-4 mb-4">
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">{{ t('fidelity.total_earned', 'Totale Guadagnato') }}</div>
            <div class="text-lg font-semibold text-green-600">
              {{ customerFidelityDetails?.total_earned || 0 }}
            </div>
          </div>
          <div class="text-center p-3 bg-gray-50 rounded-lg">
            <div class="text-sm text-gray-500">{{ t('fidelity.total_redeemed', 'Totale Riscattato') }}</div>
            <div class="text-lg font-semibold text-red-600">
              {{ customerFidelityDetails?.total_redeemed || 0 }}
            </div>
          </div>
        </div>

        <!-- Spending Progress -->
        <div v-if="customer.fidelity_tier && nextTier" class="mb-4">
          <div class="flex justify-between text-sm text-gray-600 mb-2">
            <span>{{ t('fidelity.next_tier', 'Prossimo Livello') }}: {{ getTierName(nextTier) }}</span>
            <span>{{ formatCurrency(nextTier.amount_needed) }} {{ t('fidelity.amount_needed', 'necessari') }}</span>
          </div>
          <div class="w-full bg-gray-200 rounded-full h-2">
            <div 
              class="bg-blue-600 h-2 rounded-full transition-all" 
              :style="{ width: `${getProgressPercentage()}%` }"
            ></div>
          </div>
        </div>

        <!-- Quick Actions -->
        <div class="flex space-x-2">
          <Button 
            @click="showTransactions = !showTransactions"
            variant="outline"
            size="sm"
            class="flex-1"
          >
            {{ t('fidelity.actions.view_transactions', 'Vedi Transazioni') }}
          </Button>
          <Button 
            @click="openAddPointsModal"
            size="sm"
            class="flex-1"
          >
            {{ t('fidelity.actions.add_points', 'Aggiungi Punti') }}
          </Button>
        </div>
      </CardContent>
    </template>

    <!-- Transactions List (Lazy Loaded) -->
    <CardContent v-if="showTransactions && customer.fidelity_card_number" class="pt-0">
      <fidelity-transactions 
        :customer-id="customer.id"
        :lazy-load="true"
      />
    </CardContent>

    <!-- Add Points Modal -->
    <add-points-modal
      v-if="showAddPointsModal"
      :customer="customer"
      @close="showAddPointsModal = false"
      @points-added="onPointsAdded"
    />
  </Card>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'
import Card from '@/components/ui/Card.vue'
import CardHeader from '@/components/ui/CardHeader.vue'
import CardTitle from '@/components/ui/CardTitle.vue'
import CardDescription from '@/components/ui/CardDescription.vue'
import CardContent from '@/components/ui/CardContent.vue'
import Button from '@/components/ui/button/Button.vue'
import Badge from '@/components/ui/badge/Badge.vue'
import FidelityTransactions from './FidelityTransactions.vue'
import AddPointsModal from './AddPointsModal.vue'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  },
  lazyLoad: {
    type: Boolean,
    default: true
  }
})

const emit = defineEmits(['updated'])

const { t } = useTranslations()

const creating = ref(false)
const showTransactions = ref(false)
const showAddPointsModal = ref(false)
const customerFidelityDetails = ref(null)

// Inertia form for creating fidelity card
const createCardForm = useForm({})

const nextTier = computed(() => {
  if (!props.customer.fidelity_tier || !customerFidelityDetails.value) {
    return null
  }

  const tiers = [
    { threshold: 0, rate: 1, name: 'bronze' },
    { threshold: 100, rate: 1.5, name: 'silver' },
    { threshold: 500, rate: 2, name: 'gold' },
    { threshold: 1000, rate: 3, name: 'platinum' }
  ]

  const currentTier = props.customer.fidelity_tier
  const currentSpent = customerFidelityDetails.value.total_spent_amount

  for (const tier of tiers) {
    if (tier.threshold > currentTier.threshold && currentSpent < tier.threshold) {
      return {
        ...tier,
        amount_needed: tier.threshold - currentSpent
      }
    }
  }

  return null
})

const getTierName = (tier) => {
  if (!tier) return ''
  
  if (tier.threshold === 0) return t('fidelity.tiers.bronze', 'Bronzo')
  if (tier.threshold === 100) return t('fidelity.tiers.silver', 'Argento')
  if (tier.threshold === 500) return t('fidelity.tiers.gold', 'Oro')
  if (tier.threshold === 1000) return t('fidelity.tiers.platinum', 'Platino')
  
  return `Tier ${tier.rate}x`
}

const getProgressPercentage = () => {
  if (!nextTier.value || !customerFidelityDetails.value) return 0
  
  const currentSpent = customerFidelityDetails.value.total_spent_amount
  const currentTierThreshold = props.customer.fidelity_tier.threshold
  const nextTierThreshold = nextTier.value.threshold
  
  const progress = (currentSpent - currentTierThreshold) / (nextTierThreshold - currentTierThreshold)
  return Math.min(Math.max(progress * 100, 0), 100)
}

const formatCurrency = (amount) => {
  return new Intl.NumberFormat('it-IT', {
    style: 'currency',
    currency: 'EUR'
  }).format(amount)
}

const createFidelityCard = () => {
  creating.value = true
  
  createCardForm.post(route('api.customers.fidelity.store', props.customer.id), {
    onSuccess: () => {
      emit('updated')
      creating.value = false
    },
    onError: () => {
      creating.value = false
    }
  })
}

const openAddPointsModal = () => {
  showAddPointsModal.value = true
}

const onPointsAdded = () => {
  showAddPointsModal.value = false
  emit('updated')
}

// Lazy load fidelity data when component is visible
onMounted(async () => {
  if (props.lazyLoad && props.customer.fidelity_card_number) {
    try {
      // Load via Inertia visit or fetch
      const response = await fetch(route('api.customers.fidelity.show', props.customer.id))
      if (response.ok) {
        const data = await response.json()
        customerFidelityDetails.value = data.data
      }
    } catch (error) {
      console.error('Error loading customer fidelity details:', error)
    }
  }
})
</script>
