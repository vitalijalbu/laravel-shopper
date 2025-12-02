<script setup>
import { useForm } from '@inertiajs/vue3'
import { provide, reactive, computed } from 'vue'

const props = defineProps({
  method: {
    type: String,
    default: 'post'
  },
  url: String,
  initialValues: {
    type: Object,
    default: () => ({})
  },
  preserveScroll: Boolean,
  preserveState: Boolean,
  only: Array,
  except: Array,
  onSuccess: Function,
  onError: Function,
  onFinish: Function
})

const emit = defineEmits(['submit'])

// Create Inertia form if URL is provided
const form = props.url 
  ? useForm(props.initialValues) 
  : reactive(props.initialValues)

// Provide form to child components
provide('form', form)
provide('formErrors', computed(() => form.value?.errors || {}))

const handleSubmit = (e) => {
  e.preventDefault()

  if (props.url) {
    const options = {
      preserveScroll: props.preserveScroll,
      preserveState: props.preserveState,
      only: props.only,
      except: props.except,
      onSuccess: props.onSuccess,
      onError: props.onError,
      onFinish: props.onFinish,
    }

    form.value[props.method](props.url, options)
  } else {
    emit('submit', form.value)
  }
}

defineExpose({ form, submit: handleSubmit })
</script>

<template>
  <form @submit="handleSubmit" class="space-y-6">
    <slot :form="form" :errors="form?.errors || {}" />
  </form>
</template>
