<template>
  <DialogRoot :open="open" @update:open="$emit('update:open', $event)">
    <slot />
  </DialogRoot>
</template>

<script setup lang="ts">
import { DialogRoot } from 'reka-ui'

interface Props {
  open?: boolean
}

defineProps<Props>()
defineEmits<{
  'update:open': [value: boolean]
}>()
</script>
