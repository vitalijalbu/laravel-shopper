<template>
  <AlertDialogCancel
    :class="cn('mt-2 sm:mt-0', props.class)"
    v-bind="$attrs"
  >
    <slot />
  </AlertDialogCancel>
</template>

<script setup lang="ts">
import { AlertDialogCancel } from 'reka-ui'
import { cn } from '@/utils/cn'

interface Props {
  class?: string
}

const props = defineProps<Props>()
</script>
