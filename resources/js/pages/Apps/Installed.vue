<template>
    <div class="installed-apps">
        <!-- Header -->
        <div class="apps-header mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                    {{ $t('apps.installed.title') }}
                </h1>
                <div class="flex items-center gap-4">
                    <Link
                        :href="route('apps.store')"
                        class="btn btn-primary"
                    >
                        {{ $t('apps.store.browse') }}
                    </Link>
                </div>
            </div>
            
            <!-- Filters -->
            <div class="mt-6 flex items-center gap-4">
                <div class="filter-tabs">
                    <button
                        @click="activeFilter = 'all'"
                        :class="['filter-tab', { active: activeFilter === 'all' }]"
                    >
                        {{ $t('apps.filters.all') }} ({{ allApps.length }})
                    </button>
                    <button
                        @click="activeFilter = 'active'"
                        :class="['filter-tab', { active: activeFilter === 'active' }]"
                    >
                        {{ $t('apps.installed.active') }} ({{ activeApps.length }})
                    </button>
                    <button
                        @click="activeFilter = 'inactive'"
                        :class="['filter-tab', { active: activeFilter === 'inactive' }]"
                    >
                        {{ $t('apps.installed.inactive') }} ({{ inactiveApps.length }})
                    </button>
                </div>

                <div class="search-box">
                    <input
                        v-model="searchQuery"
                        type="text"
                        :placeholder="$t('apps.store.search_placeholder')"
                        class="input"
                    />
                    <MagnifyingGlassIcon class="search-icon" />
                </div>
            </div>
        </div>

        <!-- Apps Grid -->
        <div v-if="loading" class="loading-state">
            <div class="spinner"></div>
            <p>{{ $t('apps.loading') }}</p>
        </div>

        <div v-else-if="filteredApps.length > 0" class="apps-grid">
            <InstalledAppCard
                v-for="installation in filteredApps"
                :key="installation.id"
                :installation="installation"
                @activate="handleActivate"
                @deactivate="handleDeactivate"
                @configure="handleConfigure"
                @uninstall="handleUninstall"
                @view-analytics="handleViewAnalytics"
            />
        </div>

        <div v-else class="empty-state">
            <div class="empty-icon">
                <CubeIcon class="w-16 h-16 text-gray-400" />
            </div>
            <h3 class="empty-title">
                {{ activeFilter === 'all' ? $t('apps.installed.no_apps') : $t(`apps.installed.no_${activeFilter}_apps`) }}
            </h3>
            <p class="empty-description">
                {{ activeFilter === 'all' ? $t('apps.installed.install_first_app') : $t(`apps.installed.no_${activeFilter}_description`) }}
            </p>
            <Link
                v-if="activeFilter === 'all'"
                :href="route('apps.store')"
                class="btn btn-primary"
            >
                {{ $t('apps.store.browse') }}
            </Link>
        </div>

        <!-- Bulk Actions -->
        <div v-if="selectedApps.length > 0" class="bulk-actions">
            <div class="bulk-info">
                {{ $t('apps.selected_count', { count: selectedApps.length }) }}
            </div>
            <div class="bulk-buttons">
                <button
                    @click="bulkActivate"
                    class="btn btn-success"
                    :disabled="!canBulkActivate"
                >
                    {{ $t('apps.actions.activate_selected') }}
                </button>
                <button
                    @click="bulkDeactivate"
                    class="btn btn-warning"
                    :disabled="!canBulkDeactivate"
                >
                    {{ $t('apps.actions.deactivate_selected') }}
                </button>
                <button
                    @click="bulkUninstall"
                    class="btn btn-danger"
                    :disabled="!canBulkUninstall"
                >
                    {{ $t('apps.actions.uninstall_selected') }}
                </button>
            </div>
        </div>

        <!-- App Analytics Modal -->
        <AppAnalyticsModal
            v-if="selectedInstallation"
            :installation="selectedInstallation"
            :is-open="showAnalyticsModal"
            @close="closeAnalyticsModal"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import { MagnifyingGlassIcon, CubeIcon } from '@heroicons/vue/24/outline'
import InstalledAppCard from './InstalledAppCard.vue'
import AppAnalyticsModal from './AppAnalyticsModal.vue'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    installedApps: {
        type: Array,
        default: () => []
    }
})

