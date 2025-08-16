<template>
    <div class="min-h-screen flex">
        <!-- Left Panel - Branding -->
        <div class="hidden lg:flex lg:flex-1 lg:flex-col lg:justify-center lg:px-6 lg:py-12 lg:bg-gradient-to-br lg:from-indigo-600 lg:to-purple-700">
            <div class="mx-auto w-full max-w-sm">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <img 
                        v-if="props.branding.logo"
                        :src="props.branding.logo" 
                        :alt="props.app_name"
                        class="h-12 w-auto mx-auto"
                    >
                    <h1 v-else class="text-3xl font-bold text-white">
                        {{ props.app_name }}
                    </h1>
                </div>
                
                <!-- Welcome Message -->
                <div class="text-center">
                    <h2 class="text-xl font-semibold text-white mb-4">
                    Accedi
                    </h2>
                    <p class="text-indigo-200 leading-relaxed">
                        Gestisci il tuo e-commerce con strumenti potenti e intuitivi.
                        Controlla prodotti, ordini, clienti e molto altro.
                    </p>
                </div>

                <!-- Features -->
                <div class="mt-8 space-y-4">
                    <div class="flex items-center text-indigo-200">
                        <svg class="w-5 h-5 mr-3 text-indigo-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Dashboard e analytics avanzate
                    </div>
                    <div class="flex items-center text-indigo-200">
                        <svg class="w-5 h-5 mr-3 text-indigo-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        Sistema multilingue completo
                    </div>
                    <div class="flex items-center text-indigo-200">
                        <svg class="w-5 h-5 mr-3 text-indigo-300" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        App marketplace integrato
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Panel - Login Form -->
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-8">
            <div class="mx-auto w-full max-w-sm">
                <!-- Mobile Logo -->
                <div class="text-center mb-8 lg:hidden">
                    <img 
                        v-if="props.branding.logo"
                        :src="props.branding.logo" 
                        :alt="props.app_name"
                        class="h-10 w-auto mx-auto"
                    >
                    <h1 v-else class="text-2xl font-bold text-gray-900">
                        {{ props.app_name }}
                    </h1>
                </div>

                <!-- Header -->
                <div class="text-center mb-8">
                    <h2 class="text-2xl font-bold leading-9 tracking-tight text-gray-900 dark:text-white">
                        {{ t('shopper.auth.headings.login') }}
                    </h2>
                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                        {{ t('shopper.auth.descriptions.login') }}
                    </p>
                </div>

                <!-- Status Messages -->
                <div v-if="props.status" class="mb-4 p-4 rounded-md bg-green-50 border border-green-200">
                    <p class="text-sm text-green-800">{{ props.status }}</p>
                </div>

                <!-- Login Form -->
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email Field -->
                    <div>
                        <label for="email" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                            {{ t('shopper.auth.labels.email') }}
                        </label>
                        <div class="mt-2">
                            <input
                                id="email"
                                v-model="form.email"
                                type="email"
                                :placeholder="t('shopper.auth.placeholders.email')"
                                required
                                autocomplete="email"
                                :class="[
                                    'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                    props.errors.email ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300'
                                ]"
                            />
                            <div v-if="props.errors.email" class="mt-1 text-sm text-red-600">
                                {{ props.errors.email }}
                            </div>
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div>
                        <label for="password" class="block text-sm font-medium leading-6 text-gray-900 dark:text-white">
                            {{ t('shopper.auth.labels.password') }}
                        </label>
                        <div class="mt-2">
                            <input
                                id="password"
                                v-model="form.password"
                                type="password"
                                :placeholder="t('shopper.auth.placeholders.password')"
                                required
                                autocomplete="current-password"
                                :class="[
                                    'block w-full rounded-md border-0 py-1.5 px-3 text-gray-900 shadow-sm ring-1 ring-inset placeholder:text-gray-400 focus:ring-2 focus:ring-inset focus:ring-indigo-600 sm:text-sm sm:leading-6',
                                    props.errors.password ? 'ring-red-300 focus:ring-red-600' : 'ring-gray-300'
                                ]"
                            />
                            <div v-if="props.errors.password" class="mt-1 text-sm text-red-600">
                                {{ props.errors.password }}
                            </div>
                        </div>
                    </div>

                    <!-- Remember Me & Forgot Password -->
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <input
                                id="remember"
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-600"
                            />
                            <label for="remember" class="ml-2 block text-sm text-gray-900 dark:text-white">
                                {{ t('shopper.auth.labels.remember_me') }}
                            </label>
                        </div>

                        <div class="text-sm" v-if="props.canResetPassword">
                            <Link
                                href="/cp/password/request"
                                class="font-semibold text-indigo-600 hover:text-indigo-500"
                            >
                                {{ t('shopper.auth.labels.forgot_password') }}
                            </Link>
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
                                {{ t('shopper.auth.actions.signing_in') }}
                            </span>
                            <span v-else>
                                {{ t('shopper.auth.labels.login') }}
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Language Selector -->
                <div class="mt-8 text-center" v-if="props.locales && props.locales.length > 1">
                    <div class="flex justify-center space-x-4">
                        <button
                            v-for="loc in props.locales"
                            :key="typeof loc === 'string' ? loc : loc.code"
                            @click="changeLocale(typeof loc === 'string' ? loc : loc.code)"
                            :class="[
                                'text-sm font-medium',
                                props.locale === (typeof loc === 'string' ? loc : loc.code) ? 'text-indigo-600' : 'text-gray-500 hover:text-gray-700'
                            ]"
                        >
                            {{ typeof loc === 'string' ? loc.toUpperCase() : (loc.name || loc.code?.toUpperCase()) }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { useForm, Head, Link } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'

// Route helper
const route = (name, params = {}, absolute = true) => {
    return window.route ? window.route(name, params, absolute) : '#'
}

// Props
const props = defineProps({
    status: String,
    canResetPassword: Boolean,
    locale: String,
    locales: {
        type: Array,
        default: () => []
    },
    app_name: String,
    cp_name: String,
    branding: {
        type: Object,
        default: () => ({})
    },
    errors: {
        type: Object,
        default: () => ({})
    },
})

// Translations
const { t } = useTranslations()

// Form
const form = useForm({
    email: '',
    password: '',
    remember: false,
})

// Methods
const submit = () => {
    form.post(route('cp.login'), {
        onFinish: () => {
            form.reset('password')
        },
    })
}

const changeLocale = (locale) => {
    // This would typically make a request to change locale
    window.location.href = `${window.location.pathname}?locale=${locale}`
}
</script>

