<template>
  <PageLayout
    title="Product Reviews"
    subtitle="Manage customer reviews and ratings"
    :breadcrumbs="breadcrumbs"
    :actions="pageActions"
    :tabs="tabs"
    :loading="loading"
    :success="success"
    :error="error"
    @tab-change="handleTabChange"
  >
    <!-- Reviews List Tab -->
    <template #tab-reviews>
      <div class="space-y-6">
        <!-- Filters Bar -->
        <div class="bg-white shadow rounded-lg p-6">
          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-6">
            <!-- Search -->
            <div class="lg:col-span-2">
              <label for="search" class="block text-sm font-medium text-gray-700">Search</label>
              <input
                v-model="filters.search"
                type="text"
                id="search"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                placeholder="Search reviews..."
                @input="debounceSearch"
              />
            </div>

            <!-- Product Filter -->
            <div>
              <label for="product" class="block text-sm font-medium text-gray-700">Product</label>
              <select
                v-model="filters.product_id"
                id="product"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">All Products</option>
                <option v-for="product in products" :key="product.id" :value="product.id">
                  {{ product.name }}
                </option>
              </select>
            </div>

            <!-- Rating Filter -->
            <div>
              <label for="rating" class="block text-sm font-medium text-gray-700">Rating</label>
              <select
                v-model="filters.rating"
                id="rating"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">All Ratings</option>
                <option value="5">5 Stars</option>
                <option value="4">4 Stars</option>
                <option value="3">3 Stars</option>
                <option value="2">2 Stars</option>
                <option value="1">1 Star</option>
              </select>
            </div>

            <!-- Status Filter -->
            <div>
              <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
              <select
                v-model="filters.is_approved"
                id="status"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">All Status</option>
                <option value="1">Approved</option>
                <option value="0">Pending</option>
              </select>
            </div>

            <!-- Verified Purchase Filter -->
            <div>
              <label for="verified" class="block text-sm font-medium text-gray-700">Verified</label>
              <select
                v-model="filters.is_verified_purchase"
                id="verified"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                @change="applyFilters"
              >
                <option value="">All Reviews</option>
                <option value="1">Verified Purchase</option>
                <option value="0">Unverified</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Reviews Table -->
        <DataTable
          :data="reviews"
          :columns="reviewColumns"
          :actions="tableActions"
          :row-actions="rowActions"
          :loading="loading"
          selectable
          :pagination="pagination"
          @selection-change="handleSelectionChange"
          @sort-change="handleSortChange"
          @page-change="handlePageChange"
        >
          <!-- Custom column: Customer & Product -->
          <template #column-customer="{ item }">
            <div class="space-y-1">
              <div class="text-sm font-medium text-gray-900">
                {{ item.customer?.name || 'Guest Customer' }}
              </div>
              <div class="text-sm text-gray-500">
                {{ item.product?.name }}
              </div>
            </div>
          </template>

          <!-- Custom column: Rating -->
          <template #column-rating="{ item }">
            <div class="flex items-center">
              <div class="flex">
                <Icon
                  v-for="star in 5"
                  :key="star"
                  name="star"
                  :class="[
                    star <= item.rating ? 'text-yellow-400' : 'text-gray-300',
                    'h-4 w-4'
                  ]"
                  filled
                />
              </div>
              <span class="ml-2 text-sm text-gray-600">{{ item.rating }}/5</span>
            </div>
          </template>

          <!-- Custom column: Review Content -->
          <template #column-content="{ item }">
            <div class="max-w-xs">
              <div class="text-sm font-medium text-gray-900 truncate">
                {{ item.title }}
              </div>
              <div class="text-sm text-gray-500 truncate">
                {{ item.content }}
              </div>
              <div v-if="item.review_media?.length" class="mt-1">
                <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                  <Icon name="photo" class="h-3 w-3 mr-1" />
                  {{ item.review_media.length }} media
                </span>
              </div>
            </div>
          </template>

          <!-- Custom column: Status -->
          <template #column-status="{ item }">
            <div class="space-y-1">
              <span
                :class="[
                  'inline-flex px-2 py-1 text-xs font-medium rounded-full',
                  item.is_approved
                    ? 'bg-green-100 text-green-800'
                    : 'bg-yellow-100 text-yellow-800'
                ]"
              >
                {{ item.is_approved ? 'Approved' : 'Pending' }}
              </span>
              <div v-if="item.is_verified_purchase" class="text-xs">
                <span class="inline-flex items-center rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">
                  <Icon name="shield-check" class="h-3 w-3 mr-1" />
                  Verified
                </span>
              </div>
              <div v-if="item.is_featured" class="text-xs">
                <span class="inline-flex items-center rounded-full bg-purple-100 px-2 py-0.5 text-xs font-medium text-purple-800">
                  <Icon name="star" class="h-3 w-3 mr-1" />
                  Featured
                </span>
              </div>
            </div>
          </template>

          <!-- Custom column: Engagement -->
          <template #column-engagement="{ item }">
            <div class="text-sm space-y-1">
              <div class="flex items-center text-green-600">
                <Icon name="thumb-up" class="h-3 w-3 mr-1" />
                {{ item.helpful_count }}
              </div>
              <div class="flex items-center text-red-600">
                <Icon name="thumb-down" class="h-3 w-3 mr-1" />
                {{ item.unhelpful_count }}
              </div>
            </div>
          </template>

          <!-- Custom column: Reply -->
          <template #column-reply="{ item }">
            <div v-if="item.replied_at" class="text-sm">
              <div class="text-green-600">
                <Icon name="reply" class="h-4 w-4 inline mr-1" />
                Replied
              </div>
              <div class="text-xs text-gray-500">
                {{ formatDate(item.replied_at) }}
              </div>
            </div>
            <div v-else class="text-sm text-gray-400">
              No reply
            </div>
          </template>

          <!-- Custom column: Actions -->
          <template #actions="{ item }">
            <div class="flex items-center space-x-2">
              <button
                @click="viewReview(item)"
                class="text-indigo-600 hover:text-indigo-900 text-sm"
              >
                View
              </button>
              <button
                v-if="!item.is_approved"
                @click="approveReview(item)"
                class="text-green-600 hover:text-green-900 text-sm"
              >
                Approve
              </button>
              <button
                v-if="item.is_approved"
                @click="unapproveReview(item)"
                class="text-yellow-600 hover:text-yellow-900 text-sm"
              >
                Unapprove
              </button>
              <button
                @click="editReview(item)"
                class="text-blue-600 hover:text-blue-900 text-sm"
              >
                Edit
              </button>
              <button
                @click="deleteReview(item)"
                class="text-red-600 hover:text-red-900 text-sm"
              >
                Delete
              </button>
            </div>
          </template>
        </DataTable>
      </div>
    </template>

    <!-- Analytics Tab -->
    <template #tab-analytics>
      <div class="space-y-6">
        <!-- Overview Stats -->
        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total Reviews"
            :value="analytics.total_reviews"
            icon="star"
            color="blue"
          />
          <StatCard
            title="Average Rating"
            :value="analytics.average_rating?.toFixed(1) + '/5'"
            icon="star"
            color="yellow"
          />
          <StatCard
            title="Pending Reviews"
            :value="analytics.pending_reviews"
            icon="clock"
            color="orange"
          />
          <StatCard
            title="Featured Reviews"
            :value="analytics.featured_reviews"
            icon="star"
            color="purple"
          />
        </div>

        <!-- Rating Distribution -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Rating Distribution</h3>
          <div class="space-y-3">
            <div v-for="(count, rating) in analytics.rating_distribution" :key="rating" class="flex items-center">
              <div class="w-20 text-sm text-gray-700">{{ rating }} stars</div>
              <div class="flex-1 mx-4">
                <div class="bg-gray-200 rounded-full h-4">
                  <div
                    class="bg-yellow-400 h-4 rounded-full"
                    :style="{ width: (count / analytics.total_reviews * 100) + '%' }"
                  ></div>
                </div>
              </div>
              <div class="w-16 text-sm text-gray-500 text-right">{{ count }}</div>
            </div>
          </div>
        </div>

        <!-- Recent Reviews Chart -->
        <div class="bg-white shadow rounded-lg p-6">
          <h3 class="text-lg font-medium text-gray-900 mb-4">Reviews Over Time</h3>
          <div class="h-64">
            <!-- Chart implementation would go here -->
            <div class="flex items-center justify-center h-full text-gray-500">
              Chart placeholder - implement with Chart.js or similar
            </div>
          </div>
        </div>
      </div>
    </template>
  </PageLayout>

  <!-- Review Detail Modal -->
  <Modal
    v-model="showReviewModal"
    :title="modalMode === 'view' ? 'Review Details' : modalMode === 'edit' ? 'Edit Review' : 'New Review'"
    size="xl"
  >
    <div v-if="selectedReview" class="space-y-6">
      <!-- Customer & Product Info -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
        <div>
          <label class="block text-sm font-medium text-gray-700">Customer</label>
          <div class="mt-1 text-sm text-gray-900">
            {{ selectedReview.customer?.name || 'Guest Customer' }}
          </div>
        </div>
        <div>
          <label class="block text-sm font-medium text-gray-700">Product</label>
          <div class="mt-1 text-sm text-gray-900">
            {{ selectedReview.product?.name }}
          </div>
        </div>
      </div>

      <!-- Rating -->
      <div>
        <label class="block text-sm font-medium text-gray-700">Rating</label>
        <div class="mt-1 flex items-center">
          <div class="flex">
            <Icon
              v-for="star in 5"
              :key="star"
              name="star"
              :class="[
                star <= (modalMode === 'edit' ? editForm.rating : selectedReview.rating)
                  ? 'text-yellow-400 cursor-pointer'
                  : 'text-gray-300 cursor-pointer',
                'h-6 w-6'
              ]"
              filled
              @click="modalMode === 'edit' && setRating(star)"
            />
          </div>
          <span class="ml-2 text-sm text-gray-600">
            {{ modalMode === 'edit' ? editForm.rating : selectedReview.rating }}/5
          </span>
        </div>
      </div>

      <!-- Title -->
      <div>
        <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
        <input
          v-if="modalMode === 'edit'"
          v-model="editForm.title"
          type="text"
          id="title"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        />
        <div v-else class="mt-1 text-sm text-gray-900">
          {{ selectedReview.title }}
        </div>
      </div>

      <!-- Content -->
      <div>
        <label for="content" class="block text-sm font-medium text-gray-700">Review</label>
        <textarea
          v-if="modalMode === 'edit'"
          v-model="editForm.content"
          id="content"
          rows="4"
          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
        ></textarea>
        <div v-else class="mt-1 text-sm text-gray-900 whitespace-pre-wrap">
          {{ selectedReview.content }}
        </div>
      </div>

      <!-- Media -->
      <div v-if="selectedReview.review_media?.length">
        <label class="block text-sm font-medium text-gray-700">Media</label>
        <div class="mt-2 grid grid-cols-2 gap-4 sm:grid-cols-3">
          <div
            v-for="media in selectedReview.review_media"
            :key="media.id"
            class="relative aspect-square overflow-hidden rounded-lg"
          >
            <img
              v-if="media.media_type === 'image'"
              :src="media.url"
              :alt="media.alt_text"
              class="h-full w-full object-cover"
            />
            <video
              v-else
              :src="media.url"
              class="h-full w-full object-cover"
              controls
            />
          </div>
        </div>
      </div>

      <!-- Status Controls -->
      <div v-if="modalMode === 'edit'" class="grid grid-cols-1 gap-6 sm:grid-cols-3">
        <div class="flex items-center">
          <input
            v-model="editForm.is_approved"
            id="approved"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <label for="approved" class="ml-2 block text-sm text-gray-900">
            Approved
          </label>
        </div>
        <div class="flex items-center">
          <input
            v-model="editForm.is_featured"
            id="featured"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <label for="featured" class="ml-2 block text-sm text-gray-900">
            Featured
          </label>
        </div>
        <div class="flex items-center">
          <input
            v-model="editForm.is_verified_purchase"
            id="verified"
            type="checkbox"
            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
          />
          <label for="verified" class="ml-2 block text-sm text-gray-900">
            Verified Purchase
          </label>
        </div>
      </div>

      <!-- Reply Section -->
      <div class="border-t pt-6">
        <div class="space-y-4">
          <label class="block text-sm font-medium text-gray-700">Admin Reply</label>
          
          <div v-if="selectedReview.reply_content && modalMode === 'view'">
            <div class="bg-gray-50 rounded-lg p-4">
              <div class="text-sm text-gray-900 whitespace-pre-wrap">
                {{ selectedReview.reply_content }}
              </div>
              <div v-if="selectedReview.replied_at" class="mt-2 text-xs text-gray-500">
                Replied on {{ formatDate(selectedReview.replied_at) }}
              </div>
            </div>
          </div>

          <div v-if="modalMode === 'edit'">
            <textarea
              v-model="editForm.reply_content"
              rows="3"
              class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
              placeholder="Write your reply to this review..."
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <template #actions>
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="showReviewModal = false"
          class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Cancel
        </button>
        <button
          v-if="modalMode === 'edit'"
          type="button"
          @click="saveReview"
          :disabled="saving"
          class="rounded-md border border-transparent bg-indigo-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
        >
          {{ saving ? 'Saving...' : 'Save Changes' }}
        </button>
      </div>
    </template>
  </Modal>

  <!-- Delete Confirmation Modal -->
  <Modal
    v-model="showDeleteModal"
    title="Delete Review"
    size="sm"
  >
    <div class="space-y-4">
      <p class="text-sm text-gray-700">
        Are you sure you want to delete this review? This action cannot be undone.
      </p>
      <div v-if="reviewToDelete" class="bg-gray-50 rounded-lg p-3">
        <div class="text-sm font-medium text-gray-900">{{ reviewToDelete.title }}</div>
        <div class="text-sm text-gray-500">by {{ reviewToDelete.customer?.name || 'Guest Customer' }}</div>
      </div>
    </div>

    <template #actions>
      <div class="flex justify-end space-x-3">
        <button
          type="button"
          @click="showDeleteModal = false"
          class="rounded-md border border-gray-300 bg-white py-2 px-4 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
        >
          Cancel
        </button>
        <button
          type="button"
          @click="confirmDelete"
          :disabled="deleting"
          class="rounded-md border border-transparent bg-red-600 py-2 px-4 text-sm font-medium text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
        >
          {{ deleting ? 'Deleting...' : 'Delete Review' }}
        </button>
      </div>
    </template>
  </Modal>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'
