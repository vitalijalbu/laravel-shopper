<script setup>
import { Head, router } from '@inertiajs/vue3'
import { Form, FormInput, FormTextarea, FormSelect, FormCheckbox } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

const props = defineProps({
  page: Object,
  customerGroups: Array,
})

const handleSuccess = (response) => {
  console.log('Customer created:', response)
}

const handleError = (errors) => {
  console.error('Validation errors:', errors)
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
              <span class="text-gray-900">Create</span>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Form
        id="customer-form"
        method="post"
        url="/cp/customers"
        :initial-values="{
          first_name: '',
          last_name: '',
          email: '',
          phone: '',
          is_active: true,
          accepts_marketing: false,
          customer_group_id: '',
          tax_exempt: false,
          notes: '',
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
                <TabsList class="grid w-full grid-cols-3">
                  <TabsTrigger value="general">General</TabsTrigger>
                  <TabsTrigger value="addresses">Addresses</TabsTrigger>
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
                    <CardHeader>
                      <CardTitle>Addresses</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div class="text-center py-12 text-gray-500">
                        <p>Save the customer first, then add addresses</p>
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

              <!-- Actions -->
              <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full" :disabled="form.processing">
                  {{ form.processing ? 'Creating...' : 'Create Customer' }}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  class="w-full"
                  @click="router.visit('/cp/customers')"
                >
                  Cancel
                </Button>
              </div>
            </div>
          </div>
        </template>
      </Form>
    </div>
  </div>
</template>
