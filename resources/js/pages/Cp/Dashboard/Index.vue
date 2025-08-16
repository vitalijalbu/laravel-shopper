<template>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold text-gray-900">
                    {{ t('shopper.dashboard.title', 'Dashboard') }}
                </h1>
                
                <!-- User Menu -->
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-700">
                        {{ t('shopper.dashboard.welcome', 'Benvenuto') }}, {{ user.name }}
                    </span>
                    <form @submit.prevent="logout" class="inline">
                        <button 
                            type="submit"
                            class="text-sm text-gray-500 hover:text-gray-700"
                        >
                            {{ t('shopper.auth.labels.logout', 'Logout') }}
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
            <div class="px-4 py-6 sm:px-0">
                <!-- Welcome Card -->
                <div class="bg-white overflow-hidden shadow rounded-lg mb-8">
                    <div class="px-4 py-5 sm:p-6">
                        <h2 class="text-lg font-medium text-gray-900 mb-2">
                            {{ t('shopper.dashboard.welcome_message', 'Benvenuto nel Control Panel di Shopper') }}
                        </h2>
                        <p class="text-gray-600">
                            {{ t('shopper.dashboard.description', 'Gestisci il tuo e-commerce da qui. Controlla vendite, prodotti, ordini e molto altro.') }}
                        </p>
                    </div>
                </div>

                <!-- Stats Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                    <!-- Revenue -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2V6h10a2 2 0 00-2-2H4zm2 6a2 2 0 012-2h8a2 2 0 012 2v4a2 2 0 01-2 2H8a2 2 0 01-2-2v-4zm6 4a2 2 0 100-4 2 2 0 000 4z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ t('shopper.dashboard.revenue', 'Ricavi') }}
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.revenue || '€0.00' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Orders -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 2L3 7v11a1 1 0 001 1h12a1 1 0 001-1V7l-7-5zM8 15V9h4v6H8z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ t('shopper.dashboard.orders', 'Ordini') }}
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.orders || '0' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Products -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M7 4V2a1 1 0 011-1h4a1 1 0 011 1v2h4a1 1 0 110 2h-1v10a2 2 0 01-2 2H6a2 2 0 01-2-2V6H3a1 1 0 010-2h4zM9 3v1h2V3H9zm-3 3v10h8V6H6z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ t('shopper.dashboard.products', 'Prodotti') }}
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.products || '0' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Customers -->
                    <div class="bg-white overflow-hidden shadow rounded-lg">
                        <div class="p-5">
                            <div class="flex items-center">
                                <div class="flex-shrink-0">
                                    <div class="w-8 h-8 bg-indigo-500 rounded-md flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="ml-5 w-0 flex-1">
                                    <dl>
                                        <dt class="text-sm font-medium text-gray-500 truncate">
                                            {{ t('shopper.dashboard.customers', 'Clienti') }}
                                        </dt>
                                        <dd class="text-lg font-medium text-gray-900">
                                            {{ stats.customers || '0' }}
                                        </dd>
                                    </dl>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white shadow rounded-lg">
                    <div class="px-4 py-5 sm:p-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">
                            {{ t('shopper.dashboard.quick_actions', 'Azioni Rapide') }}
                        </h3>
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                            <button class="p-4 text-center border border-gray-300 rounded-lg hover:border-gray-400 transition-colors">
                                <div class="w-8 h-8 bg-blue-500 rounded-md mx-auto mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ t('shopper.dashboard.add_product', 'Aggiungi Prodotto') }}
                                </span>
                            </button>
                            
                            <button class="p-4 text-center border border-gray-300 rounded-lg hover:border-gray-400 transition-colors">
                                <div class="w-8 h-8 bg-green-500 rounded-md mx-auto mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zM3 10a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1v-6zM14 9a1 1 0 00-1 1v6a1 1 0 001 1h2a1 1 0 001-1v-6a1 1 0 00-1-1h-2z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ t('shopper.dashboard.view_orders', 'Vedi Ordini') }}
                                </span>
                            </button>
                            
                            <button class="p-4 text-center border border-gray-300 rounded-lg hover:border-gray-400 transition-colors">
                                <div class="w-8 h-8 bg-purple-500 rounded-md mx-auto mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M13 6a3 3 0 11-6 0 3 3 0 016 0zM18 8a2 2 0 11-4 0 2 2 0 014 0zM14 15a4 4 0 00-8 0v3h8v-3z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ t('shopper.dashboard.manage_customers', 'Gestisci Clienti') }}
                                </span>
                            </button>
                            
                            <button class="p-4 text-center border border-gray-300 rounded-lg hover:border-gray-400 transition-colors">
                                <div class="w-8 h-8 bg-indigo-500 rounded-md mx-auto mb-2 flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M11.49 3.17c-.38-1.56-2.6-1.56-2.98 0a1.532 1.532 0 01-2.286.948c-1.372-.836-2.942.734-2.106 2.106.54.886.061 2.042-.947 2.287-1.561.379-1.561 2.6 0 2.978a1.532 1.532 0 01.947 2.287c-.836 1.372.734 2.942 2.106 2.106a1.532 1.532 0 012.287.947c.379 1.561 2.6 1.561 2.978 0a1.533 1.533 0 012.287-.947c1.372.836 2.942-.734 2.106-2.106a1.533 1.533 0 01.947-2.287c1.561-.379 1.561-2.6 0-2.978a1.532 1.532 0 01-.947-2.287c.836-1.372-.734-2.942-2.106-2.106a1.532 1.532 0 01-2.287-.947zM10 13a3 3 0 100-6 3 3 0 000 6z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ t('shopper.dashboard.settings', 'Impostazioni') }}
                                </span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'
import { useTranslations } from '@/composables/useTranslations'

// Props
const props = defineProps({
    user: {
        type: Object,
        required: true
    },
    stats: {
        type: Object,
        default: () => ({})
    },
    recentActivities: {
        type: Array,
        default: () => []
    },
    notifications: {
        type: Array,
        default: () => []
    }
})

// Translations
const { t } = useTranslations()

// Logout form
const logoutForm = useForm({})

const logout = () => {
    logoutForm.post(route('cp.logout'))
}

// Helper to get route
const route = (name, params = {}) => {
    return window.route ? window.route(name, params) : '#'
}
</script>
