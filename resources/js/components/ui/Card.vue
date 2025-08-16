<template>
  <div :class="cn('rounded-xl border bg-card text-card-foreground shadow', $attrs.class)">
    <slot />
  </div>
</template>

<script setup>
import { cn } from '../../lib/utils'

defineOptions({
  inheritAttrs: false,
})
</script>
