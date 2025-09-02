<template>
  <TransitionRoot as="template" :show="show">
    <Dialog as="div" class="relative z-50" @close="$emit('close')">
      <TransitionChild
        as="template"
        enter="ease-out duration-300"
        enter-from="opacity-0"
        enter-to="opacity-100"
        leave="ease-in duration-200"
        leave-from="opacity-100"
        leave-to="opacity-0"
      >
        <div class="fixed inset-0 bg-black bg-opacity-25" />
      </TransitionChild>

      <div class="fixed inset-0 z-10 overflow-y-auto">
        <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
          <TransitionChild
            as="template"
            enter="ease-out duration-300"
            enter-from="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
            enter-to="opacity-100 translate-y-0 sm:scale-100"
            leave="ease-in duration-200"
            leave-from="opacity-100 translate-y-0 sm:scale-100"
            leave-to="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
          >
            <DialogPanel class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-2xl">
              <!-- Header -->
              <div class="bg-white px-6 py-4 border-b border-gray-200">
                <div class="flex items-center justify-between">
                  <DialogTitle as="h3" class="text-lg font-medium leading-6 text-gray-900">
                    {{ isEditing ? 'Modifica Gateway' : 'Nuovo Gateway di Pagamento' }}
                  </DialogTitle>
                  <button
                    @click="$emit('close')"
                    class="rounded-md text-gray-400 hover:text-gray-500 focus:outline-none"
                  >
                    <XMarkIcon class="h-6 w-6" />
                  </button>
                </div>
              </div>

              <!-- Form -->
              <form @submit.prevent="saveGateway" class="px-6 py-6 space-y-6">
                
                <!-- Basic Information -->
                <div class="space-y-4">
                  <h4 class="text-sm font-medium text-gray-900">Informazioni di Base</h4>
                  
                  <!-- Name -->
                  <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                      Nome Gateway *
                    </label>
                    <input
                      id="name"
                      v-model="form.name"
                      type="text"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Es: Stripe, PayPal"
                    />
                    <p v-if="errors.name" class="mt-1 text-sm text-red-600">{{ errors.name }}</p>
                  </div>

                  <!-- Provider -->
                  <div>
                    <label for="provider" class="block text-sm font-medium text-gray-700">
                      Provider *
                    </label>
                    <select
                      id="provider"
                      v-model="form.provider"
                      required
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                    >
                      <option value="">Seleziona provider</option>
                      <option v-for="(label, value) in providers" :key="value" :value="value">
                        {{ label }}
                      </option>
                    </select>
                    <p v-if="errors.provider" class="mt-1 text-sm text-red-600">{{ errors.provider }}</p>
                  </div>

                  <!-- Description -->
                  <div>
                    <label for="description" class="block text-sm font-medium text-gray-700">
                      Descrizione
                    </label>
                    <textarea
                      id="description"
                      v-model="form.description"
                      rows="3"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="Descrizione del gateway di pagamento..."
                    ></textarea>
                    <p v-if="errors.description" class="mt-1 text-sm text-red-600">{{ errors.description }}</p>
                  </div>
                </div>

                <!-- Configuration -->
                <div class="space-y-4">
                  <h4 class="text-sm font-medium text-gray-900">Configurazione</h4>
                  
                  <!-- Test Mode -->
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-700">Modalità Test</label>
                      <p class="text-sm text-gray-500">Utilizza le credenziali di test per questo gateway</p>
                    </div>
                    <button
                      type="button"
                      @click="form.test_mode = !form.test_mode"
                      :class="[
                        form.test_mode ? 'bg-blue-600' : 'bg-gray-200',
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                      ]"
                    >
                      <span
                        :class="[
                          form.test_mode ? 'translate-x-5' : 'translate-x-0',
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      />
                    </button>
                  </div>

                  <!-- Is Enabled -->
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-700">Gateway Attivo</label>
                      <p class="text-sm text-gray-500">Permetti ai clienti di utilizzare questo gateway</p>
                    </div>
                    <button
                      type="button"
                      @click="form.is_enabled = !form.is_enabled"
                      :class="[
                        form.is_enabled ? 'bg-green-600' : 'bg-gray-200',
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2'
                      ]"
                    >
                      <span
                        :class="[
                          form.is_enabled ? 'translate-x-5' : 'translate-x-0',
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      />
                    </button>
                  </div>

                  <!-- Is Default -->
                  <div class="flex items-center justify-between">
                    <div>
                      <label class="text-sm font-medium text-gray-700">Gateway Predefinito</label>
                      <p class="text-sm text-gray-500">Imposta come opzione di pagamento predefinita</p>
                    </div>
                    <button
                      type="button"
                      @click="form.is_default = !form.is_default"
                      :class="[
                        form.is_default ? 'bg-purple-600' : 'bg-gray-200',
                        'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-purple-500 focus:ring-offset-2'
                      ]"
                    >
                      <span
                        :class="[
                          form.is_default ? 'translate-x-5' : 'translate-x-0',
                          'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                        ]"
                      />
                    </button>
                  </div>

                  <!-- Supported Currencies -->
                  <div>
                    <label for="supported_currencies" class="block text-sm font-medium text-gray-700">
                      Valute Supportate
                    </label>
                    <div class="mt-2 grid grid-cols-3 gap-2">
                      <label v-for="currency in availableCurrencies" :key="currency" class="flex items-center">
                        <input
                          type="checkbox"
                          :value="currency"
                          v-model="form.supported_currencies"
                          class="rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                        />
                        <span class="ml-2 text-sm text-gray-700">{{ currency }}</span>
                      </label>
                    </div>
                    <p class="mt-1 text-sm text-gray-500">Lascia vuoto per supportare tutte le valute</p>
                    <p v-if="errors.supported_currencies" class="mt-1 text-sm text-red-600">{{ errors.supported_currencies }}</p>
                  </div>

                  <!-- Sort Order -->
                  <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700">
                      Ordine di Visualizzazione
                    </label>
                    <input
                      id="sort_order"
                      v-model.number="form.sort_order"
                      type="number"
                      min="0"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="0"
                    />
                    <p class="mt-1 text-sm text-gray-500">Ordine di visualizzazione nel checkout (0 = primo)</p>
                    <p v-if="errors.sort_order" class="mt-1 text-sm text-red-600">{{ errors.sort_order }}</p>
                  </div>

                  <!-- Webhook URL -->
                  <div>
                    <label for="webhook_url" class="block text-sm font-medium text-gray-700">
                      URL Webhook
                    </label>
                    <input
                      id="webhook_url"
                      v-model="form.webhook_url"
                      type="url"
                      class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      placeholder="https://example.com/webhooks/payments"
                    />
                    <p class="mt-1 text-sm text-gray-500">URL per ricevere notifiche di pagamento</p>
                    <p v-if="errors.webhook_url" class="mt-1 text-sm text-red-600">{{ errors.webhook_url }}</p>
                  </div>
                </div>

                <!-- Provider Specific Configuration -->
                <div v-if="form.provider" class="space-y-4">
                  <h4 class="text-sm font-medium text-gray-900">
                    Configurazione {{ providers[form.provider] }}
                  </h4>
                  
                  <!-- Stripe Configuration -->
                  <div v-if="form.provider === 'stripe'" class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        {{ form.test_mode ? 'Publishable Key (Test)' : 'Publishable Key (Live)' }}
                      </label>
                      <input
                        v-model="form.config.publishable_key"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="form.test_mode ? 'pk_test_...' : 'pk_live_...'"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">
                        {{ form.test_mode ? 'Secret Key (Test)' : 'Secret Key (Live)' }}
                      </label>
                      <input
                        v-model="form.config.secret_key"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                        :placeholder="form.test_mode ? 'sk_test_...' : 'sk_live_...'"
                      />
                    </div>
                  </div>

                  <!-- PayPal Configuration -->
                  <div v-else-if="form.provider === 'paypal'" class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Client ID</label>
                      <input
                        v-model="form.config.client_id"
                        type="text"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">Client Secret</label>
                      <input
                        v-model="form.config.client_secret"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>

                  <!-- Generic Configuration for other providers -->
                  <div v-else class="space-y-4">
                    <div>
                      <label class="block text-sm font-medium text-gray-700">API Key</label>
                      <input
                        v-model="form.config.api_key"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                    <div>
                      <label class="block text-sm font-medium text-gray-700">API Secret</label>
                      <input
                        v-model="form.config.api_secret"
                        type="password"
                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                      />
                    </div>
                  </div>
                </div>

                <!-- Buttons -->
                <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
                  <button
                    type="button"
                    @click="$emit('close')"
                    class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
                  >
                    Annulla
                  </button>
                  <button
                    type="submit"
                    :disabled="processing"
                    class="px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md hover:bg-blue-700 disabled:opacity-50"
                  >
                    <span v-if="processing" class="flex items-center">
                      <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                      </svg>
                      Salvando...
                    </span>
                    <span v-else>{{ isEditing ? 'Aggiorna Gateway' : 'Crea Gateway' }}</span>
                  </button>
                </div>

              </form>
            </DialogPanel>
          </TransitionChild>
        </div>
      </div>
    </Dialog>
  </TransitionRoot>
