<template>
  <Teleport to="body">
    <Transition
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <div
        v-if="show"
        class="fixed inset-0 z-50 overflow-y-auto"
        aria-labelledby="modal-title"
        role="dialog"
        aria-modal="true"
      >
        <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
          <!-- Background overlay -->
          <div
            class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity"
            @click="$emit('cancel')"
          ></div>

          <!-- Modal panel -->
          <div
            class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle transition-all transform bg-white shadow-xl rounded-lg"
          >
            <!-- Header -->
            <div class="flex items-center justify-between mb-4">
              <h3 class="text-lg font-medium text-gray-900" id="modal-title">
                {{ title }}
              </h3>
              <button
                @click="$emit('cancel')"
                class="text-gray-400 hover:text-gray-600 focus:outline-none"
              >
                <XMarkIcon class="h-6 w-6" />
              </button>
            </div>

            <!-- Content -->
            <div class="mb-6">
              <p class="text-sm text-gray-500">
                {{ message }}
              </p>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-3">
              <button
                @click="$emit('cancel')"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
              >
                {{ cancelText }}
              </button>
              <button
                @click="$emit('confirm')"
                :class="[
                  confirmClass,
                  'px-4 py-2 text-sm font-medium rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500'
                ]"
              >
                {{ confirmText }}
              </button>
            </div>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { XMarkIcon } from '@heroicons/vue/24/outline'

defineProps({
  show: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: 'Conferma azione'
  },
  message: {
    type: String,
    default: 'Sei sicuro di voler continuare?'
  },
  confirmText: {
    type: String,
    default: 'Conferma'
  },
  cancelText: {
    type: String,
    default: 'Annulla'
  },
  confirmClass: {
    type: String,
    default: 'bg-red-600 hover:bg-red-700 text-white'
  }
})

defineEmits(['confirm', 'cancel'])
</script>
