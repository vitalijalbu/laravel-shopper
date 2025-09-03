<template>
  <div class="customer-fidelity-section">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-6">
      <div>
        <h2 class="text-xl font-semibold text-gray-900">
          {{ $t('fidelity.title') }}
        </h2>
        <p class="text-sm text-gray-600 mt-1">
          {{ $t('fidelity.section_description') }}
        </p>
      </div>
      
      <!-- Refresh Button -->
      <button 
        @click="refreshFidelityData"
        :disabled="refreshing"
        class="inline-flex items-center px-3 py-2 border border-gray-300 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
      >
        <svg 
          class="h-4 w-4 mr-2"
          :class="{ 'animate-spin': refreshing }"
          fill="none" 
          viewBox="0 0 24 24" 
          stroke="currentColor"
        >
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
        </svg>
        {{ $t('common.refresh') }}
      </button>
    </div>

    <!-- Loading State -->
    <div v-if="initialLoading" class="space-y-4">
      <div class="animate-pulse">
        <div class="bg-gradient-to-r from-gray-200 to-gray-300 rounded-lg h-32"></div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div class="animate-pulse bg-gray-200 rounded-lg h-20"></div>
        <div class="animate-pulse bg-gray-200 rounded-lg h-20"></div>
      </div>
    </div>

    <!-- Fidelity Card Widget -->
    <div v-else>
      <fidelity-card-widget
        :customer="customerWithFidelity"
        :lazy-load="false"
        @updated="handleFidelityUpdate"
      />
    </div>

    <!-- Recent Transactions Section -->
    <div v-if="!initialLoading && customerWithFidelity.fidelity.has_card" class="mt-8">
      <div class="flex items-center justify-between mb-4">
        <h3 class="text-lg font-medium text-gray-900">
          {{ $t('fidelity.recent_transactions') }}
        </h3>
        <router-link 
          :to="{ name: 'customer.fidelity.transactions', params: { id: customer.id } }"
          class="text-sm text-blue-600 hover:text-blue-500"
        >
          {{ $t('fidelity.view_all_transactions') }} →
        </router-link>
      </div>
      
      <fidelity-transactions
        :customer-id="customer.id"
        :lazy-load="false"
        :limit="5"
      />
    </div>

    <!-- Quick Stats Cards -->
    <div v-if="!initialLoading && customerWithFidelity.fidelity.has_card" class="mt-8">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Lifetime Points Earned -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    {{ $t('fidelity.stats.lifetime_earned') }}
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ customerFidelityDetails?.total_earned || 0 }} {{ $t('fidelity.points') }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Total Redeemed -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    {{ $t('fidelity.stats.total_redeemed') }}
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ customerFidelityDetails?.total_redeemed || 0 }} {{ $t('fidelity.points') }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>

        <!-- Current Tier -->
        <div class="bg-white overflow-hidden shadow rounded-lg border border-gray-200">
          <div class="p-5">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <svg class="h-6 w-6 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
              </div>
              <div class="ml-5 w-0 flex-1">
                <dl>
                  <dt class="text-sm font-medium text-gray-500 truncate">
                    {{ $t('fidelity.stats.current_tier') }}
                  </dt>
                  <dd class="text-lg font-medium text-gray-900">
                    {{ getTierDisplayName(customerWithFidelity.fidelity.tier) }}
                  </dd>
                </dl>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import { useFidelityStore } from '@/stores/fidelity'
import FidelityCardWidget from './FidelityCardWidget.vue'
import FidelityTransactions from './FidelityTransactions.vue'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['customer-updated'])

const { t } = useI18n()
const fidelityStore = useFidelityStore()

const initialLoading = ref(true)
const refreshing = ref(false)
const customerFidelityDetails = ref(null)

// Computed customer with fidelity information
const customerWithFidelity = computed(() => {
  const customer = { ...props.customer }
  
  // Ensure fidelity object exists with proper structure
  if (!customer.fidelity) {
    customer.fidelity = {
      has_card: false,
      card_number: null,
      points: 0,
      card_status: null,
      tier: null,
      card_details: null
    }
  }

  return customer
})

const getTierDisplayName = (tier) => {
  if (!tier) return t('fidelity.tiers.no_tier')
  
  if (tier.threshold === 0) return t('fidelity.tiers.bronze')
  if (tier.threshold === 100) return t('fidelity.tiers.silver')
  if (tier.threshold === 500) return t('fidelity.tiers.gold')
  if (tier.threshold === 1000) return t('fidelity.tiers.platinum')
  
  return `${t('fidelity.tier')} ${tier.rate}x`
}

const loadFidelityData = async () => {
  if (!props.customer.id) return

  try {
    // Load customer fidelity details if the customer has a card
    if (customerWithFidelity.value.fidelity.has_card) {
      customerFidelityDetails.value = await fidelityStore.loadCustomerFidelityDetails(props.customer.id)
    }
  } catch (error) {
    console.error('Error loading fidelity data:', error)
  } finally {
    initialLoading.value = false
  }
}

const refreshFidelityData = async () => {
  refreshing.value = true
  
  try {
    if (customerWithFidelity.value.fidelity.has_card) {
      customerFidelityDetails.value = await fidelityStore.loadCustomerFidelityDetails(props.customer.id)
    }
    
    // Emit event to refresh customer data
    emit('customer-updated')
  } catch (error) {
    console.error('Error refreshing fidelity data:', error)
  } finally {
    refreshing.value = false
  }
}

const handleFidelityUpdate = () => {
  // Reload fidelity data when card is created or updated
  refreshFidelityData()
}

// Watch for customer changes
watch(() => props.customer, (newCustomer) => {
  if (newCustomer?.id) {
    loadFidelityData()
  }
}, { immediate: false })

// Load data on mount
onMounted(() => {
  loadFidelityData()
})
</script>

<style scoped>
.animate-pulse {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: .5;
  }
}

@keyframes spin {
  from {
    transform: rotate(0deg);
  }
  to {
    transform: rotate(360deg);
  }
}
</style>
