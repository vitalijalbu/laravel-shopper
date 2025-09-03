<template>
  <DropdownMenuPortal>
    <DropdownMenuContent
      :class="cn(
        'z-50 min-w-[8rem] overflow-hidden rounded-md border bg-white p-1 text-popover-foreground shadow-md',
        props.class
      )"
      :align="align"
      v-bind="$attrs"
    >
      <slot />
    </DropdownMenuContent>
  </DropdownMenuPortal>
</template>

<script setup lang="ts">
import { DropdownMenuPortal, DropdownMenuContent } from 'reka-ui'
import { cn } from '@/utils/cn'

interface Props {
  class?: string
  align?: 'start' | 'center' | 'end'
}

const props = withDefaults(defineProps<Props>(), {
  align: 'start',
})
</script>
