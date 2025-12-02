<script setup>
import { Checkbox } from '@/components/ui/checkbox'
import { Label } from '@/components/ui/label'
import FormField from './FormField.vue'

const props = defineProps({
  name: {
    type: String,
    required: true
  },
  label: String,
  description: String,
  required: Boolean,
  disabled: Boolean
})
</script>

<template>
  <FormField
    :name="name"
    :description="description"
    :required="required"
  >
    <template #default="{ fieldId, modelValue, update, hasError }">
      <div class="flex items-start space-x-3">
        <Checkbox
          :id="fieldId"
          :checked="!!modelValue"
          @update:checked="update"
          :disabled="disabled"
          :class="{ 'border-red-500': hasError }"
        />
        <div class="grid gap-1.5 leading-none">
          <Label
            v-if="label"
            :for="fieldId"
            class="text-sm font-medium leading-none cursor-pointer"
            :class="{ 'text-red-600': hasError }"
          >
            {{ label }}
            <span v-if="required" class="text-red-500 ml-0.5">*</span>
          </Label>
        </div>
      </div>
    </template>
  </FormField>
</template>
