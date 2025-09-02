<template>
  <div>
    <Head :title="page.title" />

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-900">{{ $t('settings.title') }}</h1>
        <p class="mt-2 text-gray-600">{{ $t('settings.subtitle') }}</p>
      </div>

      <!-- Settings Cards Grid -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        
        <!-- General Settings -->
        <Link 
          :href="route('cp.settings.general')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <CogIcon class="h-8 w-8 text-blue-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-blue-600">
                {{ $t('settings.general.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.general.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-blue-600 group-hover:text-blue-700">
            <span>{{ $t('settings.general.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

        <!-- Payment Gateways -->
        <Link 
          :href="route('cp.settings.payment-gateways.index')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <CreditCardIcon class="h-8 w-8 text-green-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-green-600">
                {{ $t('settings.payment_gateways.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.payment_gateways.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-green-600 group-hover:text-green-700">
            <span>{{ $t('settings.payment_gateways.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

        <!-- Tax Rates -->
        <Link 
          :href="route('cp.settings.tax-rates.index')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <CalculatorIcon class="h-8 w-8 text-yellow-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-yellow-600">
                {{ $t('settings.tax_rates.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.tax_rates.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-yellow-600 group-hover:text-yellow-700">
            <span>{{ $t('settings.tax_rates.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

        <!-- Shipping Methods -->
        <Link 
          :href="route('cp.settings.shipping-methods.index')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <TruckIcon class="h-8 w-8 text-orange-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-orange-600">
                {{ $t('settings.shipping_methods.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.shipping_methods.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-orange-600 group-hover:text-orange-700">
            <span>{{ $t('settings.shipping_methods.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

        <!-- Checkout Settings -->
        <Link 
          :href="route('cp.settings.checkout')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <ShoppingCartIcon class="h-8 w-8 text-indigo-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-indigo-600">
                {{ $t('settings.checkout.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.checkout.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-indigo-600 group-hover:text-indigo-700">
            <span>{{ $t('settings.checkout.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

        <!-- Email Settings -->
        <Link 
          :href="route('cp.settings.email')"
          class="group relative overflow-hidden rounded-lg border border-gray-200 bg-white p-6 shadow-sm hover:shadow-md transition-shadow duration-200"
        >
          <div class="flex items-center mb-4">
            <div class="flex-shrink-0">
              <EnvelopeIcon class="h-8 w-8 text-red-600" />
            </div>
            <div class="ml-4">
              <h3 class="text-lg font-medium text-gray-900 group-hover:text-red-600">
                {{ $t('settings.email.title') }}
              </h3>
            </div>
          </div>
          <p class="text-sm text-gray-500">
            {{ $t('settings.email.description') }}
          </p>
          <div class="mt-4 flex items-center text-sm text-red-600 group-hover:text-red-700">
            <span>{{ $t('settings.email.action') }}</span>
            <ArrowRightIcon class="ml-2 h-4 w-4" />
          </div>
        </Link>

      </div>

      <!-- Quick Stats -->
      <div class="mt-12 bg-gray-50 rounded-lg p-6">
        <h2 class="text-lg font-medium text-gray-900 mb-4">{{ $t('settings.configuration_status.title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
          
          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div :class="['w-3 h-3 rounded-full', getStatusColor(stats.payment_gateways.status)]"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ $t('settings.configuration_status.gateway_active') }}</p>
                <p class="text-xs text-gray-500">
                  {{ $t('settings.configuration_status.configured_of', { 
                    active: stats.payment_gateways.active, 
                    total: stats.payment_gateways.total 
                  }) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div :class="['w-3 h-3 rounded-full', getStatusColor(stats.tax_rates.status)]"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ $t('settings.configuration_status.tax_zones') }}</p>
                <p class="text-xs text-gray-500">
                  {{ $t('settings.configuration_status.active_rates', { count: stats.tax_rates.active }) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div :class="['w-3 h-3 rounded-full', getStatusColor(stats.shipping_methods.status)]"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ $t('settings.configuration_status.shipping') }}</p>
                <p class="text-xs text-gray-500">
                  {{ $t('settings.configuration_status.active_methods', { count: stats.shipping_methods.active }) }}
                </p>
              </div>
            </div>
          </div>

          <div class="bg-white rounded-lg p-4 shadow-sm">
            <div class="flex items-center">
              <div class="flex-shrink-0">
                <div :class="['w-3 h-3 rounded-full', getStatusColor(stats.email.status)]"></div>
              </div>
              <div class="ml-3">
                <p class="text-sm font-medium text-gray-900">{{ $t('settings.configuration_status.email') }}</p>
                <p class="text-xs text-gray-500">
                  {{ stats.email.configured 
                    ? $t('settings.configuration_status.configuration_complete') 
                    : 'Non configurato' 
                  }}
                </p>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { Head, Link } from '@inertiajs/vue3'
import {
  ArrowRightIcon,
  CalculatorIcon,
  CogIcon,
  CreditCardIcon,
  EnvelopeIcon,
  ShoppingCartIcon,
  TruckIcon
} from '@heroicons/vue/24/outline'

defineProps({
  page: Object,
  navigation: Object,
  stats: Object,
})

// Helper function to get status color classes
const getStatusColor = (status) => {
  switch (status) {
    case 'success':
      return 'bg-green-500'
    case 'warning':
      return 'bg-yellow-500'
    case 'error':
      return 'bg-red-500'
    default:
      return 'bg-gray-500'
  }
}
</script>