import { usePage } from '@inertiajs/vue3'
import { debounce } from 'lodash'

// Components
import PageLayout from '@/components/layouts/PageLayout.vue'
import DataTable from '@/components/ui/DataTable.vue'
import Modal from '@/components/ui/Modal.vue'
import StatCard from '@/components/ui/StatCard.vue'
import Icon from '@/components/ui/Icon.vue'

// Composables
import { useApi } from '@/composables/useApi'
import { useNotification } from '@/composables/useNotification'

// Data
const loading = ref(false)
const success = ref('')
const error = ref('')
const showReviewModal = ref(false)
const showDeleteModal = ref(false)
const modalMode = ref('view') // 'view', 'edit', 'create'
const selectedReview = ref(null)
const reviewToDelete = ref(null)
const saving = ref(false)
const deleting = ref(false)

const reviews = ref([])
const products = ref([])
const pagination = ref({})
const analytics = ref({
  total_reviews: 0,
  average_rating: 0,
  pending_reviews: 0,
  featured_reviews: 0,
  rating_distribution: {}
})

const filters = reactive({
  search: '',
  product_id: '',
  rating: '',
  is_approved: '',
  is_verified_purchase: '',
  sort_by: 'created_at',
  sort_direction: 'desc',
  page: 1,
  per_page: 20
})

