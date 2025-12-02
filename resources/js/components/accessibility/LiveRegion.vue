<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  message: {
    type: String,
    default: '',
  },
  priority: {
    type: String,
    default: 'polite', // 'polite' | 'assertive'
    validator: (value) => ['polite', 'assertive'].includes(value),
  },
  atomic: {
    type: Boolean,
    default: true,
  },
})

const displayMessage = ref('')

watch(() => props.message, (newMessage) => {
  if (newMessage) {
    // Clear first to ensure screen reader announces
    displayMessage.value = ''
    setTimeout(() => {
      displayMessage.value = newMessage
    }, 100)
  }
})
</script>

<template>
  <div
    role="status"
    :aria-live="priority"
    :aria-atomic="atomic"
    class="sr-only"
  >
    {{ displayMessage }}
  </div>
</template>
