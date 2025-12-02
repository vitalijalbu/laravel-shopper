<script setup>
import { DialogTitle } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})
</script>

<template>
  <DialogTitle :class="cn('text-lg font-semibold text-gray-900', props.class)">
    <slot />
  </DialogTitle>
</template>
