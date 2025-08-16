<template>
    <div class="min-h-screen flex items-center justify-center px-6 py-12 lg:px-8">
        <div class="mx-auto w-full max-w-sm">
            <!-- Logo -->
            <div class="text-center mb-8">
                <img 
                    v-if="branding.logo"
                    :src="branding.logo" 
                    :alt="app_name"
                    class="h-10 w-auto mx-auto"
                >
                <h1 v-else class="text-2xl font-bold text-gray-900 dark:text-white">
                    {{ app_name }}
                </h1>
            </div>

            <!-- Header -->
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                    {{ $t('shopper::auth.headings.forgot_password') }}
                </h2>
                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                    {{ $t('shopper::auth.descriptions.forgot_password') }}
                </p>
            </div>

            <!-- Status Messages -->
            <div v-if="status" class="mb-4 p-4 rounded-md bg-green-50 border border-green-200">
                <p class="text-sm text-green-800">{{ status }}</p>
            </div>

            <!-- Reset Form -->
            <form @submit.prevent="submit" class="space-y-6">
                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                        {{ $t('shopper::auth.labels.email') }}
                    </label>
                    <div class="mt-2">
                        <input
                            id="email"
                            v-model="form.email"
                            type="email"
                            :placeholder="$t('shopper::auth.placeholders.email')"
                            required
                            autocomplete="email"
                            autofocus
                            :class="[
                                'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                errors.email ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300'
                            ]"
                        />
                        <div v-if="errors.email" class="mt-1 text-sm text-red-600">
                            {{ errors.email }}
                        </div>
                    </div>
                </div>

                <!-- Submit Button -->
                <div>
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="flex w-full justify-center rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold leading-6 text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span v-if="form.processing" class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                            </svg>
                            {{ $t('shopper::auth.actions.sending_link') }}
                        </span>
                        <span v-else>
                            {{ $t('shopper::auth.labels.send_reset_link') }}
                        </span>
                    </button>
                </div>

                <!-- Back to Login -->
                <div class="text-center">
                    <Link
                        :href="route('cp.login')"
                        class="text-sm font-semibold text-indigo-600 hover:text-indigo-500"
                    >
                        {{ $t('shopper::auth.labels.back_to_login') }}
                    </Link>
                </div>
            </form>

            <!-- Language Selector -->
            <div class="mt-8 text-center" v-if="locales && locales.length > 1">
                <div class="flex justify-center space-x-4">
                    <button
                        v-for="loc in locales"
                        :key="loc"
                        @click="changeLocale(loc)"
                        :class="[
                            'text-sm font-medium',
                            locale === loc ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'
                        ]"
                    >
                        {{ loc.toUpperCase() }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { useForm, Head, Link } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'

// Props
const props = defineProps({
    status: String,
    locale: String,
    locales: Array,
    app_name: String,
    cp_name: String,
    branding: Object,
    errors: Object,
})

// Composables
const { t } = useI18n()

// Form
const form = useForm({
    email: '',
})

// Methods
const submit = () => {
    form.post(route('cp.password.email'))
}

const changeLocale = (locale) => {
    window.location.href = `${window.location.pathname}?locale=${locale}`
}
</script>

<style scoped>
/* Dark mode styles */
@media (prefers-color-scheme: dark) {
    .dark\:text-white {
        color: #ffffff;
    }
    
    .dark\:text-gray-400 {
        color: #9ca3af;
    }
}
</style>
