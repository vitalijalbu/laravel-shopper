<template>
  <page 
    title="Collections"
    subtitle="Manage your content collections and structures"
    :breadcrumbs="breadcrumbs"
  >
    <template #actions>
      <button 
        class="btn btn-primary"
        @click="showCreateModal = true"
        v-if="canCreate"
      >
        <icon name="plus" class="btn-icon" />
        Create Collection
      </button>
    </template>

    <div class="collections-grid">
      <!-- E-commerce Collections -->
      <div class="collection-section">
        <h2 class="section-title">E-commerce</h2>
        <div class="collections-row">
          <collection-card
            v-for="collection in ecommerceCollections"
            :key="collection.handle"
            :collection="collection"
            @edit="editCollection"
            @delete="deleteCollection"
            @view-entries="viewEntries"
          />
        </div>
      </div>

      <!-- Content Collections -->
      <div v-if="contentCollections.length > 0" class="collection-section">
        <h2 class="section-title">Content</h2>
        <div class="collections-row">
          <collection-card
            v-for="collection in contentCollections"
            :key="collection.handle"
            :collection="collection"
            @edit="editCollection"
            @delete="deleteCollection"
            @view-entries="viewEntries"
          />
        </div>
      </div>

      <!-- Custom Collections -->
      <div v-if="customCollections.length > 0" class="collection-section">
        <h2 class="section-title">Custom</h2>
        <div class="collections-row">
          <collection-card
            v-for="collection in customCollections"
            :key="collection.handle"
            :collection="collection"
            @edit="editCollection"
            @delete="deleteCollection"
            @view-entries="viewEntries"
          />
        </div>
      </div>

      <!-- Empty State -->
      <div v-if="collections.length === 0" class="empty-state">
        <div class="empty-icon">
          <icon name="collection" class="empty-state-icon" />
        </div>
        <h3 class="empty-title">No collections yet</h3>
        <p class="empty-description">
          Collections help you organize and manage different types of content.
        </p>
        <button 
          class="btn btn-primary"
          @click="showCreateModal = true"
          v-if="canCreate"
        >
          Create your first collection
        </button>
      </div>
    </div>

    <!-- Create/Edit Collection Modal -->
    <modal
      v-if="showCreateModal || showEditModal"
      :title="editingCollection ? 'Edit Collection' : 'Create Collection'"
      @close="closeModal"
    >
      <collection-form
        :collection="editingCollection"
        :sites="sites"
        @save="saveCollection"
        @cancel="closeModal"
      />
    </modal>

    <!-- Delete Confirmation Modal -->
    <confirm-modal
      v-if="showDeleteModal"
      :title="`Delete ${deletingCollection?.title}?`"
      :message="`This will permanently delete the collection and all its entries. This action cannot be undone.`"
      danger
      @confirm="confirmDelete"
      @cancel="showDeleteModal = false"
    />
  </page>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { useShopperStore } from '../stores/shopper'
import Page from '../components/page.vue'
import CollectionCard from '../components/collection-card.vue'
import Modal from '../components/modal.vue'

const shopperStore = useShopperStore()

// State
const showCreateModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const editingCollection = ref(null)
const deletingCollection = ref(null)
const loading = ref(true)

// Props from route or parent
const sites = ref([
  { handle: 'default', name: 'Main Site' },
  { handle: 'blog', name: 'Blog' },
  { handle: 'shop', name: 'Shop' }
])

// Computed
const collections = computed(() => shopperStore.collections)

const ecommerceCollections = computed(() => {
  return collections.value.filter(c => 
    ['products', 'orders', 'customers', 'categories'].includes(c.handle)
  )
})

const contentCollections = computed(() => {
  return collections.value.filter(c => 
    ['pages', 'blog', 'news'].includes(c.handle)
  )
})

const customCollections = computed(() => {
  return collections.value.filter(c => 
    !['products', 'orders', 'customers', 'categories', 'pages', 'blog', 'news'].includes(c.handle)
  )
})

const canCreate = computed(() => {
  return shopperStore.canManage('collections')
})

