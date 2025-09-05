<template>
  <div>
    <Head :title="`${customer.name} - Addresses`" />

    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center space-x-2 text-sm text-gray-500 mb-2">
          <Link :href="route('cp.customers.index')" class="hover:text-gray-700">
            Customers
          </Link>
          <ChevronRightIcon class="w-4 h-4" />
          <Link :href="route('cp.customers.show', customer.id)" class="hover:text-gray-700">
            {{ customer.name }}
          </Link>
          <ChevronRightIcon class="w-4 h-4" />
          <span>Addresses</span>
        </div>
        <h1 class="text-3xl font-bold text-gray-900">Customer Addresses</h1>
        <p class="text-gray-600 mt-1">Manage {{ customer.name }}'s shipping and billing addresses</p>
      </div>
      <Button @click="createAddress" class="inline-flex items-center">
        <PlusIcon class="w-4 h-4 mr-2" />
        Add Address
      </Button>
    </div>

    <div class="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="address in addresses"
        :key="address.id"
        class="bg-white rounded-lg border border-gray-200 p-6 hover:shadow-md transition-shadow"
      >
        <div class="flex items-start justify-between mb-4">
          <div>
            <div class="flex items-center space-x-2 mb-2">
              <h3 class="font-semibold text-gray-900">{{ address.full_name }}</h3>
              <Badge v-if="address.is_default" variant="default">
                Default {{ address.type }}
              </Badge>
              <Badge v-else :variant="getAddressTypeVariant(address.type)">
                {{ address.type }}
              </Badge>
            </div>
            <p v-if="address.company" class="text-sm text-gray-600 mb-1">{{ address.company }}</p>
          </div>
          <DropdownMenu>
            <DropdownMenuTrigger asChild>
              <Button variant="ghost" size="sm">
                <EllipsisVerticalIcon class="w-4 h-4" />
              </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
              <DropdownMenuItem @click="editAddress(address)">
                <PencilIcon class="w-4 h-4 mr-2" />
                Edit
              </DropdownMenuItem>
              <DropdownMenuItem 
                v-if="!address.is_default"
                @click="setAsDefault(address)"
              >
                <StarIcon class="w-4 h-4 mr-2" />
                Set as Default
              </DropdownMenuItem>
              <DropdownMenuSeparator />
              <DropdownMenuItem 
                @click="deleteAddress(address)"
                class="text-red-600 focus:text-red-600"
              >
                <TrashIcon class="w-4 h-4 mr-2" />
                Delete
              </DropdownMenuItem>
            </DropdownMenuContent>
          </DropdownMenu>
        </div>

        <div class="space-y-1 text-sm text-gray-600">
          <p>{{ address.address_line_1 }}</p>
          <p v-if="address.address_line_2">{{ address.address_line_2 }}</p>
          <p>{{ address.city }}, {{ address.state }} {{ address.postal_code }}</p>
          <p>{{ address.country_code }}</p>
          <p v-if="address.phone" class="flex items-center mt-2">
            <PhoneIcon class="w-4 h-4 mr-1" />
            {{ address.phone }}
          </p>
        </div>
      </div>

      <!-- Empty state -->
      <div 
        v-if="addresses.length === 0"
        class="col-span-full bg-gray-50 rounded-lg border-2 border-dashed border-gray-300 p-12 text-center"
      >
        <MapPinIcon class="w-16 h-16 mx-auto text-gray-400 mb-4" />
        <h3 class="text-lg font-medium text-gray-900 mb-2">No addresses yet</h3>
        <p class="text-gray-600 mb-6">
          Add shipping and billing addresses for this customer.
        </p>
        <Button @click="createAddress">
          <PlusIcon class="w-4 h-4 mr-2" />
          Add First Address
        </Button>
      </div>
    </div>

    <!-- Address Form Modal -->
    <Dialog v-model:open="addressModal.open">
      <DialogContent class="max-w-2xl">
        <DialogHeader>
          <DialogTitle>
            {{ addressModal.isEditing ? 'Edit Address' : 'Add New Address' }}
          </DialogTitle>
          <DialogDescription>
            {{ addressModal.isEditing ? 'Update the address information below.' : 'Enter the address information below.' }}
          </DialogDescription>
        </DialogHeader>

        <form @submit.prevent="saveAddress" class="space-y-6">
          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <Label for="type">Address Type</Label>
              <Select v-model="addressForm.type" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select address type" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="shipping">Shipping</SelectItem>
                  <SelectItem value="billing">Billing</SelectItem>
                  <SelectItem value="both">Both</SelectItem>
                </SelectContent>
              </Select>
            </div>

            <div class="flex items-center space-x-2">
              <Checkbox 
                id="is_default" 
                v-model:checked="addressForm.is_default"
              />
              <Label for="is_default">Set as default</Label>
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <Label for="first_name">First Name</Label>
              <Input 
                id="first_name"
                v-model="addressForm.first_name"
                required
              />
            </div>
            <div>
              <Label for="last_name">Last Name</Label>
              <Input 
                id="last_name"
                v-model="addressForm.last_name"
                required
              />
            </div>
          </div>

          <div>
            <Label for="company">Company (Optional)</Label>
            <Input 
              id="company"
              v-model="addressForm.company"
            />
          </div>

          <div>
            <Label for="address_line_1">Address Line 1</Label>
            <Input 
              id="address_line_1"
              v-model="addressForm.address_line_1"
              required
            />
          </div>

          <div>
            <Label for="address_line_2">Address Line 2 (Optional)</Label>
            <Input 
              id="address_line_2"
              v-model="addressForm.address_line_2"
            />
          </div>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
            <div>
              <Label for="city">City</Label>
              <Input 
                id="city"
                v-model="addressForm.city"
                required
              />
            </div>
            <div>
              <Label for="state">State/Province</Label>
              <Input 
                id="state"
                v-model="addressForm.state"
              />
            </div>
            <div>
              <Label for="postal_code">Postal Code</Label>
              <Input 
                id="postal_code"
                v-model="addressForm.postal_code"
                required
              />
            </div>
          </div>

          <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
            <div>
              <Label for="country_code">Country</Label>
              <Select v-model="addressForm.country_code" required>
                <SelectTrigger>
                  <SelectValue placeholder="Select country" />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="US">United States</SelectItem>
                  <SelectItem value="CA">Canada</SelectItem>
                  <SelectItem value="GB">United Kingdom</SelectItem>
                  <SelectItem value="IT">Italy</SelectItem>
                  <SelectItem value="FR">France</SelectItem>
                  <SelectItem value="DE">Germany</SelectItem>
                  <!-- Add more countries as needed -->
                </SelectContent>
              </Select>
            </div>
            <div>
              <Label for="phone">Phone (Optional)</Label>
              <Input 
                id="phone"
                v-model="addressForm.phone"
                type="tel"
              />
            </div>
          </div>

          <DialogFooter>
            <Button type="button" variant="outline" @click="addressModal.open = false">
              Cancel
            </Button>
            <Button type="submit" :disabled="isSubmitting">
              {{ isSubmitting ? 'Saving...' : 'Save Address' }}
            </Button>
          </DialogFooter>
        </form>
      </DialogContent>
    </Dialog>

    <!-- Delete Confirmation Dialog -->
    <AlertDialog v-model:open="deleteDialog.open">
      <AlertDialogContent>
        <AlertDialogHeader>
          <AlertDialogTitle>Delete Address</AlertDialogTitle>
          <AlertDialogDescription>
            Are you sure you want to delete this address? This action cannot be undone.
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
            Delete Address
          </AlertDialogAction>
        </AlertDialogFooter>
      </AlertDialogContent>
    </AlertDialog>
  </div>
