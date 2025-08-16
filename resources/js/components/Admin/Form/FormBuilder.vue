<template>
  <form @submit.prevent="handleSubmit" class="space-y-6">
    <!-- Form Header -->
    <div v-if="title || description" class="border-b border-gray-200 pb-5">
      <h3 v-if="title" class="text-lg font-medium leading-6 text-gray-900">
        {{ title }}
      </h3>
      <p v-if="description" class="mt-2 max-w-4xl text-sm text-gray-500">
        {{ description }}
      </p>
    </div>

    <!-- Form Fields -->
    <div class="space-y-6">
      <div
        v-for="field in computedFields"
        :key="field.name"
        :class="getFieldWrapperClass(field)"
      >
        <!-- Field Label -->
        <label
          v-if="field.label"
          :for="field.name"
          class="block text-sm font-medium text-gray-700"
          :class="{ required: field.required }"
        >
          {{ field.label }}
          <span v-if="field.required" class="text-red-500">*</span>
        </label>

        <!-- Field Help Text -->
        <p v-if="field.help" class="text-sm text-gray-500 mb-2">
          {{ field.help }}
        </p>

        <!-- Input Fields -->
        <div class="mt-1">
          <!-- Text/Email/Password/URL -->
          <input
            v-if="
              ['text', 'email', 'password', 'url', 'number'].includes(
                field.type,
              )
            "
            :id="field.name"
            :name="field.name"
            :type="field.type"
            :placeholder="field.placeholder"
            :required="field.required"
            :disabled="field.disabled"
            :class="getInputClass(field)"
            v-model="formData[field.name]"
          />

          <!-- Textarea -->
          <textarea
            v-else-if="field.type === 'textarea'"
            :id="field.name"
            :name="field.name"
            :placeholder="field.placeholder"
            :required="field.required"
            :disabled="field.disabled"
            :rows="field.rows || 4"
            :class="getInputClass(field)"
            v-model="formData[field.name]"
          />

          <!-- Select -->
          <select
            v-else-if="field.type === 'select'"
            :id="field.name"
            :name="field.name"
            :required="field.required"
            :disabled="field.disabled"
            :class="getInputClass(field)"
            v-model="formData[field.name]"
          >
            <option v-if="field.placeholder" value="">
              {{ field.placeholder }}
            </option>
            <option
              v-for="option in field.options"
              :key="option.value"
              :value="option.value"
            >
              {{ option.label }}
            </option>
          </select>

          <!-- Checkbox -->
          <div v-else-if="field.type === 'checkbox'" class="flex items-center">
            <input
              :id="field.name"
              :name="field.name"
              type="checkbox"
              :disabled="field.disabled"
              class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded"
              v-model="formData[field.name]"
            />
            <label :for="field.name" class="ml-2 block text-sm text-gray-900">
              {{ field.checkboxLabel || field.label }}
            </label>
          </div>

          <!-- File Upload -->
          <div v-else-if="field.type === 'file'" class="mt-1">
            <input
              :id="field.name"
              :name="field.name"
              type="file"
              :accept="field.accept"
              :multiple="field.multiple"
              :required="field.required"
              :disabled="field.disabled"
              class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100"
              @change="handleFileChange(field.name, $event)"
            />
          </div>

          <!-- Date -->
          <input
            v-else-if="field.type === 'date'"
            :id="field.name"
            :name="field.name"
            type="date"
            :required="field.required"
            :disabled="field.disabled"
            :class="getInputClass(field)"
            v-model="formData[field.name]"
          />

          <!-- Custom Component Slot -->
          <slot
            v-else-if="field.type === 'custom'"
            :name="`field-${field.name}`"
            :field="field"
            :value="formData[field.name]"
            :update="(value) => (formData[field.name] = value)"
          />
        </div>

        <!-- Field Error -->
        <p v-if="errors[field.name]" class="mt-2 text-sm text-red-600">
          {{ errors[field.name] }}
        </p>
      </div>
    </div>

    <!-- Form Actions -->
    <div class="flex justify-end space-x-3 pt-6 border-t border-gray-200">
      <button
        v-if="showCancel"
        type="button"
        @click="$emit('cancel')"
        class="bg-white py-2 px-4 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
      >
        {{ cancelText }}
      </button>
      <button
        type="submit"
        :disabled="loading"
        class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50"
      >
        <svg
          v-if="loading"
          class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
          xmlns="http://www.w3.org/2000/svg"
          fill="none"
          viewBox="0 0 24 24"
        >
          <circle
            class="opacity-25"
            cx="12"
            cy="12"
            r="10"
            stroke="currentColor"
            stroke-width="4"
          ></circle>
          <path
            class="opacity-75"
            fill="currentColor"
            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
          ></path>
        </svg>
        {{ loading ? loadingText : submitText }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { reactive, computed, watch, onMounted } from "vue";

// Props
const props = defineProps({
  title: String,
  description: String,
  fields: {
    type: Array,
    required: true,
  },
  modelValue: {
    type: Object,
    default: () => ({}),
  },
  errors: {
    type: Object,
    default: () => ({}),
  },
  loading: {
    type: Boolean,
    default: false,
  },
  showCancel: {
    type: Boolean,
    default: true,
  },
  submitText: {
    type: String,
    default: "Save",
  },
  cancelText: {
    type: String,
    default: "Cancel",
  },
  loadingText: {
    type: String,
    default: "Saving...",
  },
});

// Emits
const emit = defineEmits(["update:modelValue", "submit", "cancel", "change"]);

// Form data
const formData = reactive({ ...props.modelValue });

// Watch for external changes
watch(
  () => props.modelValue,
  (newValue) => {
    Object.assign(formData, newValue);
  },
  { deep: true },
);

// Watch for form changes
watch(
  formData,
  (newValue) => {
    emit("update:modelValue", newValue);
    emit("change", newValue);
  },
  { deep: true },
);

// Computed fields with defaults
const computedFields = computed(() => {
  return props.fields.map((field) => ({
    type: "text",
    required: false,
    disabled: false,
    ...field,
  }));
});

// Initialize form data
onMounted(() => {
  // Initialize empty values for all fields
  computedFields.value.forEach((field) => {
    if (!(field.name in formData)) {
      if (field.type === "checkbox") {
        formData[field.name] = false;
      } else if (field.type === "select" && field.multiple) {
        formData[field.name] = [];
      } else {
        formData[field.name] = "";
      }
    }
  });
});

// Methods
const handleSubmit = () => {
  emit("submit", formData);
};

const handleFileChange = (fieldName, event) => {
  const files = event.target.files;
  formData[fieldName] = files.length > 1 ? files : files[0];
};

const getFieldWrapperClass = (field) => {
  const baseClass = "space-y-1";
  if (field.width) {
    return `${baseClass} ${field.width}`;
  }
  return baseClass;
};

const getInputClass = (field) => {
  const baseClass =
    "block w-full rounded-md border-gray-300 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm";
  const errorClass = props.errors[field.name]
    ? "border-red-300 text-red-900 placeholder-red-300 focus:ring-red-500 focus:border-red-500"
    : "";
  const disabledClass = field.disabled ? "bg-gray-50 cursor-not-allowed" : "";

  return [baseClass, errorClass, disabledClass].filter(Boolean).join(" ");
};
</script>

<style scoped>
.required::after {
  content: " *";
  color: #ef4444;
}
</style>
