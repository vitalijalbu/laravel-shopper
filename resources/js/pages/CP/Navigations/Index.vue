<template>
  <div>
    <Head title="Navigations" />

    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Navigations</h1>
        <p class="text-gray-600 mt-1">Manage your site's navigation menus</p>
      </div>
      <Link
        :href="route('cp.navigations.create')"
        class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
      >
        <PlusIcon class="w-4 h-4 mr-2" />
        Create Navigation
      </Link>
    </div>

    <div class="bg-white shadow-sm rounded-lg border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">All Navigations</h2>
      </div>

      <div v-if="navigations.data.length === 0" class="p-12 text-center">
        <div class="text-gray-400 mb-4">
          <NavigationIcon class="w-16 h-16 mx-auto" />
        </div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No navigations yet</h3>
        <p class="text-gray-600 mb-6">
          Get started by creating your first navigation menu.
        </p>
        <Link
          :href="route('cp.navigations.create')"
          class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700"
        >
          <PlusIcon class="w-4 h-4 mr-2" />
          Create your first navigation
        </Link>
      </div>

      <div v-else class="divide-y divide-gray-200">
        <div
          v-for="navigation in navigations.data"
          :key="navigation.id"
          class="p-6 hover:bg-gray-50 transition-colors"
        >
          <div class="flex items-center justify-between">
            <div class="flex-1">
              <div class="flex items-center space-x-3">
                <Link
                  :href="route('cp.navigations.show', navigation.handle)"
                  class="text-lg font-semibold text-gray-900 hover:text-blue-600"
                >
                  {{ navigation.title }}
                </Link>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                  {{ navigation.handle }}
                </span>
              </div>
              <p class="text-gray-600 mt-1">{{ navigation.description }}</p>
              <div class="flex items-center space-x-4 mt-3 text-sm text-gray-500">
                <span>{{ navigation.items_count }} items</span>
                <span>Created {{ formatDate(navigation.created_at) }}</span>
                <span>Updated {{ formatDate(navigation.updated_at) }}</span>
              </div>
            </div>
            <div class="flex items-center space-x-2">
              <Link
                :href="route('cp.navigations.edit', navigation.handle)"
                class="inline-flex items-center px-3 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-md hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2"
              >
                <PencilIcon class="w-4 h-4 mr-2" />
                Edit
              </Link>
              <DropdownMenu>
                <DropdownMenuTrigger>
                  <Button variant="outline" size="sm">
                    <EllipsisVerticalIcon class="w-4 h-4" />
                  </Button>
                </DropdownMenuTrigger>
                <DropdownMenuContent align="end">
                  <DropdownMenuItem @click="duplicateNavigation(navigation)">
                    <DocumentDuplicateIcon class="w-4 h-4 mr-2" />
                    Duplicate
                  </DropdownMenuItem>
                  <DropdownMenuSeparator />
                  <DropdownMenuItem 
                    @click="deleteNavigation(navigation)"
                    class="text-red-600 focus:text-red-600"
                  >
                    <TrashIcon class="w-4 h-4 mr-2" />
                    Delete
                  </DropdownMenuItem>
                </DropdownMenuContent>
              </DropdownMenu>
            </div>
          </div>
        </div>
      </div>

      <div v-if="navigations.links && navigations.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <Pagination :links="navigations.links" />
      </div>
    </div>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialog.open">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Navigation</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete "{{ deleteDialog.navigation?.title }}"? 
            This action cannot be undone and will remove all navigation items.
          </AlertDialogDescription>
        </AlertDialogHeader>
        <AlertDialogFooter>
          <AlertDialogCancel @click="deleteDialog.open = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction
            @click="confirmDelete"
            class="bg-red-600 hover:bg-red-700 focus:ring-red-500"
          >
            Delete Navigation
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { 
  PlusIcon, 
  PencilIcon, 
  TrashIcon, 
  DocumentDuplicateIcon, 
  EllipsisVerticalIcon 
} from '@heroicons/vue/24/outline'
import { Bars3Icon as NavigationIcon } from '@heroicons/vue/24/solid'
import Button from '@/components/ui/button'
import { 
  DropdownMenu, 
  DropdownMenuContent, 
  DropdownMenuItem, 
  DropdownMenuSeparator, 
  DropdownMenuTrigger 
} from '@/components/ui/dropdown-menu'
import {
  AlertDialog,
  AlertDialogAction,
  AlertDialogCancel,
  AlertDialogContent,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogHeader,
  AlertDialogTitle,
} from '@/components/ui/alert-dialog'
import Pagination from '@/components/Pagination.vue'
import { formatDate } from '@/utils/date'
import { useToast } from '@/composables/useToast'

interface Navigation {
  id: number
  handle: string
  title: string
  description?: string
  items_count: number
  created_at: string
  updated_at: string
}

interface PaginatedNavigations {
  data: Navigation[]
  links: any[]
  meta: any
}

defineProps<{
  navigations: PaginatedNavigations
}>()

const { toast } = useToast()

const deleteDialog = ref({
  open: false,
  navigation: null as Navigation | null,
})

const deleteNavigation = (navigation: Navigation) => {
  deleteDialog.value = {
    open: true,
    navigation,
  }
}

const confirmDelete = () => {
  if (!deleteDialog.value.navigation) return

  router.delete(route('cp.navigations.destroy', deleteDialog.value.navigation.handle), {
    onSuccess: () => {
      toast({
        title: 'Navigation deleted',
        description: `"${deleteDialog.value.navigation?.title}" has been deleted successfully.`,
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to delete navigation. Please try again.',
        variant: 'destructive',
      })
    },
    onFinish: () => {
      deleteDialog.value = { open: false, navigation: null }
    },
  })
}

const duplicateNavigation = (navigation: Navigation) => {
  router.post(route('cp.navigations.duplicate', navigation.handle), {}, {
    onSuccess: () => {
      toast({
        title: 'Navigation duplicated',
        description: `"${navigation.title}" has been duplicated successfully.`,
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to duplicate navigation. Please try again.',
        variant: 'destructive',
      })
    },
  })
}
</script>
