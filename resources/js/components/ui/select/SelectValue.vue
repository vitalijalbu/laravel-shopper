<template>
  <SelectValue 
    :class="cn('text-sm', $attrs.class)"
    v-bind="$attrs"
  >
    <slot />
  </SelectValue>
</template>

<script setup>
import { SelectValue } from 'reka-ui'
import { cn } from '@/lib/utils'
</script>
