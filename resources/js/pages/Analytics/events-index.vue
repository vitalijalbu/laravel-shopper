<script setup lang="ts">
import { ref, computed } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import Card from '../../Components/ui/Card.vue'
import CardHeader from '../../Components/ui/CardHeader.vue'
import CardTitle from '../../Components/ui/CardTitle.vue'
import CardContent from '../../Components/ui/CardContent.vue'
import { formatDate } from '../../utils/formatters'

interface AnalyticsEvent {
  id: number
  event_type: string
  event_data: Record<string, any>
  user_id: number | null
  session_id: string | null
  occurred_at: string
  user?: {
    name: string
    email: string
  }
}

interface PaginatedEvents {
  data: AnalyticsEvent[]
  current_page: number
  last_page: number
  per_page: number
  total: number
}

interface Props {
  events: PaginatedEvents
  event_types: string[]
  filters: {
    event_type?: string
    date_from?: string
    date_to?: string
    user_id?: string
  }
}

const props = defineProps<Props>()

const searchTerm = ref('')
const selectedEventType = ref(props.filters.event_type || '')
const dateFrom = ref(props.filters.date_from || '')
const dateTo = ref(props.filters.date_to || '')
const selectedUserId = ref(props.filters.user_id || '')

// Table columns configuration
const columns = [
  {
    key: 'event_type',
    label: 'Event Type',
    sortable: true,
  },
  {
    key: 'user',
    label: 'User',
    sortable: false,
  },
  {
    key: 'session_id',
    label: 'Session',
    sortable: false,
  },
  {
    key: 'occurred_at',
    label: 'Occurred At',
    sortable: true,
  },
  {
    key: 'actions',
    label: 'Actions',
    sortable: false,
  },
]

// Get event type badge class
const getEventTypeBadgeClass = (eventType: string) => {
  const classes = {
    page_view: 'bg-blue-100 text-blue-800',
    button_click: 'bg-green-100 text-green-800',
    form_submit: 'bg-purple-100 text-purple-800',
    purchase: 'bg-yellow-100 text-yellow-800',
    cart_add: 'bg-indigo-100 text-indigo-800',
    search: 'bg-pink-100 text-pink-800',
    order_placed: 'bg-emerald-100 text-emerald-800',
  }
  return classes[eventType as keyof typeof classes] || 'bg-gray-100 text-gray-800'
}

// Format event data for display
const formatEventData = (eventData: Record<string, any>) => {
  if (!eventData || Object.keys(eventData).length === 0) {
    return 'No data'
  }
  
  // Show key properties or truncated JSON
  const keys = Object.keys(eventData)
  if (keys.length <= 3) {
    return keys.map(key => `${key}: ${eventData[key]}`).join(', ')
  }
  
  return `${keys.length} properties`
}

// Handle filters change
const applyFilters = () => {
  const params = new URLSearchParams()
  
  if (selectedEventType.value) {
    params.append('event_type', selectedEventType.value)
  }
  
  if (dateFrom.value) {
    params.append('date_from', dateFrom.value)
  }
  
  if (dateTo.value) {
    params.append('date_to', dateTo.value)
  }
  
  if (selectedUserId.value) {
    params.append('user_id', selectedUserId.value)
  }
  
  router.get(`/admin/analytics/events?${params.toString()}`)
}

// Clear all filters
const clearFilters = () => {
  selectedEventType.value = ''
  dateFrom.value = ''
  dateTo.value = ''
  selectedUserId.value = ''
  applyFilters()
}

// Handle row click to view details
const viewEventDetails = (event: AnalyticsEvent) => {
  // In a real app, this might open a modal or navigate to a detail page
  console.log('Event details:', event)
}

// Handle export
const exportEvents = () => {
  const params = new URLSearchParams(window.location.search)
  params.append('export', 'true')
  window.open(`/admin/analytics/events?${params.toString()}`)
}

// Event type filter options
const eventTypeOptions = computed(() => {
  return props.event_types.map(type => ({
    label: type.replace('_', ' ').toUpperCase(),
    value: type,
  }))
})
</script>

