<script setup>
import { ref } from 'vue'
import { Head, router } from '@inertiajs/vue3'
import { Form, FormInput, FormTextarea, FormSelect, FormCheckbox } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'
import { Sheet, SheetHeader, SheetFooter, SheetTitle, SheetDescription } from '@/components/ui/sheet'

const props = defineProps({
  page: Object,
  customer: Object,
  customerGroups: Array,
})

const showAddressDialog = ref(false)
const editingAddress = ref(null)
const addressForm = ref({
  first_name: '',
  last_name: '',
  company: '',
  address_1: '',
  address_2: '',
  city: '',
  state: '',
  zip: '',
  country: '',
  phone: '',
  is_default: false,
})

const handleSuccess = (response) => {
  console.log('Customer updated:', response)
}

const handleError = (errors) => {
  console.error('Validation errors:', errors)
}

const deleteCustomer = () => {
  if (confirm('Are you sure you want to delete this customer?')) {
    router.delete(`/cp/customers/${props.customer.id}`, {
      onSuccess: () => {
        router.visit('/cp/customers')
      }
    })
  }
}

const openAddressDialog = (address = null) => {
  if (address) {
    editingAddress.value = address
    addressForm.value = { ...address }
  } else {
    editingAddress.value = null
    addressForm.value = {
      first_name: props.customer.first_name,
      last_name: props.customer.last_name,
      company: '',
      address_1: '',
      address_2: '',
      city: '',
      state: '',
      zip: '',
      country: 'US',
      phone: props.customer.phone || '',
      is_default: false,
    }
  }
  showAddressDialog.value = true
}

const saveAddress = () => {
  const url = editingAddress.value
    ? `/cp/customers/${props.customer.id}/addresses/${editingAddress.value.id}`
    : `/cp/customers/${props.customer.id}/addresses`

  const method = editingAddress.value ? 'put' : 'post'

  router[method](url, addressForm.value, {
    preserveScroll: true,
    onSuccess: () => {
      showAddressDialog.value = false
    }
  })
}