const editForm = reactive({
  rating: 5,
  title: '',
  content: '',
  is_approved: false,
  is_featured: false,
  is_verified_purchase: false,
  reply_content: ''
})

// Computed
const breadcrumbs = computed(() => [
  { name: 'Products', href: '/cp/products' },
  { name: 'Reviews', href: '/cp/products/reviews' }
])

const pageActions = computed(() => [
  {
    label: 'Export Reviews',
    action: () => exportReviews(),
    icon: 'download'
  }
])

const tabs = computed(() => [
  { key: 'reviews', label: 'Reviews', count: analytics.value.total_reviews },
  { key: 'analytics', label: 'Analytics' }
])

const tableActions = computed(() => [
  {
    label: 'Bulk Approve',
    action: () => bulkApprove(),
    icon: 'check'
  },
  {
    label: 'Bulk Delete',
    action: () => bulkDelete(),
    icon: 'trash',
    destructive: true
  }
])

const rowActions = computed(() => [
  {
    label: 'View',
    action: (item) => viewReview(item),
    icon: 'eye'
  },
  {
    label: 'Edit',
    action: (item) => editReview(item),
    icon: 'pencil'
  },
  {
    label: 'Delete',
    action: (item) => deleteReview(item),
    icon: 'trash',
    destructive: true
  }
])

