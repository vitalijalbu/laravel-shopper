<template>
  <!-- Static sidebar for desktop -->
  <div class="hidden lg:fixed lg:inset-y-0 lg:z-50 lg:flex lg:w-64 lg:flex-col">
    <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white border-r border-gray-200 px-6 pb-4">
      <!-- Logo -->
      <div class="flex h-16 shrink-0 items-center">
        <Link href="/" class="flex items-center space-x-2">
          <div class="h-8 w-8 bg-indigo-600 rounded-lg flex items-center justify-center">
            <span class="text-white font-bold text-sm">S</span>
          </div>
          <span class="text-lg font-semibold text-gray-900">Shopper</span>
        </Link>
      </div>

      <!-- Navigation -->
      <nav class="flex flex-1 flex-col">
        <ul role="list" class="flex flex-1 flex-col gap-y-7">
          <li>
            <ul role="list" class="-mx-2 space-y-1">
              <li v-for="item in navigation" :key="item.name">
                <Link
                  :href="item.href"
                  :class="[
                    isCurrentRoute(item.href)
                      ? 'bg-gray-50 text-indigo-600'
                      : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50',
                    'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold'
                  ]"
                >
                  <component
                    :is="item.icon"
                    :class="[
                      isCurrentRoute(item.href) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600',
                      'h-6 w-6 shrink-0'
                    ]"
                    aria-hidden="true"
                  />
                  {{ item.name }}
                </Link>
              </li>
            </ul>
          </li>
        </ul>
      </nav>
    </div>
  </div>

  <!-- Mobile sidebar -->
  <div v-show="open" class="relative z-50 lg:hidden" @click="$emit('close')">
    <div class="fixed inset-0 bg-gray-900/80" />
    <div class="fixed inset-0 flex">
      <div class="relative mr-16 flex w-full max-w-xs flex-1" @click.stop>
        <div class="absolute left-full top-0 flex w-16 justify-center pt-5">
          <button type="button" class="-m-2.5 p-2.5" @click="$emit('close')">
            <span class="sr-only">Close sidebar</span>
            <XMarkIcon class="h-6 w-6 text-white" aria-hidden="true" />
          </button>
        </div>
        <!-- Mobile sidebar content (same as desktop) -->
        <div class="flex grow flex-col gap-y-5 overflow-y-auto bg-white px-6 pb-4">
          <div class="flex h-16 shrink-0 items-center">
            <Link href="/" class="flex items-center space-x-2">
              <div class="h-8 w-8 bg-indigo-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">S</span>
              </div>
              <span class="text-lg font-semibold text-gray-900">Shopper</span>
            </Link>
          </div>
          <nav class="flex flex-1 flex-col">
            <ul role="list" class="flex flex-1 flex-col gap-y-7">
              <li>
                <ul role="list" class="-mx-2 space-y-1">
                  <li v-for="item in navigation" :key="item.name">
                    <Link
                      :href="item.href"
                      :class="[
                        isCurrentRoute(item.href)
                          ? 'bg-gray-50 text-indigo-600'
                          : 'text-gray-700 hover:text-indigo-600 hover:bg-gray-50',
                        'group flex gap-x-3 rounded-md p-2 text-sm leading-6 font-semibold'
                      ]"
                    >
                      <component
                        :is="item.icon"
                        :class="[
                          isCurrentRoute(item.href) ? 'text-indigo-600' : 'text-gray-400 group-hover:text-indigo-600',
                          'h-6 w-6 shrink-0'
                        ]"
                        aria-hidden="true"
                      />
                      {{ item.name }}
                    </Link>
                  </li>
                </ul>
              </li>
            </ul>
          </nav>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'
import { 
  HomeIcon,
  ShoppingBagIcon,
  ShoppingCartIcon,
  UserGroupIcon,
  TagIcon,
  BuildingStorefrontIcon,
  RectangleStackIcon,
  TicketIcon,
  CogIcon,
  XMarkIcon
} from '@heroicons/vue/24/outline'

defineProps({
  open: Boolean
})

defineEmits(['close'])

const page = usePage()

const navigation = [
  { name: 'Dashboard', href: '/admin', icon: HomeIcon },
  { name: 'Orders', href: '/admin/orders', icon: ShoppingCartIcon },
  { name: 'Products', href: '/admin/products', icon: ShoppingBagIcon },
  { name: 'Customers', href: '/admin/customers', icon: UserGroupIcon },
  { name: 'Categories', href: '/admin/categories', icon: TagIcon },
  { name: 'Brands', href: '/admin/brands', icon: BuildingStorefrontIcon },
  { name: 'Collections', href: '/admin/collections', icon: RectangleStackIcon },
  { name: 'Discounts', href: '/admin/discounts', icon: TicketIcon },
  { name: 'Settings', href: '/admin/settings', icon: CogIcon },
]

const isCurrentRoute = (href) => {
  return page.url === href || page.url.startsWith(href + '/')
}
</script>
