<template>
  <div>
    <Head :title="page.title" />

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Impostazioni Generali</h1>
          <p class="mt-2 text-gray-600">Configura le informazioni di base del tuo negozio</p>
        </div>
        <Link 
          :href="route('cp.settings.index')"
          class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
        >
          <ArrowLeftIcon class="w-4 h-4 mr-2" />
          Torna alle Impostazioni
        </Link>
      </div>

      <!-- Settings Form -->
      <form @submit.prevent="saveSettings" class="space-y-8">
        
        <!-- Store Information -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Informazioni Negozio</h2>
            <p class="mt-1 text-sm text-gray-500">
              Informazioni di base che verranno mostrate ai clienti
            </p>
          </div>
          <div class="px-6 py-6 space-y-6">
            
            <!-- Store Name -->
            <div>
              <label for="store_name" class="block text-sm font-medium text-gray-700">
                Nome Negozio *
              </label>
              <input
                id="store_name"
                v-model="form.store_name"
                type="text"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Il mio E-commerce"
              />
              <p v-if="errors.store_name" class="mt-1 text-sm text-red-600">{{ errors.store_name }}</p>
            </div>

            <!-- Store Description -->
            <div>
              <label for="store_description" class="block text-sm font-medium text-gray-700">
                Descrizione Negozio
              </label>
              <textarea
                id="store_description"
                v-model="form.store_description"
                rows="3"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Descrivi il tuo negozio..."
              ></textarea>
              <p v-if="errors.store_description" class="mt-1 text-sm text-red-600">{{ errors.store_description }}</p>
            </div>

            <!-- Store Email -->
            <div>
              <label for="store_email" class="block text-sm font-medium text-gray-700">
                Email Negozio *
              </label>
              <input
                id="store_email"
                v-model="form.store_email"
                type="email"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="info@negozio.com"
              />
              <p v-if="errors.store_email" class="mt-1 text-sm text-red-600">{{ errors.store_email }}</p>
            </div>

            <!-- Store Phone -->
            <div>
              <label for="store_phone" class="block text-sm font-medium text-gray-700">
                Telefono Negozio
              </label>
              <input
                id="store_phone"
                v-model="form.store_phone"
                type="tel"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="+39 123 456 7890"
              />
              <p v-if="errors.store_phone" class="mt-1 text-sm text-red-600">{{ errors.store_phone }}</p>
            </div>

          </div>
        </div>

        <!-- Store Address -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Indirizzo Negozio</h2>
            <p class="mt-1 text-sm text-gray-500">
              Indirizzo fisico del tuo negozio o sede legale
            </p>
          </div>
          <div class="px-6 py-6 space-y-6">
            
            <!-- Address -->
            <div>
              <label for="store_address" class="block text-sm font-medium text-gray-700">
                Indirizzo
              </label>
              <input
                id="store_address"
                v-model="form.store_address"
                type="text"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Via Roma, 123"
              />
              <p v-if="errors.store_address" class="mt-1 text-sm text-red-600">{{ errors.store_address }}</p>
            </div>

            <!-- City, ZIP, Country -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
              <div>
                <label for="store_city" class="block text-sm font-medium text-gray-700">
                  Città
                </label>
                <input
                  id="store_city"
                  v-model="form.store_city"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="Milano"
                />
                <p v-if="errors.store_city" class="mt-1 text-sm text-red-600">{{ errors.store_city }}</p>
              </div>

              <div>
                <label for="store_zip" class="block text-sm font-medium text-gray-700">
                  CAP
                </label>
                <input
                  id="store_zip"
                  v-model="form.store_zip"
                  type="text"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                  placeholder="20100"
                />
                <p v-if="errors.store_zip" class="mt-1 text-sm text-red-600">{{ errors.store_zip }}</p>
              </div>

              <div>
                <label for="store_country" class="block text-sm font-medium text-gray-700">
                  Paese
                </label>
                <select
                  id="store_country"
                  v-model="form.store_country"
                  class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                >
                  <option value="IT">Italia</option>
                  <option value="FR">Francia</option>
                  <option value="DE">Germania</option>
                  <option value="ES">Spagna</option>
                  <option value="US">Stati Uniti</option>
                </select>
                <p v-if="errors.store_country" class="mt-1 text-sm text-red-600">{{ errors.store_country }}</p>
              </div>
            </div>

          </div>
        </div>

        <!-- Currency & Localization -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Valuta e Localizzazione</h2>
            <p class="mt-1 text-sm text-gray-500">
              Configurazioni regionali e valuta predefinita
            </p>
          </div>
          <div class="px-6 py-6 space-y-6">
            
            <!-- Default Currency -->
            <div>
              <label for="default_currency" class="block text-sm font-medium text-gray-700">
                Valuta Predefinita *
              </label>
              <select
                id="default_currency"
                v-model="form.default_currency"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="EUR">Euro (EUR)</option>
                <option value="USD">Dollaro USA (USD)</option>
                <option value="GBP">Sterlina (GBP)</option>
                <option value="CHF">Franco Svizzero (CHF)</option>
              </select>
              <p v-if="errors.default_currency" class="mt-1 text-sm text-red-600">{{ errors.default_currency }}</p>
            </div>

            <!-- Default Language -->
            <div>
              <label for="default_language" class="block text-sm font-medium text-gray-700">
                Lingua Predefinita *
              </label>
              <select
                id="default_language"
                v-model="form.default_language"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="it">Italiano</option>
                <option value="en">Inglese</option>
                <option value="fr">Francese</option>
                <option value="de">Tedesco</option>
                <option value="es">Spagnolo</option>
              </select>
              <p v-if="errors.default_language" class="mt-1 text-sm text-red-600">{{ errors.default_language }}</p>
            </div>

            <!-- Timezone -->
            <div>
              <label for="timezone" class="block text-sm font-medium text-gray-700">
                Fuso Orario *
              </label>
              <select
                id="timezone"
                v-model="form.timezone"
                required
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
              >
                <option value="Europe/Rome">Europa/Roma</option>
                <option value="Europe/Paris">Europa/Parigi</option>
                <option value="Europe/London">Europa/Londra</option>
                <option value="America/New_York">America/New_York</option>
                <option value="Asia/Tokyo">Asia/Tokyo</option>
              </select>
              <p v-if="errors.timezone" class="mt-1 text-sm text-red-600">{{ errors.timezone }}</p>
            </div>

          </div>
        </div>

        <!-- Store Status -->
        <div class="bg-white shadow rounded-lg">
          <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-medium text-gray-900">Stato Negozio</h2>
            <p class="mt-1 text-sm text-gray-500">
              Controlla la visibilità e l'accessibilità del negozio
            </p>
          </div>
          <div class="px-6 py-6 space-y-6">
            
            <!-- Store Status -->
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Negozio Online</h3>
                <p class="text-sm text-gray-500">Permetti ai clienti di effettuare acquisti</p>
              </div>
              <button
                type="button"
                @click="form.store_online = !form.store_online"
                :class="[
                  form.store_online ? 'bg-blue-600' : 'bg-gray-200',
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                ]"
              >
                <span
                  :class="[
                    form.store_online ? 'translate-x-5' : 'translate-x-0',
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                  ]"
                />
              </button>
            </div>

            <!-- Password Protection -->
            <div class="flex items-center justify-between">
              <div>
                <h3 class="text-sm font-medium text-gray-900">Protezione Password</h3>
                <p class="text-sm text-gray-500">Richiedi password per accedere al negozio</p>
              </div>
              <button
                type="button"
                @click="form.password_protection = !form.password_protection"
                :class="[
                  form.password_protection ? 'bg-blue-600' : 'bg-gray-200',
                  'relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2'
                ]"
              >
                <span
                  :class="[
                    form.password_protection ? 'translate-x-5' : 'translate-x-0',
                    'pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out'
                  ]"
                />
              </button>
            </div>

            <!-- Store Password -->
            <div v-if="form.password_protection">
              <label for="store_password" class="block text-sm font-medium text-gray-700">
                Password Negozio
              </label>
              <input
                id="store_password"
                v-model="form.store_password"
                type="password"
                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                placeholder="Inserisci password..."
              />
              <p v-if="errors.store_password" class="mt-1 text-sm text-red-600">{{ errors.store_password }}</p>
            </div>

          </div>
        </div>

        <!-- Save Button -->
        <div class="flex justify-end space-x-3">
          <Link
            :href="route('cp.settings.index')"
            class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50"
          >
            Annulla
          </Link>
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
            <span v-else>Salva Impostazioni</span>
          </button>
        </div>

      </form>
    </div>
  </div>
</template>

<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3'
import { ArrowLeftIcon } from '@heroicons/vue/24/outline'

const props = defineProps({
  page: Object,
  navigation: Object,
  settings: Object,
  errors: Object
})

// Form data
const form = useForm({
  // Store Information
  store_name: props.settings?.store_name || '',
  store_description: props.settings?.store_description || '',
  store_email: props.settings?.store_email || '',
  store_phone: props.settings?.store_phone || '',
  
  // Store Address
  store_address: props.settings?.store_address || '',
  store_city: props.settings?.store_city || '',
  store_zip: props.settings?.store_zip || '',
  store_country: props.settings?.store_country || 'IT',
  
  // Currency & Localization
  default_currency: props.settings?.default_currency || 'EUR',
  default_language: props.settings?.default_language || 'it',
  timezone: props.settings?.timezone || 'Europe/Rome',
  
  // Store Status
  store_online: props.settings?.store_online ?? true,
  password_protection: props.settings?.password_protection ?? false,
  store_password: props.settings?.store_password || ''
})

// Save settings
const saveSettings = () => {
  form.put(route('cp.settings.general.update'), {
    preserveScroll: true,
    onSuccess: () => {
      // Show success notification
      console.log('Settings saved successfully!')
    }
  })
}
</script>