const reviewColumns = computed(() => [
  {
    key: 'customer',
    label: 'Customer & Product',
    sortable: false
  },
  {
    key: 'rating',
    label: 'Rating',
    sortable: true
  },
  {
    key: 'content',
    label: 'Review',
    sortable: false
  },
  {
    key: 'status',
    label: 'Status',
    sortable: false
  },
  {
    key: 'engagement',
    label: 'Engagement',
    sortable: false
  },
  {
    key: 'reply',
    label: 'Reply',
    sortable: false
  },
  {
    key: 'created_at',
    label: 'Date',
    sortable: true
  }
])

// API
const { get, post, put, delete: deleteApi } = useApi()
const { showNotification } = useNotification()

// Methods
const loadReviews = async () => {
  loading.value = true
  try {
    const params = new URLSearchParams()
    Object.entries(filters).forEach(([key, value]) => {
      if (value !== '' && value !== null) {
        params.append(key, value)
      }
    })

    const response = await get(`/api/admin/reviews?${params.toString()}`)
    reviews.value = response.data
    pagination.value = response.meta
  } catch (err) {
    error.value = 'Failed to load reviews'
  } finally {
    loading.value = false
  }
}

const loadProducts = async () => {
  try {
    const response = await get('/api/admin/products?limit=1000')
    products.value = response.data
  } catch (err) {
    console.error('Failed to load products:', err)
  }
}

