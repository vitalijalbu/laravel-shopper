<template>
  <div class="space-y-1.5">
    <label 
      v-if="label"
      :for="id"
      class="text-sm font-medium leading-none peer-disabled:cursor-not-allowed peer-disabled:opacity-70"
      :class="labelClass"
    >
      {{ label }}
      <span v-if="required" class="text-destructive">*</span>
    </label>
    <input
      :id="id"
      v-bind="$attrs"
      :value="modelValue"
      @input="$emit('update:modelValue', $event.target.value)"
      :class="inputClass"
    />
    <p v-if="error" class="text-sm text-destructive">
      {{ error }}
    </p>
    <p v-else-if="description" class="text-sm text-muted-foreground">
      {{ description }}
    </p>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { cn } from '@/lib/utils'

const props = defineProps({
  id: String,
  label: String,
  modelValue: [String, Number],
  error: String,
  description: String,
  required: Boolean,
  variant: {
    type: String,
    default: 'default'
  }
})

const emits = defineEmits(['update:modelValue'])

const inputClass = computed(() => {
  return cn(
    'flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
    props.error && 'border-destructive focus-visible:ring-destructive'
  )
})

const labelClass = computed(() => {
  return cn(
    props.error && 'text-destructive'
  )
})
</script>
