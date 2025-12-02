<script setup>
import { Head, router } from '@inertiajs/vue3'
import { Form, FormInput, FormTextarea, FormSelect, FormCheckbox } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card'
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/components/ui/tabs'

const props = defineProps({
  page: Object,
  schema: Object,
  product: Object,
  categories: Array,
  brands: Array,
})

const statusOptions = [
  { label: 'Active', value: 'active' },
  { label: 'Draft', value: 'draft' },
  { label: 'Archived', value: 'archived' },
]

const handleSuccess = (response) => {
  console.log('Product updated:', response)
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
              <a href="/cp/products" class="hover:text-gray-700">Products</a>
              <span class="mx-2">/</span>
              <a :href="`/cp/products/${product.id}`" class="hover:text-gray-700">{{ product.name }}</a>
              <span class="mx-2">/</span>
              <span class="text-gray-900">Edit</span>
            </nav>
          </div>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <Form
        id="product-form"
        method="put"
        :url="`/cp/products/${product.id}`"
        :initial-values="{
          name: product.name || '',
          handle: product.handle || '',
          description: product.description || '',
          status: product.status || 'draft',
          price: product.price || '',
          compare_at_price: product.compare_at_price || '',
          cost_per_item: product.cost_per_item || '',
          sku: product.sku || '',
          barcode: product.barcode || '',
          track_quantity: product.track_quantity ?? true,
          quantity: product.quantity || 0,
          weight: product.weight || '',
          weight_unit: product.weight_unit || 'kg',
          requires_shipping: product.requires_shipping ?? true,
          is_visible: product.is_visible ?? true,
          is_featured: product.is_featured ?? false,
          seo_title: product.seo_title || '',
          seo_description: product.seo_description || '',
          category_id: product.category_id || '',
          brand_id: product.brand_id || '',
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
                <TabsList class="grid w-full grid-cols-5">
                  <TabsTrigger value="general">General</TabsTrigger>
                  <TabsTrigger value="pricing">Pricing</TabsTrigger>
                  <TabsTrigger value="inventory">Inventory</TabsTrigger>
                  <TabsTrigger value="shipping">Shipping</TabsTrigger>
                  <TabsTrigger value="seo">SEO</TabsTrigger>
                </TabsList>

                <!-- General Tab -->
                <TabsContent value="general" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Product Information</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <FormInput
                        name="name"
                        label="Product Name"
                        placeholder="e.g. Classic Cotton T-Shirt"
                        required
                        autocomplete="off"
                      />

                      <FormInput
                        name="handle"
                        label="URL Handle"
                        placeholder="classic-cotton-t-shirt"
                        description="Used in the product URL. Auto-generated from name if left empty."
                        autocomplete="off"
                      />

                      <FormTextarea
                        name="description"
                        label="Description"
                        placeholder="Describe your product..."
                        :rows="6"
                      />
                    </CardContent>
                  </Card>

                  <Card>
                    <CardHeader>
                      <CardTitle>Media</CardTitle>
                    </CardHeader>
                    <CardContent>
                      <div class="border-2 border-dashed border-gray-300 rounded-lg p-12 text-center">
                        <p class="text-gray-500">Media upload component will go here</p>
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Pricing Tab -->
                <TabsContent value="pricing" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Pricing</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <div class="grid grid-cols-2 gap-4">
                        <FormInput
                          name="price"
                          label="Price"
                          type="number"
                          placeholder="0.00"
                          required
                          step="0.01"
                        />

                        <FormInput
                          name="compare_at_price"
                          label="Compare at Price"
                          type="number"
                          placeholder="0.00"
                          step="0.01"
                          description="Show a sale price"
                        />
                      </div>

                      <FormInput
                        name="cost_per_item"
                        label="Cost per Item"
                        type="number"
                        placeholder="0.00"
                        step="0.01"
                        description="Customers won't see this"
                      />
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Inventory Tab -->
                <TabsContent value="inventory" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Inventory</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <div class="grid grid-cols-2 gap-4">
                        <FormInput
                          name="sku"
                          label="SKU"
                          placeholder="SKU-001"
                          autocomplete="off"
                        />

                        <FormInput
                          name="barcode"
                          label="Barcode"
                          placeholder="123456789"
                          autocomplete="off"
                        />
                      </div>

                      <FormCheckbox
                        name="track_quantity"
                        label="Track quantity"
                        description="Track inventory for this product"
                      />

                      <FormInput
                        name="quantity"
                        label="Quantity"
                        type="number"
                        placeholder="0"
                      />
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- Shipping Tab -->
                <TabsContent value="shipping" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Shipping</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <FormCheckbox
                        name="requires_shipping"
                        label="This product requires shipping"
                      />

                      <div class="grid grid-cols-2 gap-4">
                        <FormInput
                          name="weight"
                          label="Weight"
                          type="number"
                          placeholder="0.0"
                          step="0.01"
                        />

                        <FormSelect
                          name="weight_unit"
                          label="Weight Unit"
                          :options="[
                            { label: 'Kilograms (kg)', value: 'kg' },
                            { label: 'Grams (g)', value: 'g' },
                            { label: 'Pounds (lb)', value: 'lb' },
                            { label: 'Ounces (oz)', value: 'oz' },
                          ]"
                        />
                      </div>
                    </CardContent>
                  </Card>
                </TabsContent>

                <!-- SEO Tab -->
                <TabsContent value="seo" class="space-y-6 mt-6">
                  <Card>
                    <CardHeader>
                      <CardTitle>Search Engine Optimization</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                      <FormInput
                        name="seo_title"
                        label="SEO Title"
                        placeholder="Product title for search engines"
                        description="Recommended: 50-60 characters"
                      />

                      <FormTextarea
                        name="seo_description"
                        label="SEO Description"
                        placeholder="Product description for search engines"
                        :rows="4"
                        description="Recommended: 150-160 characters"
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
                  <FormSelect
                    name="status"
                    label="Product Status"
                    :options="statusOptions"
                  />
                </CardContent>
              </Card>

              <!-- Organization Card -->
              <Card>
                <CardHeader>
                  <CardTitle>Organization</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <FormSelect
                    name="category_id"
                    label="Category"
                    placeholder="Select a category"
                    :options="categories.map(c => ({ label: c.name, value: c.id }))"
                  />

                  <FormSelect
                    name="brand_id"
                    label="Brand"
                    placeholder="Select a brand"
                    :options="brands.map(b => ({ label: b.name, value: b.id }))"
                  />
                </CardContent>
              </Card>

              <!-- Visibility Card -->
              <Card>
                <CardHeader>
                  <CardTitle>Visibility</CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                  <FormCheckbox
                    name="is_visible"
                    label="Visible in storefront"
                    description="Make this product visible to customers"
                  />

                  <FormCheckbox
                    name="is_featured"
                    label="Featured product"
                    description="Show in featured products section"
                  />
                </CardContent>
              </Card>

              <!-- Actions -->
              <div class="flex flex-col gap-3">
                <Button type="submit" class="w-full" :disabled="form.processing">
                  {{ form.processing ? 'Updating...' : 'Update Product' }}
                </Button>
                <Button
                  type="button"
                  variant="outline"
                  class="w-full"
                  @click="router.visit(`/cp/products/${product.id}`)"
                >
                  Cancel
                </Button>
                <Button
                  type="button"
                  variant="destructive"
                  class="w-full"
                  @click="deleteProduct"
                >
                  Delete Product
                </Button>
              </div>
            </div>
          </div>
        </template>
      </Form>
    </div>
  </div>
</template>

<script>
export default {
  methods: {
    deleteProduct() {
      if (confirm('Are you sure you want to delete this product?')) {
        this.$inertia.delete(`/cp/products/${this.product.id}`)
      }
    }
  }
}
</script>
