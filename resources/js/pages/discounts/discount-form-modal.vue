<template>
  <ShopperModal
    :show="show"
    @close="$emit('update:show', false)"
    :max-width="'4xl'"
  >
    <template #header>
      <h3 class="text-lg font-medium text-gray-900">
        {{ isEditing ? $t('discount.actions.edit') : $t('discount.actions.create') }}
      </h3>
    </template>

    <form @submit.prevent="saveDiscount" class="space-y-6">
      <!-- Basic Information -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Name -->
        <div class="md:col-span-2">
          <ShopperLabel for="name" required>
            {{ $t('discount.labels.name') }}
          </ShopperLabel>
          <ShopperInput
            id="name"
            v-model="form.name"
            :placeholder="$t('discount.placeholders.name')"
            :error="errors.name"
            required
          />
        </div>

        <!-- Description -->
        <div class="md:col-span-2">
          <ShopperLabel for="description">
            {{ $t('discount.labels.description') }}
          </ShopperLabel>
          <ShopperTextarea
            id="description"
            v-model="form.description"
            :placeholder="$t('discount.placeholders.description')"
            :error="errors.description"
            rows="3"
          />
        </div>

        <!-- Code -->
        <div>
          <ShopperLabel for="code">
            {{ $t('discount.labels.code') }}
          </ShopperLabel>
          <ShopperInput
            id="code"
            v-model="form.code"
            :placeholder="$t('discount.placeholders.code')"
            :error="errors.code"
          />
          <div class="mt-1 text-xs text-gray-500">
            {{ $t('discount.help.code') }}
          </div>
        </div>

        <!-- Type -->
        <div>
          <ShopperLabel for="type" required>
            {{ $t('discount.labels.type') }}
          </ShopperLabel>
          <ShopperSelect
            id="type"
            v-model="form.type"
            :error="errors.type"
            required
          >
            <option value="">Seleziona tipo</option>
            <option value="percentage">{{ $t('discount.types.percentage') }}</option>
            <option value="fixed_amount">{{ $t('discount.types.fixed_amount') }}</option>
            <option value="free_shipping">{{ $t('discount.types.free_shipping') }}</option>
          </ShopperSelect>
        </div>
      </div>

      <!-- Discount Value -->
      <div v-if="form.type && form.type !== 'free_shipping'" class="space-y-4">
        <h4 class="text-sm font-medium text-gray-900">Valore dello sconto</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Value -->
          <div>
            <ShopperLabel for="value" required>
              {{ $t('discount.labels.value') }}
            </ShopperLabel>
            <ShopperInput
              id="value"
              v-model="form.value"
              type="number"
              :min="0"
              :max="form.type === 'percentage' ? 100 : undefined"
              :step="form.type === 'percentage' ? 1 : 0.01"
              :placeholder="form.type === 'percentage' ? $t('discount.placeholders.value_percentage') : $t('discount.placeholders.value_fixed')"
              :error="errors.value"
              required
            >
              <template #trailing>
                <span class="text-gray-500 text-sm">
                  {{ form.type === 'percentage' ? '%' : '€' }}
                </span>
              </template>
            </ShopperInput>
            <div class="mt-1 text-xs text-gray-500">
              {{ form.type === 'percentage' ? $t('discount.help.value_percentage') : $t('discount.help.value_fixed') }}
            </div>
          </div>

          <!-- Minimum Order Amount -->
          <div>
            <ShopperLabel for="minimum_order_amount">
              {{ $t('discount.labels.minimum_order_amount') }}
            </ShopperLabel>
            <ShopperInput
              id="minimum_order_amount"
              v-model="form.minimum_order_amount"
              type="number"
              min="0"
              step="0.01"
              :placeholder="$t('discount.placeholders.minimum_order')"
              :error="errors.minimum_order_amount"
            >
              <template #trailing>
                <span class="text-gray-500 text-sm">€</span>
              </template>
            </ShopperInput>
            <div class="mt-1 text-xs text-gray-500">
              {{ $t('discount.help.minimum_order') }}
            </div>
          </div>

          <!-- Maximum Discount Amount -->
          <div v-if="form.type === 'percentage'">
            <ShopperLabel for="maximum_discount_amount">
              {{ $t('discount.labels.maximum_discount_amount') }}
            </ShopperLabel>
            <ShopperInput
              id="maximum_discount_amount"
              v-model="form.maximum_discount_amount"
              type="number"
              min="0"
              step="0.01"
              :placeholder="$t('discount.placeholders.maximum_discount')"
              :error="errors.maximum_discount_amount"
            >
              <template #trailing>
                <span class="text-gray-500 text-sm">€</span>
              </template>
            </ShopperInput>
            <div class="mt-1 text-xs text-gray-500">
              {{ $t('discount.help.maximum_discount') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Usage Limits -->
      <div class="space-y-4">
        <h4 class="text-sm font-medium text-gray-900">Limiti di utilizzo</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Usage Limit -->
          <div>
            <ShopperLabel for="usage_limit">
              {{ $t('discount.labels.usage_limit') }}
            </ShopperLabel>
            <ShopperInput
              id="usage_limit"
              v-model="form.usage_limit"
              type="number"
              min="1"
              :placeholder="$t('discount.placeholders.usage_limit')"
              :error="errors.usage_limit"
            />
            <div class="mt-1 text-xs text-gray-500">
              {{ $t('discount.help.usage_limit') }}
            </div>
          </div>

          <!-- Usage Limit Per Customer -->
          <div>
            <ShopperLabel for="usage_limit_per_customer">
              {{ $t('discount.labels.usage_limit_per_customer') }}
            </ShopperLabel>
            <ShopperInput
              id="usage_limit_per_customer"
              v-model="form.usage_limit_per_customer"
              type="number"
              min="1"
              :placeholder="$t('discount.placeholders.usage_limit_per_customer')"
              :error="errors.usage_limit_per_customer"
            />
            <div class="mt-1 text-xs text-gray-500">
              {{ $t('discount.help.usage_limit_per_customer') }}
            </div>
          </div>
        </div>
      </div>

      <!-- Schedule -->
      <div class="space-y-4">
        <h4 class="text-sm font-medium text-gray-900">Programmazione</h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Start Date -->
          <div>
            <ShopperLabel for="starts_at">
              {{ $t('discount.labels.starts_at') }}
            </ShopperLabel>
            <ShopperInput
              id="starts_at"
              v-model="form.starts_at"
              type="datetime-local"
              :error="errors.starts_at"
            />
          </div>

          <!-- End Date -->
          <div>
            <ShopperLabel for="expires_at">
              {{ $t('discount.labels.expires_at') }}
            </ShopperLabel>
            <ShopperInput
              id="expires_at"
              v-model="form.expires_at"
              type="datetime-local"
              :error="errors.expires_at"
            />
          </div>
        </div>
      </div>

      <!-- Eligibility -->
      <div class="space-y-4">
        <h4 class="text-sm font-medium text-gray-900">Criteri di ammissibilità</h4>
        
        <!-- Products -->
        <div>
          <ShopperLabel for="eligible_products">
            {{ $t('discount.labels.eligible_products') }}
          </ShopperLabel>
          <ShopperMultiSelect
            id="eligible_products"
            v-model="form.eligible_products"
            :options="products"
            :loading="loadingProducts"
            placeholder="Seleziona prodotti..."
            :error="errors.eligible_products"
            searchable
            @search="searchProducts"
          />
          <div class="mt-1 text-xs text-gray-500">
            {{ $t('discount.help.eligible_products') }}
          </div>
        </div>

        <!-- Categories -->
        <div>
          <ShopperLabel for="eligible_categories">
            {{ $t('discount.labels.eligible_categories') }}
          </ShopperLabel>
          <ShopperMultiSelect
            id="eligible_categories"
            v-model="form.eligible_categories"
            :options="categories"
            :loading="loadingCategories"
            placeholder="Seleziona categorie..."
            :error="errors.eligible_categories"
            searchable
            @search="searchCategories"
          />
          <div class="mt-1 text-xs text-gray-500">
            {{ $t('discount.help.eligible_categories') }}
          </div>
        </div>

        <!-- Customers -->
        <div>
          <ShopperLabel for="eligible_customers">
            {{ $t('discount.labels.eligible_customers') }}
          </ShopperLabel>
          <ShopperMultiSelect
            id="eligible_customers"
            v-model="form.eligible_customers"
            :options="customers"
            :loading="loadingCustomers"
            placeholder="Seleziona clienti..."
            :error="errors.eligible_customers"
            searchable
            @search="searchCustomers"
          />
          <div class="mt-1 text-xs text-gray-500">
            {{ $t('discount.help.eligible_customers') }}
          </div>
        </div>
      </div>

      <!-- Status -->
      <div>
        <ShopperCheckbox
          id="is_enabled"
          v-model="form.is_enabled"
          :label="$t('discount.labels.is_enabled')"
        />
      </div>
    </form>

    <template #footer>
      <div class="flex justify-end space-x-3">
        <ShopperButton
          variant="outline"
          @click="$emit('update:show', false)"
        >
          Annulla
        </ShopperButton>
        <ShopperButton
          @click="saveDiscount"
          :loading="saving"
          class="btn-primary"
        >
          {{ isEditing ? 'Aggiorna' : 'Crea' }}
        </ShopperButton>
      </div>
    </template>
  </ShopperModal>
</template>

<script setup>
import { ref, reactive, computed, watch, onMounted } from 'vue'
import { useTranslations } from '@/composables/useTranslations'
import { useApi } from '@/composables/useApi'
import { useNotifications } from '@/composables/useNotifications'
import { debounce } from '@/lib/utils'

// Components
import ShopperModal from '@/components/ui/ShopperModal.vue'
import ShopperLabel from '@/components/ui/ShopperLabel.vue'
import ShopperInput from '@/components/ui/ShopperInput.vue'
import ShopperTextarea from '@/components/ui/ShopperTextarea.vue'
import ShopperSelect from '@/components/ui/ShopperSelect.vue'
import ShopperMultiSelect from '@/components/ui/ShopperMultiSelect.vue'
import ShopperCheckbox from '@/components/ui/ShopperCheckbox.vue'
import ShopperButton from '@/components/ui/ShopperButton.vue'

// Props & Emits
const props = defineProps({
  show: Boolean,
  discount: Object,
})

const emit = defineEmits(['update:show', 'saved'])

// Composables
const { $t } = useTranslations()
const { api } = useApi()
const { notify } = useNotifications()

// State
const saving = ref(false)
const loadingProducts = ref(false)
const loadingCategories = ref(false)
const loadingCustomers = ref(false)

const products = ref([])
const categories = ref([])
const customers = ref([])

const form = reactive({
  name: '',
  description: '',
  code: '',
  type: '',
  value: '',
  minimum_order_amount: '',
  maximum_discount_amount: '',
  usage_limit: '',
  usage_limit_per_customer: '',
  eligible_products: [],
  eligible_categories: [],
  eligible_customers: [],
  is_enabled: true,
  starts_at: '',
  expires_at: '',
})

const errors = reactive({})

// Computed
const isEditing = computed(() => !!props.discount?.id)

// Methods
const resetForm = () => {
  Object.assign(form, {
    name: '',
    description: '',
    code: '',
    type: '',
    value: '',
    minimum_order_amount: '',
    maximum_discount_amount: '',
    usage_limit: '',
    usage_limit_per_customer: '',
    eligible_products: [],
    eligible_categories: [],
    eligible_customers: [],
    is_enabled: true,
    starts_at: '',
    expires_at: '',
  })
  Object.keys(errors).forEach(key => delete errors[key])
}

const populateForm = (discount) => {
  if (!discount) return
  
  Object.assign(form, {
    name: discount.name || '',
    description: discount.description || '',
    code: discount.code || '',
    type: discount.type || '',
    value: discount.value || '',
    minimum_order_amount: discount.minimum_order_amount || '',
    maximum_discount_amount: discount.maximum_discount_amount || '',
    usage_limit: discount.usage_limit || '',
    usage_limit_per_customer: discount.usage_limit_per_customer || '',
    eligible_products: discount.eligible_products || [],
    eligible_categories: discount.eligible_categories || [],
    eligible_customers: discount.eligible_customers || [],
    is_enabled: discount.is_enabled ?? true,
    starts_at: discount.starts_at ? formatDateTimeLocal(discount.starts_at) : '',
    expires_at: discount.expires_at ? formatDateTimeLocal(discount.expires_at) : '',
  })
}

const saveDiscount = async () => {
  saving.value = true
  Object.keys(errors).forEach(key => delete errors[key])

  try {
    const payload = { ...form }
    
    // Clean empty values
    Object.keys(payload).forEach(key => {
      if (payload[key] === '' || payload[key] === null) {
        payload[key] = null
      }
    })

    let response
    if (isEditing.value) {
      response = await api.put(`/admin/discounts/${props.discount.id}`, payload)
    } else {
      response = await api.post('/admin/discounts', payload)
    }

    notify.success(
      isEditing.value 
        ? $t('discount.messages.updated_successfully')
        : $t('discount.messages.created_successfully')
    )
    
    emit('saved', response.data)
  } catch (error) {
    if (error.response?.data?.errors) {
      Object.assign(errors, error.response.data.errors)
    } else {
      notify.error('Errore nel salvataggio dello sconto')
    }
  } finally {
    saving.value = false
  }
}

// Search methods
const searchProducts = debounce(async (query) => {
  if (!query) return
  
  loadingProducts.value = true
  try {
    const response = await api.get('/admin/products', {
      params: { search: query, per_page: 20 }
    })
    products.value = response.data.data.map(product => ({
      value: product.id,
      label: product.name,
    }))
  } catch (error) {
    console.error('Error searching products:', error)
  } finally {
    loadingProducts.value = false
  }
}, 300)

const searchCategories = debounce(async (query) => {
  if (!query) return
  
  loadingCategories.value = true
  try {
    const response = await api.get('/admin/categories', {
      params: { search: query, per_page: 20 }
    })
    categories.value = response.data.data.map(category => ({
      value: category.id,
      label: category.name,
    }))
  } catch (error) {
    console.error('Error searching categories:', error)
  } finally {
    loadingCategories.value = false
  }
}, 300)

const searchCustomers = debounce(async (query) => {
  if (!query) return
  
  loadingCustomers.value = true
  try {
    const response = await api.get('/admin/customers', {
      params: { search: query, per_page: 20 }
    })
    customers.value = response.data.data.map(customer => ({
      value: customer.id,
      label: `${customer.first_name} ${customer.last_name} (${customer.email})`,
    }))
  } catch (error) {
    console.error('Error searching customers:', error)
  } finally {
    loadingCustomers.value = false
  }
}, 300)

// Utilities
const formatDateTimeLocal = (dateString) => {
  const date = new Date(dateString)
  const offset = date.getTimezoneOffset()
  const adjustedDate = new Date(date.getTime() - (offset * 60 * 1000))
  return adjustedDate.toISOString().slice(0, 16)
}

// Watchers
watch(() => props.show, (show) => {
  if (show) {
    if (props.discount) {
      populateForm(props.discount)
    } else {
      resetForm()
    }
  }
})

watch(() => props.discount, (discount) => {
  if (props.show && discount) {
    populateForm(discount)
  }
})

// Lifecycle
onMounted(() => {
  // Load initial data if needed
})
</script>
