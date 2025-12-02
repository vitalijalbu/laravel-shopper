<script setup>
import { DialogDescription } from 'reka-ui'
import { cn } from '@/lib/utils'

const props = defineProps({
  class: String
})
</script>

<template>
  <DialogDescription :class="cn('text-sm text-gray-500', props.class)">
    <slot />
  </DialogDescription>
</template>
