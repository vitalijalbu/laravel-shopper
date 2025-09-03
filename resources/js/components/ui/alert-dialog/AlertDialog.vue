<template>
  <AlertDialogRoot :open="open" @update:open="$emit('update:open', $event)">
    <slot />
  </AlertDialogRoot>
</template>

<script setup lang="ts">
import { AlertDialogRoot } from 'reka-ui'

interface Props {
  open?: boolean
}

defineProps<Props>()
defineEmits<{
  'update:open': [value: boolean]
}>()
</script>
