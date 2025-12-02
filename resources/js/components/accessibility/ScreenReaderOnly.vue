<script setup>
defineProps({
  as: {
    type: String,
    default: 'span',
  },
})
</script>

<template>
  <component :is="as" class="sr-only">
    <slot />
  </component>
</template>

<style>
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border-width: 0;
}
</style>