// State
const activeFilter = ref('all')
const searchQuery = ref('')
const loading = ref(false)
const selectedApps = ref([])
const selectedInstallation = ref(null)
const showAnalyticsModal = ref(false)

// Computed
const allApps = computed(() => props.installedApps)

const activeApps = computed(() => 
    props.installedApps.filter(installation => installation.status === 'active')
)

const inactiveApps = computed(() => 
    props.installedApps.filter(installation => installation.status === 'inactive')
)

const filteredApps = computed(() => {
    let apps = []
    
    switch (activeFilter.value) {
        case 'active':
            apps = activeApps.value
            break
        case 'inactive':
            apps = inactiveApps.value
            break
        default:
            apps = allApps.value
    }

    // Search filter
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase()
        apps = apps.filter(installation =>
            installation.app.name.toLowerCase().includes(query) ||
            installation.app.description.toLowerCase().includes(query) ||
            installation.app.author.toLowerCase().includes(query)
        )
    }

    return apps
})

const canBulkActivate = computed(() => 
    selectedApps.value.some(id => {
        const installation = props.installedApps.find(ia => ia.id === id)
        return installation?.status === 'inactive'
    })
)

const canBulkDeactivate = computed(() => 
    selectedApps.value.some(id => {
        const installation = props.installedApps.find(ia => ia.id === id)
        return installation?.status === 'active'
    })
)

const canBulkUninstall = computed(() => 
    selectedApps.value.some(id => {
        const installation = props.installedApps.find(ia => ia.id === id)
        return installation && !installation.app.system
    })
)

// Methods
const handleActivate = async (installation) => {
    try {
        loading.value = true
        await router.post(route('apps.activate', installation.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                installation.status = 'active'
            }
        })
    } catch (error) {
        console.error('Activation failed:', error)
    } finally {
        loading.value = false
    }
}

const handleDeactivate = async (installation) => {
    try {
        loading.value = true
        await router.post(route('apps.deactivate', installation.id), {}, {
            preserveScroll: true,
            onSuccess: () => {
                installation.status = 'inactive'
            }
        })
    } catch (error) {
        console.error('Deactivation failed:', error)
    } finally {
        loading.value = false
    }
}

const handleConfigure = (installation) => {
    router.visit(route('apps.configure', installation.app.id))
}

