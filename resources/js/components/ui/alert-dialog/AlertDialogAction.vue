<template>
  <AlertDialogAction
    :class="cn(props.class)"
    v-bind="$attrs"
  >
    <slot />
  </AlertDialogAction>
</template>

<script setup lang="ts">
import { AlertDialogAction } from 'reka-ui'
import { cn } from '@/utils/cn'

interface Props {
  class?: string
}

const props = defineProps<Props>()
</script>