</template>

<script setup lang="ts">
import { ref, reactive } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import { route } from 'ziggy-js'
import {
  PlusIcon,
  PencilIcon,
  TrashIcon,
  EllipsisVerticalIcon,
  ChevronRightIcon,
  MapPinIcon,
  PhoneIcon,
  StarIcon,
} from '@heroicons/vue/24/outline'
import Button from '@/components/ui/button'
import Badge from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuTrigger,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu'
import {
  Dialog,
  DialogContent,
  DialogHeader,
  DialogTitle,
  DialogDescription,
  DialogFooter,
} from '@/components/ui/dialog'
import {
  AlertDialog,
  AlertDialogContent,
  AlertDialogHeader,
  AlertDialogTitle,
  AlertDialogDescription,
  AlertDialogFooter,
  AlertDialogCancel,
  AlertDialogAction,
} from '@/components/ui/alert-dialog'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Checkbox } from '@/components/ui/checkbox'
import { useToast } from '@/composables/useToast'

interface Customer {
  id: number
  name: string
  email: string
}

interface Address {
  id: number
  type: string
  first_name: string
  last_name: string
  company?: string
  address_line_1: string
  address_line_2?: string
  city: string
  state?: string
  postal_code: string
  country_code: string
  phone?: string
  is_default: boolean
  full_name: string
  formatted_address: string
}