const deleteAddress = (addressId) => {
  if (confirm('Are you sure you want to delete this address?')) {
    router.delete(`/cp/customers/${props.customer.id}/addresses/${addressId}`, {
      preserveScroll: true
    })
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <Head :title="page.title" />

    <!-- Header -->
    <div class="bg-white border-b border-gray-200">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-2xl font-semibold text-gray-900">{{ page.title }}</h1>
            <nav class="mt-2 flex text-sm text-gray-500">
              <a href="/cp" class="hover:text-gray-700">Dashboard</a>
              <span class="mx-2">/</span>
              <a href="/cp/customers" class="hover:text-gray-700">Customers</a>
              <span class="mx-2">/</span>
              <span class="text-gray-900">{{ customer.first_name }} {{ customer.last_name }}</span>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Form
        id="customer-form"
        method="put"
        :url="`/cp/customers/${customer.id}`"
        :initial-values="{
          first_name: customer.first_name || '',
          last_name: customer.last_name || '',
          email: customer.email || '',
          phone: customer.phone || '',
          is_active: customer.is_active ?? true,
          accepts_marketing: customer.accepts_marketing ?? false,
          customer_group_id: customer.customer_group_id || '',
          tax_exempt: customer.tax_exempt ?? false,
          notes: customer.notes || '',
        }"
        preserve-scroll
        :on-success="handleSuccess"
        :on-error="handleError"
      >
        <template #default="{ form }">
          <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content Area -->
            <div class="lg:col-span-2 space-y-6">
              <Tabs default-value="general" class="w-full">
                <TabsList class="grid w-full grid-cols-4">
                  <TabsTrigger value="general">General</TabsTrigger>
                  <TabsTrigger value="addresses">Addresses</TabsTrigger>
                  <TabsTrigger value="orders">Orders</TabsTrigger>
                  <TabsTrigger value="notes">Notes</TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Customer Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <div class="grid grid-cols-2 gap-4">
                        <FormInput
                          name="first_name"
                          label="First Name"
                          placeholder="John"
                          required
                          autocomplete="given-name"
                        />

                        <FormInput
                          name="last_name"
                          label="Last Name"
                          placeholder="Doe"
                          required
                          autocomplete="family-name"
                        />
                      </div>

                      <FormInput
                        name="email"
                        label="Email"
                        type="email"
                        placeholder="john.doe@example.com"
                        required
                        autocomplete="email"
                      />

                      <FormInput
                        name="phone"
                        label="Phone"
                        type="tel"
                        placeholder="+1 (555) 123-4567"
                        autocomplete="tel"
                      />
                    </CardContent>
                  </Card>

                  <Card>
                    <CardHeader>
                      <CardTitle>Marketing</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <FormCheckbox
                        name="accepts_marketing"
                        label="Customer accepts marketing"
                        description="Customer has agreed to receive marketing emails"
                      />
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Addresses Tab -->
                <TabsContent value="addresses" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader class="flex flex-row items-center justify-between">
                      <CardTitle>Addresses</CardTitle>
                      <Button @click="openAddressDialog()" size="sm">
                        Add Address
                      </Button>
                    </CardHeader>
                    <CardContent>
                      <div v-if="customer.addresses && customer.addresses.length" class="space-y-4">
                        <div
                          v-for="address in customer.addresses"
                          :key="address.id"
                          class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
                        >
                          <div class="flex items-start justify-between">
                            <div class="flex-1">
                              <div class="flex items-center gap-2 mb-2">
                                <h4 class="font-medium text-gray-900">
                                  {{ address.first_name }} {{ address.last_name }}
                                </h4>
                                <span v-if="address.is_default" class="px-2 py-0.5 text-xs font-medium bg-blue-100 text-blue-700 rounded">
                                  Default
                                </span>
                              </div>
                              <p v-if="address.company" class="text-sm text-gray-600">{{ address.company }}</p>
                              <p class="text-sm text-gray-600">{{ address.address_1 }}</p>
                              <p v-if="address.address_2" class="text-sm text-gray-600">{{ address.address_2 }}</p>
                              <p class="text-sm text-gray-600">
                                {{ address.city }}, {{ address.state }} {{ address.zip }}
                              </p>
                              <p class="text-sm text-gray-600">{{ address.country }}</p>
                              <p v-if="address.phone" class="text-sm text-gray-600 mt-1">{{ address.phone }}</p>
                            </div>
                            <div class="flex gap-2">
                              <Button @click="openAddressDialog(address)" variant="outline" size="sm">
                                Edit
                              </Button>
                              <Button @click="deleteAddress(address.id)" variant="destructive" size="sm">
                                Delete
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center py-12 text-gray-500">
                        <p>No addresses added yet</p>
                        <Button @click="openAddressDialog()" variant="outline" size="sm" class="mt-4">
                          Add First Address
                        </Button>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Orders Tab -->
                <TabsContent value="orders" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Orders</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div v-if="customer.orders && customer.orders.length" class="space-y-3">
                        <div
                          v-for="order in customer.orders"
                          :key="order.id"
                          class="p-4 border border-gray-200 rounded-lg hover:border-gray-300 transition-colors"
                        >
                          <div class="flex items-center justify-between">
                            <div>
                              <p class="font-medium text-gray-900">#{{ order.number }}</p>
                              <p class="text-sm text-gray-500">{{ order.created_at }}</p>
                            </div>
                            <div class="text-right">
                              <p class="font-medium text-gray-900">{{ order.total_formatted }}</p>
                              <span :class="[
                                'text-xs px-2 py-1 rounded-full',
                                order.status === 'completed' ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700'
                              ]">
                                {{ order.status }}
                              </span>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div v-else class="text-center py-12 text-gray-500">
                        <p>No orders yet</p>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Notes Tab -->
                <TabsContent value="notes" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Notes</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <FormTextarea
                        name="notes"
                        label="Internal Notes"
                        placeholder="Add notes about this customer..."
                        :rows="6"
                        description="Only visible to staff"
                      />
                    </CardContent>
                  </Card>
                </TabsContent>
              </Tabs>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
              <!-- Status Card -->
              <Card>
                <CardHeader>
                  <CardTitle>Status</CardTitle>
                </CardHeader>
                <CardContent>
                  <FormCheckbox
                    name="is_active"
                    label="Active customer"
                    description="Customer can place orders"
                  />
                </CardContent>
              </Card>

              <!-- Customer Group -->
              <Card>
                <CardHeader>
                  <CardTitle>Customer Group</CardTitle>
                </CardHeader>
                <CardContent>
                  <FormSelect
                    name="customer_group_id"
                    label="Group"
                    placeholder="Select a group"
                    :options="(customerGroups || []).map(g => ({ label: g.name, value: g.id }))"
                  />
                </CardContent>
              </Card>

              <!-- Tax Settings -->
              <Card>
                <CardHeader>
                  <CardTitle>Tax Settings</CardTitle>
                </CardHeader>
                <CardContent>
                  <FormCheckbox
                    name="tax_exempt"
                    label="Tax exempt"
                    description="Don't charge taxes for this customer"
                  />
                </CardContent>
              </Card>

              <!-- Stats -->
              <Card v-if="customer.stats">
                <CardHeader>
                  <CardTitle>Statistics</CardTitle>
                </CardHeader>
                <CardContent class="space-y-3">
                  <div>
                    <p class="text-sm text-gray-500">Total Orders</p>
                    <p class="text-lg font-semibold text-gray-900">{{ customer.stats.total_orders || 0 }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Total Spent</p>
                    <p class="text-lg font-semibold text-gray-900">{{ customer.stats.total_spent_formatted || '$0.00' }}</p>
                  </div>
                  <div>
                    <p class="text-sm text-gray-500">Average Order Value</p>
                    <p class="text-lg font-semibold text-gray-900">{{ customer.stats.avg_order_value_formatted || '$0.00' }}</p>
                  </div>
                </CardContent>
              </Card>

              <!-- Actions -->
              <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full" :disabled="form.processing">
                  {{ form.processing ? 'Updating...' : 'Update Customer' }}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  class="w-full"
                  @click="router.visit('/cp/customers')"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  variant="destructive"
                  class="w-full"
                  @click="deleteCustomer"
                >
                  Delete Customer
                </Button>
              </div>
            </div>
          </div>
        </template>
      </Form>
    </div>

    <!-- Address Sheet (Drawer) -->
    <Sheet v-model:open="showAddressDialog" side="right">
      <SheetHeader>
        <SheetTitle>{{ editingAddress ? 'Edit Address' : 'Add Address' }}</SheetTitle>
        <SheetDescription>
          {{ editingAddress ? 'Update the customer address' : 'Add a new address for this customer' }}
        </SheetDescription>
      </SheetHeader>

      <div class="space-y-4 py-6 overflow-y-auto flex-1">
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-900 mb-2">First Name</label>
              <input
                v-model="addressForm.first_name"
                type="text"
                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 mb-2">Last Name</label>
              <input
                v-model="addressForm.last_name"
                type="text"
                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Company</label>
            <input
              v-model="addressForm.company"
              type="text"
              class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Address Line 1</label>
            <input
              v-model="addressForm.address_1"
              type="text"
              class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Address Line 2</label>
            <input
              v-model="addressForm.address_2"
              type="text"
              class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div class="grid grid-cols-3 gap-4">
            <div>
              <label class="block text-sm font-medium text-gray-900 mb-2">City</label>
              <input
                v-model="addressForm.city"
                type="text"
                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 mb-2">State</label>
              <input
                v-model="addressForm.state"
                type="text"
                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-gray-900 mb-2">ZIP</label>
              <input
                v-model="addressForm.zip"
                type="text"
                class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
              />
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Country</label>
            <input
              v-model="addressForm.country"
              type="text"
              class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-900 mb-2">Phone</label>
            <input
              v-model="addressForm.phone"
              type="tel"
              class="flex h-10 w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500"
            />
          </div>

          <div class="flex items-center space-x-3">
            <input
              v-model="addressForm.is_default"
              type="checkbox"
              id="is_default"
              class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
            />
            <label for="is_default" class="text-sm font-medium text-gray-900">
              Set as default address
            </label>
          </div>
        </div>

        <SheetFooter class="mt-6 border-t pt-4">
          <Button @click="showAddressDialog = false" variant="outline" class="flex-1">
            Cancel
          </Button>
          <Button @click="saveAddress" class="flex-1">
            {{ editingAddress ? 'Update Address' : 'Add Address' }}
          </Button>
        </SheetFooter>
      </Sheet>
  </div>
</template>