<template>
  <Head title="Analytics Events" />
  
  <AdminLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-gray-900">Analytics Events</h1>
          <p class="text-gray-600 mt-1">View detailed analytics events and user interactions</p>
        </div>
        
        <button
          @click="exportEvents"
          class="btn btn-outline flex items-center gap-2"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
          </svg>
          Export
        </button>
      </div>

      <!-- Filters -->
      <Card>
        <CardHeader>
          <CardTitle>Filters</CardTitle>
        </CardHeader>
        <CardContent>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Event Type
              </label>
              <FilterDropdown
                v-model="selectedEventType"
                :options="eventTypeOptions"
                placeholder="All event types"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Date From
              </label>
              <input
                v-model="dateFrom"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                Date To
              </label>
              <input
                v-model="dateTo"
                type="date"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            
            <div>
              <label class="block text-sm font-medium text-gray-700 mb-1">
                User ID
              </label>
              <input
                v-model="selectedUserId"
                type="text"
                placeholder="Enter user ID"
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
              />
            </div>
            
            <div class="flex items-end gap-2">
              <button
                @click="applyFilters"
                class="btn btn-primary flex-1"
              >
                Apply
              </button>
              <button
                @click="clearFilters"
                class="btn btn-outline px-3"
                title="Clear filters"
              >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Events Table -->
      <Card>
        <CardHeader>
          <div class="flex items-center justify-between">
            <CardTitle>
              Events ({{ events.total.toLocaleString() }})
            </CardTitle>
            
            <SearchInput
              v-model="searchTerm"
              placeholder="Search events..."
              class="max-w-sm"
            />
          </div>
        </CardHeader>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
              <thead class="bg-gray-50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Event Type
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    User
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Session
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Data
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Occurred At
                  </th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    Actions
                  </th>
                </tr>
              </thead>
              <tbody class="bg-white divide-y divide-gray-200">
                <tr
                  v-for="event in events.data"
                  :key="event.id"
                  class="hover:bg-gray-50 cursor-pointer"
                  @click="viewEventDetails(event)"
                >
                  <td class="px-6 py-4 whitespace-nowrap">
                    <span
                      :class="[
                        'inline-block px-2 py-1 text-xs font-medium rounded-full',
                        getEventTypeBadgeClass(event.event_type)
                      ]"
                    >
                      {{ event.event_type.replace('_', ' ') }}
                    </span>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap">
                    <div v-if="event.user" class="text-sm">
                      <div class="font-medium text-gray-900">{{ event.user.name }}</div>
                      <div class="text-gray-500">{{ event.user.email }}</div>
                    </div>
                    <div v-else class="text-sm text-gray-500">
                      Guest
                    </div>
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ event.session_id ? event.session_id.substring(0, 8) + '...' : '-' }}
                  </td>
                  <td class="px-6 py-4 text-sm text-gray-500 max-w-xs truncate">
                    {{ formatEventData(event.event_data) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    {{ formatDate(event.occurred_at) }}
                  </td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                    <button
                      @click.stop="viewEventDetails(event)"
                      class="text-blue-600 hover:text-blue-900"
                    >
                      View
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          
          <!-- Empty state -->
          <div v-if="events.data.length === 0" class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
            </svg>
            <h3 class="mt-2 text-sm font-medium text-gray-900">No events found</h3>
            <p class="mt-1 text-sm text-gray-500">
              Try adjusting your filters or date range.
            </p>
          </div>
          
          <!-- Pagination -->
          <div v-if="events.last_page > 1" class="px-6 py-4 border-t border-gray-200">
            <div class="flex items-center justify-between">
              <div class="text-sm text-gray-700">
                Showing {{ (events.current_page - 1) * events.per_page + 1 }} to 
                {{ Math.min(events.current_page * events.per_page, events.total) }} 
                of {{ events.total.toLocaleString() }} results
              </div>
              
              <div class="flex items-center gap-2">
                <button
                  :disabled="events.current_page === 1"
                  @click="router.get(`/admin/analytics/events?page=${events.current_page - 1}`)"
                  class="btn btn-outline btn-sm"
                  :class="{ 'opacity-50 cursor-not-allowed': events.current_page === 1 }"
                >
                  Previous
                </button>
                
                <span class="text-sm text-gray-700">
                  Page {{ events.current_page }} of {{ events.last_page }}
                </span>
                
                <button
                  :disabled="events.current_page === events.last_page"
                  @click="router.get(`/admin/analytics/events?page=${events.current_page + 1}`)"
                  class="btn btn-outline btn-sm"
                  :class="{ 'opacity-50 cursor-not-allowed': events.current_page === events.last_page }"
                >
                  Next
                </button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AdminLayout>
</template>