const handleUninstall = async (installation) => {
    if (!confirm($t('apps.messages.confirm_uninstall', { name: installation.app.name }))) {
        return
    }

    try {
        loading.value = true
        await router.delete(route('apps.uninstall', installation.app.id), {
            preserveScroll: true,
            onSuccess: () => {
                const index = props.installedApps.findIndex(ia => ia.id === installation.id)
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

const handleViewAnalytics = (installation) => {
    selectedInstallation.value = installation
    showAnalyticsModal.value = true
}

const closeAnalyticsModal = () => {
    showAnalyticsModal.value = false
    selectedInstallation.value = null
}

const bulkActivate = async () => {
    const installationsToActivate = selectedApps.value
        .map(id => props.installedApps.find(ia => ia.id === id))
        .filter(installation => installation?.status === 'inactive')

    try {
        loading.value = true
        await router.post(route('apps.bulk-activate'), {
            installation_ids: installationsToActivate.map(i => i.id)
        }, {
            preserveScroll: true,
            onSuccess: () => {
                installationsToActivate.forEach(installation => {
                    installation.status = 'active'
                })
                selectedApps.value = []
            }
        })
    } catch (error) {
        console.error('Bulk activation failed:', error)
    } finally {
        loading.value = false
    }
}

const bulkDeactivate = async () => {
    const installationsToDeactivate = selectedApps.value
        .map(id => props.installedApps.find(ia => ia.id === id))
        .filter(installation => installation?.status === 'active')

    try {
        loading.value = true
        await router.post(route('apps.bulk-deactivate'), {
            installation_ids: installationsToDeactivate.map(i => i.id)
        }, {
            preserveScroll: true,
            onSuccess: () => {
                installationsToDeactivate.forEach(installation => {
                    installation.status = 'inactive'
                })
                selectedApps.value = []
            }
        })
    } catch (error) {
        console.error('Bulk deactivation failed:', error)
    } finally {
        loading.value = false
    }
}

const bulkUninstall = async () => {
    const installationsToUninstall = selectedApps.value
        .map(id => props.installedApps.find(ia => ia.id === id))
        .filter(installation => installation && !installation.app.system)

    if (!confirm($t('apps.messages.confirm_bulk_uninstall', { count: installationsToUninstall.length }))) {
        return
    }

    try {
        loading.value = true
        await router.delete(route('apps.bulk-uninstall'), {
            data: {
                installation_ids: installationsToUninstall.map(i => i.id)
            },
            preserveScroll: true,
            onSuccess: () => {
                installationsToUninstall.forEach(installation => {
                    const index = props.installedApps.findIndex(ia => ia.id === installation.id)
                    if (index !== -1) {
                        props.installedApps.splice(index, 1)
                    }
                })
                selectedApps.value = []
            }
        })
    } catch (error) {
        console.error('Bulk uninstall failed:', error)
    } finally {
        loading.value = false
    }
}

onMounted(() => {
    // Any initialization logic
})
</script>

<style scoped>
.installed-apps {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
}

.apps-header {
    margin-bottom: 32px;
}

.filter-tabs {
    display: flex;
    gap: 4px;
    background: #f3f4f6;
    padding: 4px;
    border-radius: 8px;
}

.filter-tab {
    padding: 8px 16px;
    border-radius: 6px;
    border: none;
    background: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    font-size: 14px;
}

.filter-tab.active {
    background: white;
    color: #111827;
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
}

.filter-tab:hover:not(.active) {
    color: #374151;
}

.search-box {
    position: relative;
    flex: 1;
    max-width: 300px;
}

.search-icon {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    width: 16px;
    height: 16px;
    color: #9ca3af;
}

.input {
    width: 100%;
    padding: 8px 12px 8px 36px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    background: white;
}

.input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.loading-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 0;
    color: #6b7280;
}

.spinner {
    width: 40px;
    height: 40px;
    border: 3px solid #e5e7eb;
    border-top: 3px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 16px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.apps-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
    gap: 24px;
}

.empty-state {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 64px 0;
    text-align: center;
}

.empty-icon {
    margin-bottom: 16px;
    opacity: 0.5;
}

.empty-title {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.empty-description {
    color: #6b7280;
    margin: 0 0 24px 0;
    max-width: 400px;
}

.bulk-actions {
    position: fixed;
    bottom: 24px;
    left: 50%;
    transform: translateX(-50%);
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 16px 24px;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    display: flex;
    align-items: center;
    gap: 16px;
    z-index: 50;
}

.bulk-info {
    color: #374151;
    font-weight: 500;
}

.bulk-buttons {
    display: flex;
    gap: 8px;
}

.btn {
    padding: 8px 16px;
    border-radius: 8px;
    border: none;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover {
    background: #2563eb;
}

.btn-success {
    background: #10b981;
    color: white;
}

.btn-success:hover:not(:disabled) {
    background: #059669;
}

.btn-warning {
    background: #f59e0b;
    color: white;
}

.btn-warning:hover:not(:disabled) {
    background: #d97706;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #dc2626;
}

.btn:disabled {
    background: #d1d5db;
    color: #9ca3af;
    cursor: not-allowed;
}

/* Responsive */
@media (max-width: 768px) {
    .installed-apps {
        padding: 16px;
    }
    
    .apps-header .flex {
        flex-direction: column;
        align-items: stretch;
        gap: 16px;
    }
    
    .mt-6 {
        margin-top: 16px;
        flex-direction: column;
        gap: 16px;
    }
    
    .search-box {
        max-width: none;
    }
    
    .apps-grid {
        grid-template-columns: 1fr;
    }
    
    .bulk-actions {
        left: 16px;
        right: 16px;
        transform: none;
        flex-direction: column;
        text-align: center;
    }
    
    .bulk-buttons {
        justify-content: center;
        flex-wrap: wrap;
    }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .filter-tabs {
        background: #374151;
    }
    
    .filter-tab {
        color: #d1d5db;
    }
    
    .filter-tab.active {
        background: #1f2937;
        color: #f9fafb;
    }
    
    .input {
        background: #1f2937;
        border-color: #374151;
        color: #f9fafb;
    }
    
    .input:focus {
        border-color: #3b82f6;
    }
    
    .empty-title {
        color: #f9fafb;
    }
    
    .bulk-actions {
        background: #1f2937;
        border-color: #374151;
    }
    
    .bulk-info {
        color: #d1d5db;
    }
}
</style>
