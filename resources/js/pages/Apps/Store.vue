<template>
    <div class="apps-store max-w-7xl mx-auto p-6">
        <!-- Header -->
        <div class="apps-header mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $t('apps.store.title') }}
                </h1>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('apps.installed')"
                        class="btn btn-outline"
                    >
                        {{ $t('apps.installed.title') }}
                    </Link>
                    <Link
                        :href="route('apps.submit')"
                        class="btn btn-primary"
                    >
                        {{ $t('apps.store.submit') }}
                    </Link>
                </div>
            </div>
            
            <!-- Search and Filters -->
            <div class="mt-6 grid grid-cols-1 lg:grid-cols-4 gap-4">
                <div class="lg:col-span-2">
                    <div class="relative">
                        <input
                            v-model="search"
                            type="text"
                            :placeholder="$t('apps.store.search_placeholder')"
                            class="input pl-10"
                            @input="debouncedSearch"
                        />
                        <SearchIcon class="absolute left-3 top-3 w-4 h-4 text-gray-400" />
                    </div>
                </div>
                <div>
                    <select v-model="selectedCategory" class="select" @change="filterApps">
                        <option value="">{{ $t('apps.store.all_categories') }}</option>
                        <option
                            v-for="(label, key) in categories"
                            :key="key"
                            :value="key"
                        >
                            {{ label }}
                        </option>
                    </select>
                </div>
                <div>
                    <select v-model="selectedPricing" class="select" @change="filterApps">
                        <option value="">{{ $t('apps.store.all_pricing') }}</option>
                        <option value="free">{{ $t('apps.store.free_apps') }}</option>
                        <option value="paid">{{ $t('apps.store.paid_apps') }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Featured Apps -->
        <div v-if="featuredApps.length > 0" class="mb-12">
            <h2 class="text-2xl font-semibold mb-6 text-gray-900 dark:text-white">
                {{ $t('apps.store.featured') }}
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <AppCard
                    v-for="app in featuredApps"
                    :key="app.id"
                    :app="app"
                    :featured="true"
                    @install="handleInstall"
                    @view-details="viewAppDetails"
                />
            </div>
        </div>

        <!-- Apps Grid -->
        <div>
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                    {{ $t('apps.store.browse') }}
                </h2>
                <div class="flex items-center gap-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">
                        {{ $t('apps.filters.sort_by') }}:
                    </span>
                    <select v-model="sortBy" class="select select-sm" @change="sortApps">
                        <option value="popular">{{ $t('apps.filters.popular') }}</option>
                        <option value="newest">{{ $t('apps.filters.newest') }}</option>
                        <option value="rating">{{ $t('apps.filters.rating') }}</option>
                        <option value="name">{{ $t('apps.filters.name') }}</option>
                        <option value="price">{{ $t('apps.filters.price') }}</option>
                    </select>
                </div>
            </div>

            <div v-if="loading" class="text-center py-12">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-primary mx-auto"></div>
            </div>

            <div
                v-else-if="filteredApps.length > 0"
                class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6"
            >
                <AppCard
                    v-for="app in filteredApps"
                    :key="app.id"
                    :app="app"
                    @install="handleInstall"
                    @view-details="viewAppDetails"
                />
            </div>

            <div v-else class="text-center py-12">
                <div class="text-gray-500 dark:text-gray-400">
                    {{ $t('apps.store.no_apps') }}
                </div>
            </div>
        </div>

        <!-- App Details Modal -->
        <AppDetailsModal
            v-if="selectedApp"
            :app="selectedApp"
            :is-open="showDetailsModal"
            @close="closeDetailsModal"
            @install="handleInstall"
            @uninstall="handleUninstall"
            @configure="handleConfigure"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { usePage } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { debounce } from 'lodash'
import { SearchIcon } from '@heroicons/vue/24/outline'
import AppCard from './AppCard.vue'
import AppDetailsModal from './AppDetailsModal.vue'

const { t: $t } = useI18n()
const page = usePage()

// Props
const props = defineProps({
    apps: {
        type: Array,
        default: () => []
    },
    featuredApps: {
        type: Array,
        default: () => []
    },
    categories: {
        type: Object,
        default: () => ({})
    },
    installedApps: {
        type: Array,
        default: () => []
    }
})

// State
const search = ref('')
const selectedCategory = ref('')
const selectedPricing = ref('')
const sortBy = ref('popular')
const loading = ref(false)
const selectedApp = ref(null)
const showDetailsModal = ref(false)

// Computed
const filteredApps = computed(() => {
    let filtered = [...props.apps]

    // Search filter
    if (search.value) {
        const query = search.value.toLowerCase()
        filtered = filtered.filter(app =>
            app.name.toLowerCase().includes(query) ||
            app.description.toLowerCase().includes(query) ||
            app.author.toLowerCase().includes(query)
        )
    }

    // Category filter
    if (selectedCategory.value) {
        filtered = filtered.filter(app => app.category === selectedCategory.value)
    }

    // Pricing filter
    if (selectedPricing.value) {
        if (selectedPricing.value === 'free') {
            filtered = filtered.filter(app => app.price === 0)
        } else if (selectedPricing.value === 'paid') {
            filtered = filtered.filter(app => app.price > 0)
        }
    }

    // Sorting
    switch (sortBy.value) {
        case 'popular':
            filtered.sort((a, b) => b.installs - a.installs)
            break
        case 'newest':
            filtered.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
            break
        case 'rating':
            filtered.sort((a, b) => b.rating - a.rating)
            break
        case 'name':
            filtered.sort((a, b) => a.name.localeCompare(b.name))
            break
        case 'price':
            filtered.sort((a, b) => a.price - b.price)
            break
    }

    return filtered
})

// Methods
const debouncedSearch = debounce(() => {
    filterApps()
}, 300)

const filterApps = () => {
    // Trigger reactivity
    search.value = search.value
}

const sortApps = () => {
    // Trigger reactivity
    sortBy.value = sortBy.value
}

const handleInstall = async (app) => {
    try {
        loading.value = true
        await router.post(route('apps.install'), {
            app_id: app.id
        }, {
            preserveScroll: true,
            onSuccess: () => {
                // Update local state
                props.installedApps.push({
                    app: app,
                    status: 'active',
                    installed_at: new Date().toISOString()
                })
            }
        })
    } catch (error) {
        console.error('Installation failed:', error)
    } finally {
        loading.value = false
    }
}

const handleUninstall = async (app) => {
    if (!confirm($t('apps.messages.confirm_uninstall', { name: app.name }))) {
        return
    }

    try {
        loading.value = true
        await router.delete(route('apps.uninstall', app.id), {
            preserveScroll: true,
            onSuccess: () => {
                // Update local state
                const index = props.installedApps.findIndex(ia => ia.app.id === app.id)
                if (index !== -1) {
                    props.installedApps.splice(index, 1)
                }
            }
        })
    } catch (error) {
        console.error('Uninstall failed:', error)
    } finally {
        loading.value = false
    }
}

const handleConfigure = (app) => {
    router.visit(route('apps.configure', app.id))
}

const viewAppDetails = (app) => {
    selectedApp.value = app
    showDetailsModal.value = true
}

const closeDetailsModal = () => {
    showDetailsModal.value = false
    selectedApp.value = null
}

onMounted(() => {
    // Any initialization logic
})
</script>

<style scoped>
.apps-store {
    /* Styling gestito tramite classi Tailwind */
}

.btn {
    padding: 1rem 1rem;
    border-radius: 0.5rem;
    font-weight: 500;
    transition: all 0.2s;
}

.btn-primary {
    background-color: #2563eb;
    color: white;
}

.btn-primary:hover {
    background-color: #1d4ed8;
}

.btn-outline {
    border: 1px solid #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background-color: #f9fafb;
}

.form-input {
    width: 100%;
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
}

.form-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.form-select {
    padding: 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.5rem;
}

.badge {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    border-radius: 0.25rem;
}
</style>