const loadAnalytics = async () => {
  try {
    const response = await get('/api/admin/reviews/analytics')
    analytics.value = response.data
  } catch (err) {
    console.error('Failed to load analytics:', err)
  }
}

const debounceSearch = debounce(() => {
  filters.page = 1
  loadReviews()
}, 300)

const applyFilters = () => {
  filters.page = 1
  loadReviews()
}

const handleTabChange = (tab) => {
  if (tab === 'analytics') {
    loadAnalytics()
  }
}

const handleSelectionChange = (selected) => {
  // Handle bulk selection
}

const handleSortChange = (sort) => {
  filters.sort_by = sort.column
  filters.sort_direction = sort.direction
  loadReviews()
}

const handlePageChange = (page) => {
  filters.page = page
  loadReviews()
}

const viewReview = (review) => {
  selectedReview.value = review
  modalMode.value = 'view'
  showReviewModal.value = true
}

const editReview = (review) => {
  selectedReview.value = review
  modalMode.value = 'edit'
  
  // Populate edit form
  editForm.rating = review.rating
  editForm.title = review.title
  editForm.content = review.content
  editForm.is_approved = review.is_approved
  editForm.is_featured = review.is_featured
  editForm.is_verified_purchase = review.is_verified_purchase
  editForm.reply_content = review.reply_content || ''
  
  showReviewModal.value = true
}

const saveReview = async () => {
  saving.value = true
  try {
    await put(`/api/admin/reviews/${selectedReview.value.id}`, editForm)
    showNotification('Review updated successfully', 'success')
    showReviewModal.value = false
    loadReviews()
  } catch (err) {
    error.value = 'Failed to update review'
  } finally {
    saving.value = false
  }
}

const setRating = (rating) => {
  editForm.rating = rating
}

const approveReview = async (review) => {
  try {
    await put(`/api/admin/reviews/${review.id}/approve`)
    showNotification('Review approved', 'success')
    loadReviews()
  } catch (err) {
    error.value = 'Failed to approve review'
  }
}

const unapproveReview = async (review) => {
  try {
    await put(`/api/admin/reviews/${review.id}/unapprove`)
    showNotification('Review unapproved', 'success')
    loadReviews()
  } catch (err) {
    error.value = 'Failed to unapprove review'
  }
}

const deleteReview = (review) => {
  reviewToDelete.value = review
  showDeleteModal.value = true
}

const confirmDelete = async () => {
  deleting.value = true
  try {
    await deleteApi(`/api/admin/reviews/${reviewToDelete.value.id}`)
    showNotification('Review deleted successfully', 'success')
    showDeleteModal.value = false
    loadReviews()
  } catch (err) {
    error.value = 'Failed to delete review'
  } finally {
    deleting.value = false
  }
}

const bulkApprove = async () => {
  // Implement bulk approve logic
}

const bulkDelete = async () => {
  // Implement bulk delete logic
}

const exportReviews = async () => {
  try {
    window.open('/api/admin/reviews/export', '_blank')
  } catch (err) {
    error.value = 'Failed to export reviews'
  }
}

const formatDate = (date) => {
  return new Date(date).toLocaleDateString('en-US', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Lifecycle
onMounted(() => {
  loadReviews()
  loadProducts()
})
</script>