defineProps<{
  customer: Customer
  addresses: Address[]
}>()

const { toast } = useToast()

const isSubmitting = ref(false)

const addressModal = reactive({
  open: false,
  isEditing: false,
  editingId: null as number | null,
})

const addressForm = reactive({
  type: '',
  first_name: '',
  last_name: '',
  company: '',
  address_line_1: '',
  address_line_2: '',
  city: '',
  state: '',
  postal_code: '',
  country_code: '',
  phone: '',
  is_default: false,
})

const deleteDialog = reactive({
  open: false,
  address: null as Address | null,
})

const createAddress = () => {
  resetForm()
  addressModal.open = true
  addressModal.isEditing = false
}

const editAddress = (address: Address) => {
  Object.assign(addressForm, {
    type: address.type,
    first_name: address.first_name,
    last_name: address.last_name,
    company: address.company || '',
    address_line_1: address.address_line_1,
    address_line_2: address.address_line_2 || '',
    city: address.city,
    state: address.state || '',
    postal_code: address.postal_code,
    country_code: address.country_code,
    phone: address.phone || '',
    is_default: address.is_default,
  })

  addressModal.open = true
  addressModal.isEditing = true
  addressModal.editingId = address.id
}

const resetForm = () => {
  Object.assign(addressForm, {
    type: '',
    first_name: '',
    last_name: '',
    company: '',
    address_line_1: '',
    address_line_2: '',
    city: '',
    state: '',
    postal_code: '',
    country_code: '',
    phone: '',
    is_default: false,
  })
  addressModal.editingId = null
}

const saveAddress = () => {
  isSubmitting.value = true

  const url = addressModal.isEditing
    ? route('cp.customers.addresses.update', { customer: customer.id, address: addressModal.editingId })
    : route('cp.customers.addresses.store', customer.id)

  const method = addressModal.isEditing ? 'put' : 'post'

  router[method](url, addressForm, {
    onSuccess: () => {
      toast({
        title: 'Success',
        description: `Address ${addressModal.isEditing ? 'updated' : 'created'} successfully.`,
      })
      addressModal.open = false
      resetForm()
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to save address. Please try again.',
        variant: 'destructive',
      })
    },
    onFinish: () => {
      isSubmitting.value = false
    },
  })
}

const deleteAddress = (address: Address) => {
  deleteDialog.address = address
  deleteDialog.open = true
}

const confirmDelete = () => {
  if (!deleteDialog.address) return

  router.delete(route('cp.customers.addresses.destroy', { customer: customer.id, address: deleteDialog.address.id }), {
    onSuccess: () => {
      toast({
        title: 'Address deleted',
        description: 'The address has been deleted successfully.',
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to delete address. Please try again.',
        variant: 'destructive',
      })
    },
    onFinish: () => {
      deleteDialog.open = false
      deleteDialog.address = null
    },
  })
}

const setAsDefault = (address: Address) => {
  router.post(route('cp.customers.addresses.set-default', { customer: customer.id, address: address.id }), {}, {
    onSuccess: () => {
      toast({
        title: 'Default address updated',
        description: 'The address has been set as default.',
      })
    },
    onError: () => {
      toast({
        title: 'Error',
        description: 'Failed to set default address. Please try again.',
        variant: 'destructive',
      })
    },
  })
}

const getAddressTypeVariant = (type: string) => {
  switch (type) {
    case 'shipping': return 'secondary'
    case 'billing': return 'outline'
    case 'both': return 'default'
    default: return 'outline'
  }
}
</script>
