<script setup>
defineProps({
  href: {
    type: String,
    default: '#main-content',
  },
  text: {
    type: String,
    default: 'Skip to main content',
  },
})

const handleSkip = (e) => {
  e.preventDefault()
  const target = document.querySelector(e.target.getAttribute('href'))
  if (target) {
    target.setAttribute('tabindex', '-1')
    target.focus()
    target.scrollIntoView()
    // Remove tabindex after focus
    setTimeout(() => {
      target.removeAttribute('tabindex')
    }, 100)
  }
}
</script>

<template>
  <a
    :href="href"
    class="skip-link"
    @click="handleSkip"
  >
    {{ text }}
  </a>
</template>

<style scoped>
.skip-link {
  position: absolute;
  top: -40px;
  left: 0;
  z-index: 100;
  padding: 8px 16px;
  background: #4f46e5;
  color: white;
  text-decoration: none;
  border-radius: 0 0 4px 0;
}

.skip-link:focus {
  top: 0;
}
</style>