const breadcrumbs = computed(() => [
  { title: 'Control Panel', url: '/cp' },
  { title: 'Collections', url: '/cp/collections' }
])

// Methods
const editCollection = (collection) => {
  editingCollection.value = collection
  showEditModal.value = true
}

const deleteCollection = (collection) => {
  deletingCollection.value = collection
  showDeleteModal.value = true
}

const viewEntries = (collection) => {
  router.visit(route('cp.collections.entries.index', { collection: collection.handle }))
}

const saveCollection = async (collectionData) => {
  try {
    shopperStore.setLoading(true)
    
    if (editingCollection.value) {
      shopperStore.updateCollection(editingCollection.value.handle, collectionData)
      shopperStore.addToast('Collection updated successfully', 'success')
    } else {
      shopperStore.createCollection(collectionData)
      shopperStore.addToast('Collection created successfully', 'success')
    }
    
    closeModal()
  } catch (error) {
    shopperStore.addError(error)
    shopperStore.addToast('Failed to save collection', 'error')
  } finally {
    shopperStore.setLoading(false)
  }
}

const confirmDelete = async () => {
  try {
    shopperStore.setLoading(true)
    shopperStore.deleteCollection(deletingCollection.value.handle)
    shopperStore.addToast('Collection deleted successfully', 'success')
    showDeleteModal.value = false
  } catch (error) {
    shopperStore.addError(error)
    shopperStore.addToast('Failed to delete collection', 'error')
  } finally {
    shopperStore.setLoading(false)
  }
}

const closeModal = () => {
  showCreateModal.value = false
  showEditModal.value = false
  editingCollection.value = null
}

// Load collections on mount
onMounted(async () => {
  try {
    await shopperStore.fetchCollections()
    
    // Create default e-commerce collections if they don't exist
    const defaultCollections = [
      {
        handle: 'products',
        title: 'Products',
        type: 'ecommerce',
        icon: 'box',
        blueprint: 'product',
        sites: ['default', 'shop'],
        route: '/products/{slug}',
        sort_by: 'title',
        sort_direction: 'asc'
      },
      {
        handle: 'orders',
        title: 'Orders',
        type: 'ecommerce',
        icon: 'document-text',
        blueprint: 'order',
        sites: ['default'],
        sort_by: 'created_at',
        sort_direction: 'desc'
      },
      {
        handle: 'customers',
        title: 'Customers',
        type: 'ecommerce',
        icon: 'users',
        blueprint: 'customer',
        sites: ['default'],
        sort_by: 'name',
        sort_direction: 'asc'
      },
      {
        handle: 'categories',
        title: 'Categories',
        type: 'ecommerce',
        icon: 'folder',
        blueprint: 'category',
        sites: ['default', 'shop'],
        route: '/categories/{slug}',
        sort_by: 'title',
        sort_direction: 'asc'
      }
    ]

    for (const collectionData of defaultCollections) {
      if (!shopperStore.getCollection(collectionData.handle)) {
        shopperStore.createCollection(collectionData)
      }
    }
  } catch (error) {
    shopperStore.addError(error)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.collections-grid {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.collection-section {
  margin-bottom: 3rem;
}

.section-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 1rem;
  padding-bottom: 0.5rem;
  border-bottom: 2px solid #e5e7eb;
}

.collections-row {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
  gap: 1.5rem;
}

.empty-state {
  text-align: center;
  padding: 4rem 2rem;
  background: white;
  border-radius: 0.5rem;
  border: 2px dashed #d1d5db;
}

.empty-icon {
  margin: 0 auto 1.5rem;
  width: 64px;
  height: 64px;
  background: #f3f4f6;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.empty-state-icon {
  width: 32px;
  height: 32px;
  color: #9ca3af;
}

.empty-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: #1f2937;
  margin-bottom: 0.5rem;
}

.empty-description {
  color: #6b7280;
  margin-bottom: 2rem;
  max-width: 400px;
  margin-left: auto;
  margin-right: auto;
}

.btn-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.5rem;
}
</style>
