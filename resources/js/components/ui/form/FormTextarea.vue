<script setup>
import FormField from './FormField.vue'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  label: String,
  description: String,
  required: Boolean,
  placeholder: String,
  disabled: Boolean,
  readonly: Boolean,
  rows: {
    type: Number,
    default: 3
  }
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
      <textarea
        :id="fieldId"
        :placeholder="placeholder"
        :disabled="disabled"
        :readonly="readonly"
        :rows="rows"
        :value="modelValue"
        @input="update($event.target.value)"
        class="flex min-h-20 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm placeholder:text-gray-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:cursor-not-allowed disabled:opacity-50 resize-y"
        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
      />
    </template>
  </FormField>
</template>
