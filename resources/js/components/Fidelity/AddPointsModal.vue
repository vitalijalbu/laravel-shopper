<template>
  <Dialog :open="true" @update:open="close">
    <DialogContent class="sm:max-w-lg">
      <DialogHeader>
        <DialogTitle class="flex items-center">
          <svg class="h-6 w-6 text-blue-600 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
          </svg>
          {{ t('fidelity.actions.add_points', 'Aggiungi Punti') }}
        </DialogTitle>
        <DialogDescription>
          {{ t('fidelity.modals.add_points.description', 'Aggiungi punti fedeltà a :customer', { customer: customer.name }) }}
        </DialogDescription>
      </DialogHeader>

      <form @submit.prevent="submitForm" class="space-y-4">
        <!-- Points Amount -->
        <div>
          <Label for="points">
            {{ t('fidelity.modals.add_points.points_amount', 'Quantità punti') }}
          </Label>
          <div class="mt-1 relative">
            <input
              id="points"
              v-model.number="form.points"
              type="number"
              min="1"
              step="1"
              required
              class="block w-full pr-12 border-gray-300 rounded-md focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
              :class="{ 'border-red-300': form.errors.points }"
              :placeholder="t('fidelity.modals.add_points.points_placeholder', 'Es. 100')"
            >
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
              <span class="text-gray-500 sm:text-sm">
                {{ t('fidelity.points', 'punti') }}
              </span>
            </div>
          </div>
          <p v-if="form.errors.points" class="mt-1 text-sm text-red-600">
            {{ form.errors.points }}
          </p>
        </div>

        <!-- Transaction Type -->
        <div>
          <Label for="type">
            {{ t('fidelity.modals.add_points.transaction_type', 'Tipo transazione') }}
          </Label>
          <select
            id="type"
            v-model="form.type"
            required
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.type }"
          >
            <option value="earned">{{ t('fidelity.transactions.types.earned', 'Guadagnati') }}</option>
            <option value="adjusted">{{ t('fidelity.transactions.types.adjusted', 'Modificati') }}</option>
          </select>
          <p v-if="form.errors.type" class="mt-1 text-sm text-red-600">
            {{ form.errors.type }}
          </p>
        </div>

        <!-- Description -->
        <div>
          <Label for="description">
            {{ t('fidelity.modals.add_points.description', 'Descrizione') }}
          </Label>
          <textarea
            id="description"
            v-model="form.description"
            rows="3"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.description }"
            :placeholder="t('fidelity.modals.add_points.description_placeholder', 'Motivo della modifica punti...')"
          ></textarea>
          <p v-if="form.errors.description" class="mt-1 text-sm text-red-600">
            {{ form.errors.description }}
          </p>
        </div>

        <!-- Expires At -->
        <div>
          <Label for="expires_at">
            {{ t('fidelity.modals.add_points.expires_at', 'Data scadenza') }}
          </Label>
          <input
            id="expires_at"
            v-model="form.expires_at"
            type="date"
            :min="minExpirationDate"
            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
            :class="{ 'border-red-300': form.errors.expires_at }"
          >
          <p class="mt-1 text-xs text-gray-500">
            {{ t('fidelity.modals.add_points.expires_at_help', 'Lascia vuoto per nessuna scadenza') }}
          </p>
          <p v-if="form.errors.expires_at" class="mt-1 text-sm text-red-600">
            {{ form.errors.expires_at }}
          </p>
        </div>

        <DialogFooter>
          <Button
            type="button"
            @click="close"
            :disabled="form.processing"
            variant="outline"
          >
            {{ t('common.cancel', 'Annulla') }}
          </Button>
          <Button
            type="submit"
            :disabled="form.processing"
          >
            <svg v-if="form.processing" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            {{ form.processing ? t('common.processing', 'Elaborazione') : t('fidelity.actions.add_points', 'Aggiungi Punti') }}
          </Button>
        </DialogFooter>
      </form>
    </DialogContent>
  </Dialog>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'
import Dialog from '@/components/ui/dialog/Dialog.vue'
import DialogContent from '@/components/ui/dialog/DialogContent.vue'
import DialogHeader from '@/components/ui/dialog/DialogHeader.vue'
import DialogTitle from '@/components/ui/dialog/DialogTitle.vue'
import DialogDescription from '@/components/ui/dialog/DialogDescription.vue'
import DialogFooter from '@/components/ui/dialog/DialogFooter.vue'
import Button from '@/components/ui/button/Button.vue'
import Label from '@/components/ui/label/Label.vue'

const props = defineProps({
  customer: {
    type: Object,
    required: true
  }
})

const emit = defineEmits(['close', 'points-added'])

const { t } = useTranslations()

// Inertia form
const form = useForm({
  points: null,
  type: 'earned',
  description: '',
  expires_at: ''
})

const minExpirationDate = computed(() => {
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  return tomorrow.toISOString().split('T')[0]
})

const submitForm = () => {
  form.post(route('api.customers.fidelity.points.store', props.customer.id), {
    onSuccess: () => {
      emit('points-added')
      close()
    },
    onError: (errors) => {
      console.error('Validation errors:', errors)
    }
  })
}

const close = () => {
  emit('close')
}

// Set default expiration date on mount
onMounted(() => {
  const defaultExpirationDays = 365 // 1 year from now
  const defaultExpiration = new Date()
  defaultExpiration.setDate(defaultExpiration.getDate() + defaultExpirationDays)
  form.expires_at = defaultExpiration.toISOString().split('T')[0]
})
</script>

<style scoped>
.animate-spin {
  animation: spin 1s linear infinite;
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
