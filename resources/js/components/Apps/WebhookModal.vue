<template>
  <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
    <div class="flex min-h-screen items-center justify-center p-4">
      <div
        class="fixed inset-0 bg-black bg-opacity-25"
        @click="$emit('close')"
      ></div>

      <div
        class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
      >
        <div class="p-6">
          <div class="flex items-center justify-between mb-6">
            <h2 class="text-2xl font-bold text-gray-900">
              Webhook Configuration
            </h2>
            <button
              @click="$emit('close')"
              class="text-gray-400 hover:text-gray-600"
            >
              <svg
                class="w-6 h-6"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M6 18L18 6M6 6l12 12"
                />
              </svg>
            </button>
          </div>

          <form @submit.prevent="saveWebhook" class="space-y-6">
            <div>
              <label
                for="webhook-url"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Webhook URL
              </label>
              <input
                id="webhook-url"
                v-model="webhookData.url"
                type="url"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="https://your-app.com/webhooks/shopper"
                required
              />
            </div>

            <div>
              <label
                for="webhook-secret"
                class="block text-sm font-medium text-gray-700 mb-2"
              >
                Secret Key (Optional)
              </label>
              <input
                id="webhook-secret"
                v-model="webhookData.secret"
                type="password"
                class="block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                placeholder="Optional secret for webhook verification"
              />
            </div>

            <div>
              <label class="block text-sm font-medium text-gray-700 mb-3">
                Events to Subscribe
              </label>
              <div class="space-y-2">
                <div
                  v-for="event in availableEvents"
                  :key="event.value"
                  class="flex items-center"
                >
                  <input
                    :id="`event-${event.value}`"
                    v-model="webhookData.events"
                    :value="event.value"
                    type="checkbox"
                    class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
                  />
                  <label
                    :for="`event-${event.value}`"
                    class="ml-2 block text-sm text-gray-900"
                  >
                    {{ event.label }}
                  </label>
                </div>
              </div>
            </div>

            <div class="flex items-center">
              <input
                id="webhook-active"
                v-model="webhookData.active"
                type="checkbox"
                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
              />
              <label
                for="webhook-active"
                class="ml-2 block text-sm text-gray-900"
              >
                Enable webhook
              </label>
            </div>

            <div class="flex justify-end space-x-3 pt-6 border-t">
              <button
                type="button"
                @click="$emit('close')"
                class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50"
              >
                Cancel
              </button>
              <button
                type="button"
                @click="testWebhook"
                class="px-4 py-2 text-blue-600 border border-blue-600 rounded-md hover:bg-blue-50"
                :disabled="!webhookData.url"
              >
                Test Webhook
              </button>
              <button
                type="submit"
                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"
              >
                Save Webhook
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from "vue";

defineProps({
  show: {
    type: Boolean,
    default: false,
  },
});

defineEmits(["close", "save"]);

const webhookData = ref({
  url: "",
  secret: "",
  events: [],
  active: true,
});

const availableEvents = [
  { value: "order.created", label: "Order Created" },
  { value: "order.updated", label: "Order Updated" },
  { value: "order.cancelled", label: "Order Cancelled" },
  { value: "product.created", label: "Product Created" },
  { value: "product.updated", label: "Product Updated" },
  { value: "customer.created", label: "Customer Created" },
  { value: "customer.updated", label: "Customer Updated" },
];

const saveWebhook = () => {
  // Emit save event with webhook data
  console.log("Saving webhook:", webhookData.value);
};

const testWebhook = () => {
  // Test webhook functionality
  console.log("Testing webhook:", webhookData.value.url);
};
</script>
