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
  options: {
    type: Array,
    required: true
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
      <select
        :id="fieldId"
        :disabled="disabled"
        :value="modelValue"
        @change="update($event.target.value)"
        class="flex h-10 w-full items-center justify-between rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent disabled:cursor-not-allowed disabled:opacity-50"
        :class="{ 'border-red-500 focus:ring-red-500': hasError }"
      >
        <option v-if="placeholder" value="">{{ placeholder }}</option>
        <option
          v-for="option in options"
          :key="option.value"
          :value="option.value"
        >
          {{ option.label }}
        </option>
      </select>
    </template>
  </FormField>
</template>
