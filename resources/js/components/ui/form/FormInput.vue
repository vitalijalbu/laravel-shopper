<script setup>
import { Input } from '@/components/ui/input'
import FormField from './FormField.vue'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  label: String,
  description: String,
  required: Boolean,
  type: {
    type: String,
    default: 'text'
  },
  placeholder: String,
  disabled: Boolean,
  readonly: Boolean,
  autocomplete: String
})
</script>

<template>
  <FormField
    :name="name"
    :label="label"
    :description="description"
    :required="required"
  >
    <template #default="{ fieldId, modelValue, update, hasError }">
      <Input
        :id="fieldId"
        :type="type"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :autocomplete="autocomplete"
        :model-value="modelValue"
        @update:model-value="update"
        :class="{ 'border-red-500 focus-visible:ring-red-500': hasError }"
      />
    </template>
  </FormField>
</template>
