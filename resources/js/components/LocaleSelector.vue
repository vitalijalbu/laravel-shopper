<template>
  <div class="relative">
    <!-- Language Selector Button -->
    <button
      @click="isOpen = !isOpen"
      type="button"
      class="flex items-center space-x-2 px-3 py-2 text-sm font-medium text-gray-700 hover:text-gray-900 hover:bg-gray-50 rounded-md transition-colors"
      :class="{ 'bg-gray-50': isOpen }"
    >
      <globe-alt-icon class="w-4 h-4" />
      <span>{{ currentLocaleLabel }}</span>
      <chevron-down-icon 
        class="w-4 h-4 transition-transform" 
        :class="{ 'rotate-180': isOpen }"
      />
    </button>

    <!-- Dropdown Menu -->
    <transition
      enter-active-class="transition ease-out duration-100"
      enter-from-class="transform opacity-0 scale-95"
      enter-to-class="transform opacity-100 scale-100"
      leave-active-class="transition ease-in duration-75"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform opacity-0 scale-95"
    >
      <div
        v-show="isOpen"
        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg ring-1 ring-black ring-opacity-5 z-50"
      >
        <div class="py-1">
          <button
            v-for="option in localeOptions"
            :key="option.value"
            @click="changeLocale(option.value)"
            type="button"
            class="flex items-center w-full px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 hover:text-gray-900 transition-colors"
            :class="{ 
              'bg-blue-50 text-blue-600': option.value === locale,
              'font-medium': option.value === locale 
            }"
          >
            <span class="flex-1 text-left">{{ option.label }}</span>
            <check-icon 
              v-if="option.value === locale"
              class="w-4 h-4 text-blue-600"
            />
          </button>
        </div>
      </div>
    </transition>

    <!-- Backdrop -->
    <div
      v-if="isOpen"
      @click="isOpen = false"
      class="fixed inset-0 z-40"
    ></div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from 'vue'
import { useTranslation } from '../stores/locale'
import { 
  GlobeAltIcon, 
  ChevronDownIcon, 
  CheckIcon 
} from '@heroicons/vue/24/outline'

export default {
  name: 'LocaleSelector',
  
  components: {
    GlobeAltIcon,
    ChevronDownIcon,
    CheckIcon
  },

  setup() {
    const { 
      locale, 
      availableLocales, 
      localeOptions, 
      setLocale, 
      t 
    } = useTranslation()
    
    const isOpen = ref(false)
    const isChanging = ref(false)

    // Computed
    const currentLocaleLabel = computed(() => {
      const current = localeOptions.value.find(option => option.value === locale.value)
      return current ? current.label : locale.value.toUpperCase()
    })

    // Methods
    const changeLocale = async (newLocale) => {
      if (newLocale === locale.value || isChanging.value) {
        isOpen.value = false
        return
      }

      isChanging.value = true
      
      try {
        await setLocale(newLocale)
        isOpen.value = false
        
        // Show success message
        if (window.showNotification) {
          window.showNotification(
            t('admin.messages.locale_updated'), 
            'success'
          )
        }
        
        // Optional: Reload page to apply new translations everywhere
        if (shouldReloadOnLocaleChange()) {
          setTimeout(() => {
            window.location.reload()
          }, 500)
        }
        
      } catch (error) {
        console.error('Failed to change locale:', error)
        
        if (window.showNotification) {
          window.showNotification(
            t('admin.messages.error'), 
            'error'
          )
        }
      } finally {
        isChanging.value = false
      }
    }

    const shouldReloadOnLocaleChange = () => {
      // Check if app config suggests reloading
      return window.ShopperConfig?.reloadOnLocaleChange !== false
    }

    const handleKeydown = (event) => {
      if (event.key === 'Escape') {
        isOpen.value = false
      }
    }

    // Lifecycle
    onMounted(() => {
      document.addEventListener('keydown', handleKeydown)
    })

    onUnmounted(() => {
      document.removeEventListener('keydown', handleKeydown)
    })

    // Close dropdown when clicking outside
    watch(isOpen, (newValue) => {
      if (newValue) {
        // Focus management for accessibility
        const firstOption = document.querySelector('[data-locale-option]')
        if (firstOption) {
          firstOption.focus()
        }
      }
    })

    return {
      isOpen,
      isChanging,
      locale,
      availableLocales,
      localeOptions,
      currentLocaleLabel,
      changeLocale,
      t
    }
  }
}
</script>

<style scoped>
/* Custom styles if needed */
.locale-selector-enter-active,
.locale-selector-leave-active {
  transition: all 0.2s ease;
}

.locale-selector-enter-from,
.locale-selector-leave-to {
  opacity: 0;
  transform: translateY(-10px);
}
</style>
