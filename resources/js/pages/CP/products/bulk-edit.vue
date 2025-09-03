<template>
  <div>
    <Head title="Bulk Edit Products" />

    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-gray-900">Bulk Edit Products</h1>
        <p class="text-gray-600 mt-1">Edit multiple products at once</p>
      </div>
      <div class="flex items-center space-x-4">
        <Button 
          v-if="selectedProducts.length > 0" 
          variant="outline" 
          @click="clearSelection"
        >
          Clear Selection ({{ selectedProducts.length }})
        </Button>
        <Button @click="applyBulkChanges" :disabled="selectedProducts.length === 0">
          Apply Changes
        </Button>
      </div>
    </div>

    <!-- Filters and Search -->
    <div class="bg-white rounded-lg border border-gray-200 p-6 mb-6">
      <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div>
          <Label for="search">Search Products</Label>
          <Input
            id="search"
            v-model="filters.search"
            placeholder="Search by title, SKU..."
            @input="applyFilters"
          />
        </div>
        <div>
          <Label for="collection">Collection</Label>
          <Select v-model="filters.collection" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Collections" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Collections</SelectItem>
              <SelectItem v-for="collection in collections" :key="collection.id" :value="collection.handle">
                {{ collection.title }}
              </SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div>
          <Label for="status">Status</Label>
          <Select v-model="filters.status" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Statuses" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Statuses</SelectItem>
              <SelectItem value="published">Published</SelectItem>
              <SelectItem value="draft">Draft</SelectItem>
              <SelectItem value="archived">Archived</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div>
          <Label for="inventory">Inventory</Label>
          <Select v-model="filters.inventory" @update:model-value="applyFilters">
            <SelectTrigger>
              <SelectValue placeholder="All Inventory" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="">All Inventory</SelectItem>
              <SelectItem value="in_stock">In Stock</SelectItem>
              <SelectItem value="out_of_stock">Out of Stock</SelectItem>
              <SelectItem value="low_stock">Low Stock</SelectItem>
            </SelectContent>
          </Select>
        </div>
      </div>
    </div>

    <!-- Bulk Edit Form -->
    <div v-if="selectedProducts.length > 0" class="bg-blue-50 rounded-lg border border-blue-200 p-6 mb-6">
      <h2 class="text-lg font-semibold text-blue-900 mb-4">
        Bulk Edit {{ selectedProducts.length }} Product{{ selectedProducts.length !== 1 ? 's' : '' }}
      </h2>
      
      <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <!-- Left Column -->
        <div class="space-y-6">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_status" 
                v-model:checked="bulkEdit.status.enabled"
              />
              <Label for="bulk_status" class="font-medium">Status</Label>
            </div>
            <Select 
              v-model="bulkEdit.status.value" 
              :disabled="!bulkEdit.status.enabled"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select status" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="published">Published</SelectItem>
                <SelectItem value="draft">Draft</SelectItem>
                <SelectItem value="archived">Archived</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_collection" 
                v-model:checked="bulkEdit.collection.enabled"
              />
              <Label for="bulk_collection" class="font-medium">Collection</Label>
            </div>
            <Select 
              v-model="bulkEdit.collection.value" 
              :disabled="!bulkEdit.collection.enabled"
            >
              <SelectTrigger>
                <SelectValue placeholder="Select collection" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem v-for="collection in collections" :key="collection.id" :value="collection.handle">
                  {{ collection.title }}
                </SelectItem>
              </SelectContent>
            </Select>
          </div>

          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_tags" 
                v-model:checked="bulkEdit.tags.enabled"
              />
              <Label for="bulk_tags" class="font-medium">Tags</Label>
            </div>
            <div class="space-y-2">
              <Select 
                v-model="bulkEdit.tags.action" 
                :disabled="!bulkEdit.tags.enabled"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select action" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="add">Add Tags</SelectItem>
                  <SelectItem value="remove">Remove Tags</SelectItem>
                  <SelectItem value="replace">Replace All Tags</SelectItem>
                </SelectContent>
              </Select>
              <Input 
                v-model="bulkEdit.tags.value"
                placeholder="Enter tags separated by commas"
                :disabled="!bulkEdit.tags.enabled"
              />
            </div>
          </div>
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_price" 
                v-model:checked="bulkEdit.price.enabled"
              />
              <Label for="bulk_price" class="font-medium">Price</Label>
            </div>
            <div class="space-y-2">
              <Select 
                v-model="bulkEdit.price.action" 
                :disabled="!bulkEdit.price.enabled"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select action" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="set">Set Price</SelectItem>
                  <SelectItem value="increase_percent">Increase by %</SelectItem>
                  <SelectItem value="decrease_percent">Decrease by %</SelectItem>
                  <SelectItem value="increase_amount">Increase by Amount</SelectItem>
                  <SelectItem value="decrease_amount">Decrease by Amount</SelectItem>
                </SelectContent>
              </Select>
              <div class="relative">
                <Input 
                  v-model="bulkEdit.price.value"
                  type="number"
                  step="0.01"
                  placeholder="0.00"
                  :disabled="!bulkEdit.price.enabled"
                />
                <span 
                  v-if="bulkEdit.price.action?.includes('percent')"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                >
                  %
                </span>
                <span 
                  v-else-if="bulkEdit.price.action && bulkEdit.price.action !== 'set'"
                  class="absolute right-3 top-1/2 transform -translate-y-1/2 text-gray-500"
                >
                  €
                </span>
              </div>
            </div>
          </div>

          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_inventory" 
                v-model:checked="bulkEdit.inventory.enabled"
              />
              <Label for="bulk_inventory" class="font-medium">Inventory</Label>
            </div>
            <div class="space-y-2">
              <Select 
                v-model="bulkEdit.inventory.action" 
                :disabled="!bulkEdit.inventory.enabled"
              >
                <SelectTrigger>
                  <SelectValue placeholder="Select action" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="set">Set Quantity</SelectItem>
                  <SelectItem value="increase">Increase by</SelectItem>
                  <SelectItem value="decrease">Decrease by</SelectItem>
                </SelectContent>
              </Select>
              <Input 
                v-model="bulkEdit.inventory.value"
                type="number"
                placeholder="0"
                :disabled="!bulkEdit.inventory.enabled"
              />
            </div>
          </div>

          <div>
            <div class="flex items-center space-x-2 mb-2">
              <Checkbox 
                id="bulk_seo" 
                v-model:checked="bulkEdit.seo.enabled"
              />
              <Label for="bulk_seo" class="font-medium">SEO Title Template</Label>
            </div>
            <Input 
              v-model="bulkEdit.seo.value"
              placeholder="Use {title} for product title"
              :disabled="!bulkEdit.seo.enabled"
            />
            <p class="text-xs text-gray-500 mt-1">
              Use {title}, {price}, {collection} as variables
            </p>
          </div>
        </div>
      </div>
    </div>

    <!-- Products Table -->
    <div class="bg-white rounded-lg border border-gray-200 overflow-hidden">
      <div class="px-6 py-4 border-b border-gray-200">
        <div class="flex items-center justify-between">
          <h2 class="text-lg font-semibold text-gray-900">Products</h2>
          <div class="flex items-center space-x-2">
            <Checkbox 
              :checked="selectedProducts.length === products.data.length && products.data.length > 0"
              :indeterminate="selectedProducts.length > 0 && selectedProducts.length < products.data.length"
              @update:checked="toggleSelectAll"
            />
            <span class="text-sm text-gray-600">
              {{ selectedProducts.length }} of {{ products.data.length }} selected
            </span>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full">
          <thead class="bg-gray-50">
            <tr>
              <th class="w-12 px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Select
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Product
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Status
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Inventory
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Price
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Collection
              </th>
              <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                Actions
              </th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-200">
            <tr
              v-for="product in products.data"
              :key="product.id"
              :class="{ 'bg-blue-50': selectedProducts.includes(product.id) }"
            >
              <td class="px-6 py-4 whitespace-nowrap">
                <Checkbox 
                  :checked="selectedProducts.includes(product.id)"
                  @update:checked="toggleProductSelection(product.id)"
                />
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center space-x-3">
                  <img 
                    v-if="product.image"
                    :src="product.image" 
                    :alt="product.title"
                    class="w-12 h-12 rounded-md object-cover"
                  />
                  <div class="w-12 h-12 bg-gray-200 rounded-md flex items-center justify-center" v-else>
                    <PhotoIcon class="w-6 h-6 text-gray-400" />
                  </div>
                  <div>
                    <div class="font-medium text-gray-900">{{ product.title }}</div>
                    <div class="text-sm text-gray-500">{{ product.sku }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Badge :variant="getStatusVariant(product.status)">
                  {{ product.status }}
                </Badge>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ product.inventory_quantity || 0 }}</div>
                <div class="text-xs text-gray-500">
                  {{ product.inventory_quantity > 0 ? 'In stock' : 'Out of stock' }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">€{{ product.price }}</div>
                <div v-if="product.compare_at_price" class="text-xs text-gray-500 line-through">
                  €{{ product.compare_at_price }}
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ product.collection?.title || 'No collection' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <Link 
                  :href="route('cp.entries.edit', { collection: product.collection_handle, entry: product.handle })"
                  class="text-blue-600 hover:text-blue-900 text-sm font-medium"
                >
                  Edit
                </Link>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-if="products.links && products.links.length > 3" class="px-6 py-4 bg-gray-50 border-t border-gray-200">
        <Pagination :links="products.links" />
      </div>
    </div>

    <!-- Bulk Edit Confirmation Dialog -->
    <AlertDialog v-model:open="confirmDialog.open">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Confirm Bulk Changes</AlertDialogTitle>
          <AlertDialogDescription>
            You are about to apply changes to {{ selectedProducts.length }} product{{ selectedProducts.length !== 1 ? 's' : '' }}.
            This action cannot be undone.
          </AlertDialogDescription>
        </AlertDialogHeader>
        
        <div class="my-4 p-4 bg-gray-50 rounded-lg">
          <h4 class="font-medium text-gray-900 mb-2">Changes to apply:</h4>
          <ul class="space-y-1 text-sm text-gray-600">
            <li v-if="bulkEdit.status.enabled">
              Set status to: {{ bulkEdit.status.value }}
            </li>
            <li v-if="bulkEdit.collection.enabled">
              Move to collection: {{ getCollectionTitle(bulkEdit.collection.value) }}
            </li>
            <li v-if="bulkEdit.tags.enabled">
              {{ bulkEdit.tags.action }} tags: {{ bulkEdit.tags.value }}
            </li>
            <li v-if="bulkEdit.price.enabled">
              {{ getPriceActionText() }}
            </li>
            <li v-if="bulkEdit.inventory.enabled">
              {{ getInventoryActionText() }}
            </li>
            <li v-if="bulkEdit.seo.enabled">
              Update SEO title template: {{ bulkEdit.seo.value }}
            </li>
          </ul>
        </div>

        <AlertDialogFooter>
          <AlertDialogCancel @click="confirmDialog.open = false">
            Cancel
          </AlertDialogCancel>
          <AlertDialogAction @click="executeBulkChanges">
            Apply Changes
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive, computed } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import { PhotoIcon } from '@heroicons/vue/24/outline'
import Button from '@/components/ui/button'
import Badge from '@/components/ui/badge'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
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
import { useToast } from '@/composables/useToast'
import { debounce } from 'lodash-es'

