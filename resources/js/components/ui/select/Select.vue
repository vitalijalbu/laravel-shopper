<template>
  <SelectRoot :model-value="modelValue" @update:model-value="$emit('update:modelValue', $event)" v-bind="$attrs">
    <slot />
  </SelectRoot>
</template>

<script setup>
import { SelectRoot } from 'reka-ui'

defineProps({
  modelValue: {
    type: [String, Number],
    default: undefined
  }
})

defineEmits(['update:modelValue'])
</script>
