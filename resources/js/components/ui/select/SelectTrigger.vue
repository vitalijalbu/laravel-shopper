<template>
  <SelectTrigger
    :class="cn(
      'flex h-10 w-full items-center justify-between rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
      $attrs.class
    )"
    v-bind="$attrs"
  >
    <slot />
    <SelectIcon />
  </SelectTrigger>
</template>

<script setup>
import { SelectTrigger, SelectIcon } from 'reka-ui'
import { cn } from '@/lib/utils'
</script>