interface Product {
  id: number
  title: string
  handle: string
  sku: string
  status: string
  price: number
  compare_at_price?: number
  inventory_quantity: number
  image?: string
  collection?: {
    title: string
    handle: string
  }
  collection_handle: string
}

interface Collection {
  id: number
  title: string
  handle: string
}

interface PaginatedProducts {
  data: Product[]
  links: any[]
  meta: any
}

defineProps<{
  products: PaginatedProducts
  collections: Collection[]
}>()

const { toast } = useToast()

const selectedProducts = ref<number[]>([])

const filters = reactive({
  search: '',
  collection: '',
  status: '',
  inventory: '',
})

const bulkEdit = reactive({
  status: { enabled: false, value: '' },
  collection: { enabled: false, value: '' },
  tags: { enabled: false, action: '', value: '' },
  price: { enabled: false, action: '', value: '' },
  inventory: { enabled: false, action: '', value: '' },
  seo: { enabled: false, value: '' },
})

const confirmDialog = reactive({
  open: false,
})

const toggleProductSelection = (productId: number) => {
  const index = selectedProducts.value.indexOf(productId)
  if (index > -1) {
    selectedProducts.value.splice(index, 1)
  } else {
    selectedProducts.value.push(productId)
  }
}

const toggleSelectAll = (checked: boolean) => {
  if (checked) {
    selectedProducts.value = products.data.map(p => p.id)
  } else {
    selectedProducts.value = []
  }
}

