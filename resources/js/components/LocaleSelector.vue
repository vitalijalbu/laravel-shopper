<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
      Select Locales
    </label>
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 max-h-64 overflow-y-auto p-4 border border-gray-300 rounded-md">
      <label
        v-for="locale in availableLocales"
        :key="locale.code"
        class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
      >
        <input
          type="checkbox"
          :value="locale.code"
          :checked="isSelected(locale.code)"
          @change="toggleLocale(locale.code)"
          class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500"
        />
        <span class="text-sm text-gray-700">{{ locale.name }}</span>
      </label>
    </div>
    <p class="mt-2 text-sm text-gray-500">{{ selectedCount }} locales selected</p>
  </div>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  availableLocales: {
    type: Array,
    required: true,
  },
})

const emit = defineEmits(['update:modelValue'])

const selectedCount = computed(() => props.modelValue.length)

const isSelected = (code) => {
  return props.modelValue.includes(code)
}

const toggleLocale = (code) => {
  const newValue = [...props.modelValue]
  const index = newValue.indexOf(code)
  
  if (index > -1) {
    newValue.splice(index, 1)
  } else {
    newValue.push(code)
  }
  
  emit('update:modelValue', newValue)
}
</script>

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
    <div v-if="isOpen" @click="isOpen = false" class="fixed inset-0 z-40"></div>
  </div>
</template>

<script>
import { ref, computed, watch, onMounted, onUnmounted } from "vue";
import { useTranslation } from "../stores/locale";
import {
  GlobeAltIcon,
  ChevronDownIcon,
  CheckIcon,
} from "@heroicons/vue/24/outline";

export default {
  name: "LocaleSelector",

  components: {
    GlobeAltIcon,
    ChevronDownIcon,
    CheckIcon,
  },

  setup() {
    const { locale, availableLocales, localeOptions, setLocale, t } =
      useTranslation();

    const isOpen = ref(false);
    const isChanging = ref(false);

    // Computed
    const currentLocaleLabel = computed(() => {
      const current = localeOptions.value.find(
        (option) => option.value === locale.value,
      );
      return current ? current.label : locale.value.toUpperCase();
    });

    // Methods
    const changeLocale = async (newLocale) => {
      if (newLocale === locale.value || isChanging.value) {
        isOpen.value = false;
        return;
      }

      isChanging.value = true;

      try {
        await setLocale(newLocale);
        isOpen.value = false;

        // Show success message
        if (window.showNotification) {
          window.showNotification(
            t("admin.messages.locale_updated"),
            "success",
          );
        }

        // Optional: Reload page to apply new translations everywhere
        if (shouldReloadOnLocaleChange()) {
          setTimeout(() => {
            window.location.reload();
          }, 500);
        }
      } catch (error) {
        console.error("Failed to change locale:", error);

        if (window.showNotification) {
          window.showNotification(t("admin.messages.error"), "error");
        }
      } finally {
        isChanging.value = false;
      }
    };

    const shouldReloadOnLocaleChange = () => {
      // Check if app config suggests reloading
      return window.ShopperConfig?.reloadOnLocaleChange !== false;
    };

    const handleKeydown = (event) => {
      if (event.key === "Escape") {
        isOpen.value = false;
      }
    };

    // Lifecycle
    onMounted(() => {
      document.addEventListener("keydown", handleKeydown);
    });

    onUnmounted(() => {
      document.removeEventListener("keydown", handleKeydown);
    });

    // Close dropdown when clicking outside
    watch(isOpen, (newValue) => {
      if (newValue) {
        // Focus management for accessibility
        const firstOption = document.querySelector("[data-locale-option]");
        if (firstOption) {
          firstOption.focus();
        }
      }
    });

    return {
      isOpen,
      isChanging,
      locale,
      availableLocales,
      localeOptions,
      currentLocaleLabel,
      changeLocale,
      t,
    };
  },
};
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