</template>

<script setup>
import { computed, watch } from 'vue'
import { useForm } from '@inertiajs/vue3'
import { Dialog, DialogPanel, DialogTitle, TransitionChild, TransitionRoot } from '@headlessui/vue'
import { XMarkIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  show: Boolean,
  gateway: Object,
  providers: Object,
  errors: Object
})

const emit = defineEmits(['close', 'saved'])

const isEditing = computed(() => !!props.gateway?.id)

const availableCurrencies = ['EUR', 'USD', 'GBP', 'CHF', 'JPY', 'CAD', 'AUD']

// Form setup
const form = useForm({
  name: '',
  slug: '',
  description: '',
  provider: '',
  config: {},
  is_enabled: true,
  is_default: false,
  supported_currencies: [],
  webhook_url: '',
  test_mode: true,
  sort_order: 0
})

// Watch for gateway changes (editing)
watch(() => props.gateway, (gateway) => {
  if (gateway) {
    form.reset()
    Object.assign(form, {
      name: gateway.name || '',
      slug: gateway.slug || '',
      description: gateway.description || '',
      provider: gateway.provider || '',
      config: gateway.config || {},
      is_enabled: gateway.is_enabled ?? true,
      is_default: gateway.is_default ?? false,
      supported_currencies: gateway.supported_currencies || [],
      webhook_url: gateway.webhook_url || '',
      test_mode: gateway.test_mode ?? true,
      sort_order: gateway.sort_order || 0
    })
  } else {
    form.reset()
  }
}, { immediate: true })

// Auto-generate slug from name
watch(() => form.name, (name) => {
  if (name && !isEditing.value) {
    form.slug = name.toLowerCase()
      .replace(/[^a-z0-9\s-]/g, '')
      .replace(/\s+/g, '-')
      .replace(/-+/g, '-')
      .trim('-')
  }
})

// Save gateway
const saveGateway = () => {
  const url = isEditing.value 
    ? route('cp.settings.payment-gateways.update', props.gateway.id)
    : route('cp.settings.payment-gateways.store')
    
  const method = isEditing.value ? 'put' : 'post'
  
  form[method](url, {
    preserveScroll: true,
    onSuccess: () => {
      emit('saved')
    }
  })
}
</script>