const clearSelection = () => {
  selectedProducts.value = []
}

const applyFilters = debounce(() => {
  router.get(route('cp.products.bulk-edit'), filters, {
    preserveState: true,
    preserveScroll: true,
  })
}, 300)

const applyBulkChanges = () => {
  if (selectedProducts.value.length === 0) {
    toast({
      title: 'No products selected',
      description: 'Please select at least one product to edit.',
      variant: 'destructive',
    })
    return
  }

  const hasChanges = Object.values(bulkEdit).some(field => field.enabled)
  if (!hasChanges) {
    toast({
      title: 'No changes to apply',
      description: 'Please enable at least one field to edit.',
      variant: 'destructive',
    })
    return
  }

  confirmDialog.open = true
}

const executeBulkChanges = () => {
  const changes = {}
  
  Object.entries(bulkEdit).forEach(([key, field]) => {
    if (field.enabled) {
      changes[key] = field
    }
  })

  router.post(route('cp.products.bulk-update'), {
    product_ids: selectedProducts.value,
    changes,
  }, {
    onSuccess: () => {
      toast({
        title: 'Bulk changes applied',
        description: `Updated ${selectedProducts.value.length} products successfully.`,
      })
      selectedProducts.value = []
      confirmDialog.open = false
      // Reset bulk edit form
      Object.values(bulkEdit).forEach(field => {
        field.enabled = false
        field.value = ''
        if ('action' in field) field.action = ''
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to apply bulk changes. Please try again.',
        variant: 'destructive',
      })
    },
  })
}

const getStatusVariant = (status: string) => {
  switch (status) {
    case 'published': return 'default'
    case 'draft': return 'secondary'
    case 'archived': return 'outline'
    default: return 'outline'
  }
}

const getCollectionTitle = (handle: string) => {
  return collections.find(c => c.handle === handle)?.title || handle
}

const getPriceActionText = () => {
  const { action, value } = bulkEdit.price
  switch (action) {
    case 'set': return `Set price to €${value}`
    case 'increase_percent': return `Increase price by ${value}%`
    case 'decrease_percent': return `Decrease price by ${value}%`
    case 'increase_amount': return `Increase price by €${value}`
    case 'decrease_amount': return `Decrease price by €${value}`
    default: return 'Update prices'
  }
}

const getInventoryActionText = () => {
  const { action, value } = bulkEdit.inventory
  switch (action) {
    case 'set': return `Set inventory to ${value}`
    case 'increase': return `Increase inventory by ${value}`
    case 'decrease': return `Decrease inventory by ${value}`
    default: return 'Update inventory'
  }
}
</script>
