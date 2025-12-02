<script setup>
import { computed, inject } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  label: String,
  description: String,
  required: Boolean,
  class: String
})

const form = inject('form', null)
const formErrors = inject('formErrors', {})

const error = computed(() => formErrors.value?.[props.name])
const hasError = computed(() => !!error.value)
const fieldId = computed(() => `field-${props.name}`)

const updateValue = (value) => {
  if (form?.value) {
    form.value[props.name] = value
  }
}
</script>

<template>
  <div :class="cn('space-y-2', props.class)">
    <label
      v-if="label"
      :for="fieldId"
      class="block text-sm font-medium text-gray-900"
      :class="{ 'text-red-600': hasError }"
    >
      {{ label }}
      <span v-if="required" class="text-red-500 ml-0.5">*</span>
    </label>

    <slot
      :error="error"
      :has-error="hasError"
      :field-id="fieldId"
      :model-value="form?.value?.[props.name]"
      :update="updateValue"
    />

    <p v-if="description && !hasError" class="text-sm text-gray-500">
      {{ description }}
    </p>

    <p v-if="hasError" class="text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>
