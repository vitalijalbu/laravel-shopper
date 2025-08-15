<template>
    <div class="app-configure">
        <!-- Header -->
        <div class="configure-header">
            <div class="header-content">
                <div class="app-info">
                    <div class="app-icon">
                        <img
                            v-if="app.icon"
                            :src="app.icon"
                            :alt="app.name"
                            class="icon-image"
                        />
                        <div v-else class="icon-placeholder">
                            {{ app.name.charAt(0).toUpperCase() }}
                        </div>
                    </div>
                    <div class="app-details">
                        <h1 class="app-name">{{ $t('apps.configure.title', { name: app.name }) }}</h1>
                        <p class="app-version">{{ $t('apps.details.version') }} {{ app.version }}</p>
                        <div class="app-status" :class="statusClass">
                            <div class="status-indicator"></div>
                            <span>{{ statusText }}</span>
                        </div>
                    </div>
                </div>
                
                <div class="header-actions">
                    <Link
                        :href="route('apps.installed')"
                        class="btn btn-outline"
                    >
                        <ArrowLeftIcon class="btn-icon" />
                        {{ $t('apps.actions.back_to_apps') }}
                    </Link>
                    <button
                        @click="testConnection"
                        class="btn btn-secondary"
                        :disabled="testing"
                    >
                        <BoltIcon class="btn-icon" />
                        {{ testing ? $t('apps.configure.testing') : $t('apps.configure.test_connection') }}
                    </button>
                </div>
            </div>
        </div>

        <div class="configure-content">
            <!-- Navigation Tabs -->
            <div class="nav-tabs">
                <button
                    v-for="tab in tabs"
                    :key="tab.key"
                    @click="activeTab = tab.key"
                    :class="['nav-tab', { active: activeTab === tab.key }]"
                >
                    <component :is="tab.icon" class="tab-icon" />
                    {{ tab.label }}
                </button>
            </div>

            <!-- Tab Content -->
            <div class="tab-content">
                <!-- General Settings -->
                <div v-if="activeTab === 'general'" class="tab-pane">
                    <div class="settings-section">
                        <h3 class="section-title">{{ $t('apps.configure.settings') }}</h3>
                        <div class="settings-grid">
                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.app_name') }}</label>
                                <input
                                    v-model="settings.app_name"
                                    type="text"
                                    class="setting-input"
                                    :placeholder="app.name"
                                />
                                <p class="setting-help">{{ $t('apps.configure.app_name_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.enabled') }}</label>
                                <div class="toggle-switch">
                                    <input
                                        v-model="settings.enabled"
                                        type="checkbox"
                                        class="toggle-input"
                                        id="app-enabled"
                                    />
                                    <label for="app-enabled" class="toggle-label"></label>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.enabled_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.auto_update') }}</label>
                                <div class="toggle-switch">
                                    <input
                                        v-model="settings.auto_update"
                                        type="checkbox"
                                        class="toggle-input"
                                        id="auto-update"
                                    />
                                    <label for="auto-update" class="toggle-label"></label>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.auto_update_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.debug_mode') }}</label>
                                <div class="toggle-switch">
                                    <input
                                        v-model="settings.debug_mode"
                                        type="checkbox"
                                        class="toggle-input"
                                        id="debug-mode"
                                    />
                                    <label for="debug-mode" class="toggle-label"></label>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.debug_mode_help') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- API Settings -->
                <div v-if="activeTab === 'api'" class="tab-pane">
                    <div class="settings-section">
                        <h3 class="section-title">{{ $t('apps.configure.api_settings') }}</h3>
                        <div class="settings-grid">
                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.api_url') }}</label>
                                <input
                                    v-model="settings.api_url"
                                    type="url"
                                    class="setting-input"
                                    placeholder="https://api.example.com"
                                />
                                <p class="setting-help">{{ $t('apps.configure.api_url_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.api_key') }}</label>
                                <div class="input-with-toggle">
                                    <input
                                        v-model="settings.api_key"
                                        :type="showApiKey ? 'text' : 'password'"
                                        class="setting-input"
                                        placeholder="••••••••••••••••"
                                    />
                                    <button
                                        @click="showApiKey = !showApiKey"
                                        class="toggle-visibility-btn"
                                        type="button"
                                    >
                                        <EyeIcon v-if="!showApiKey" class="w-4 h-4" />
                                        <EyeSlashIcon v-else class="w-4 h-4" />
                                    </button>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.api_key_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.timeout') }}</label>
                                <div class="input-with-suffix">
                                    <input
                                        v-model.number="settings.timeout"
                                        type="number"
                                        class="setting-input"
                                        min="1"
                                        max="300"
                                    />
                                    <span class="input-suffix">{{ $t('apps.configure.seconds') }}</span>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.timeout_help') }}</p>
                            </div>

                            <div class="setting-group">
                                <label class="setting-label">{{ $t('apps.configure.rate_limit') }}</label>
                                <div class="input-with-suffix">
                                    <input
                                        v-model.number="settings.rate_limit"
                                        type="number"
                                        class="setting-input"
                                        min="1"
                                        max="10000"
                                    />
                                    <span class="input-suffix">{{ $t('apps.configure.per_minute') }}</span>
                                </div>
                                <p class="setting-help">{{ $t('apps.configure.rate_limit_help') }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Webhooks -->
                <div v-if="activeTab === 'webhooks'" class="tab-pane">
                    <div class="settings-section">
                        <div class="section-header">
                            <h3 class="section-title">{{ $t('apps.webhooks.title') }}</h3>
                            <button @click="addWebhook" class="btn btn-primary">
                                <PlusIcon class="btn-icon" />
                                {{ $t('apps.webhooks.add_webhook') }}
                            </button>
                        </div>

                        <div v-if="webhooks.length > 0" class="webhooks-list">
                            <div
                                v-for="(webhook, index) in webhooks"
                                :key="webhook.id || index"
                                class="webhook-item"
                            >
                                <div class="webhook-content">
                                    <div class="webhook-header">
                                        <h4 class="webhook-name">{{ webhook.name || $t('apps.webhooks.unnamed') }}</h4>
                                        <div class="webhook-status" :class="webhook.enabled ? 'enabled' : 'disabled'">
                                            {{ webhook.enabled ? $t('apps.webhooks.enabled') : $t('apps.webhooks.disabled') }}
                                        </div>
                                    </div>
                                    
                                    <div class="webhook-details">
                                        <div class="detail-item">
                                            <span class="detail-label">{{ $t('apps.webhooks.endpoint') }}:</span>
                                            <span class="detail-value">{{ webhook.endpoint }}</span>
                                        </div>
                                        <div class="detail-item">
                                            <span class="detail-label">{{ $t('apps.webhooks.events') }}:</span>
                                            <span class="detail-value">{{ webhook.events?.join(', ') || 'None' }}</span>
                                        </div>
                                        <div v-if="webhook.last_success" class="detail-item">
                                            <span class="detail-label">{{ $t('apps.webhooks.last_success') }}:</span>
                                            <span class="detail-value">{{ formatDateTime(webhook.last_success) }}</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="webhook-actions">
                                    <button
                                        @click="testWebhook(webhook)"
                                        class="btn btn-sm btn-outline"
                                        :disabled="!webhook.enabled"
                                    >
                                        {{ $t('apps.webhooks.test_webhook') }}
                                    </button>
                                    <button
                                        @click="editWebhook(webhook)"
                                        class="btn btn-sm btn-outline"
                                    >
                                        <PencilIcon class="w-4 h-4" />
                                    </button>
                                    <button
                                        @click="removeWebhook(index)"
                                        class="btn btn-sm btn-danger"
                                    >
                                        <TrashIcon class="w-4 h-4" />
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div v-else class="empty-state">
                            <CubeTransparentIcon class="empty-icon" />
                            <h4 class="empty-title">{{ $t('apps.webhooks.no_webhooks') }}</h4>
                            <p class="empty-description">{{ $t('apps.webhooks.add_first_webhook') }}</p>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div v-if="activeTab === 'permissions'" class="tab-pane">
                    <div class="settings-section">
                        <h3 class="section-title">{{ $t('apps.configure.permissions') }}</h3>
                        <div class="permissions-grid">
                            <div
                                v-for="permission in availablePermissions"
                                :key="permission.key"
                                class="permission-item"
                            >
                                <div class="permission-content">
                                    <div class="permission-header">
                                        <h4 class="permission-name">{{ $t(`apps.permissions.${permission.key}`) }}</h4>
                                        <div class="toggle-switch">
                                            <input
                                                v-model="settings.permissions"
                                                :value="permission.key"
                                                type="checkbox"
                                                class="toggle-input"
                                                :id="`permission-${permission.key}`"
                                            />
                                            <label :for="`permission-${permission.key}`" class="toggle-label"></label>
                                        </div>
                                    </div>
                                    <p class="permission-description">{{ permission.description }}</p>
                                </div>
                                <div class="permission-icon">
                                    <component :is="permission.icon" class="w-6 h-6" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advanced -->
                <div v-if="activeTab === 'advanced'" class="tab-pane">
                    <div class="settings-section">
                        <h3 class="section-title">{{ $t('apps.configure.advanced') }}</h3>
                        
                        <div class="advanced-actions">
                            <div class="action-card">
                                <div class="action-content">
                                    <h4 class="action-title">{{ $t('apps.actions.export_settings') }}</h4>
                                    <p class="action-description">{{ $t('apps.configure.export_settings_description') }}</p>
                                </div>
                                <button @click="exportSettings" class="btn btn-outline">
                                    <DocumentArrowDownIcon class="btn-icon" />
                                    {{ $t('apps.actions.export') }}
                                </button>
                            </div>

                            <div class="action-card">
                                <div class="action-content">
                                    <h4 class="action-title">{{ $t('apps.actions.import_settings') }}</h4>
                                    <p class="action-description">{{ $t('apps.configure.import_settings_description') }}</p>
                                </div>
                                <input
                                    ref="importInput"
                                    type="file"
                                    accept=".json"
                                    @change="importSettings"
                                    class="hidden"
                                />
                                <button @click="$refs.importInput.click()" class="btn btn-outline">
                                    <DocumentArrowUpIcon class="btn-icon" />
                                    {{ $t('apps.actions.import') }}
                                </button>
                            </div>

                            <div class="action-card danger">
                                <div class="action-content">
                                    <h4 class="action-title">{{ $t('apps.configure.reset_settings') }}</h4>
                                    <p class="action-description">{{ $t('apps.configure.reset_settings_description') }}</p>
                                </div>
                                <button @click="resetSettings" class="btn btn-danger">
                                    <ArrowPathIcon class="btn-icon" />
                                    {{ $t('apps.actions.reset') }}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer Actions -->
        <div class="configure-footer">
            <div class="footer-actions">
                <button @click="resetForm" class="btn btn-outline">
                    {{ $t('apps.actions.cancel') }}
                </button>
                <button
                    @click="saveSettings"
                    class="btn btn-primary"
                    :disabled="saving"
                >
                    {{ saving ? $t('apps.actions.saving') : $t('apps.configure.save_settings') }}
                </button>
            </div>
        </div>

        <!-- Webhook Modal -->
        <WebhookModal
            v-if="showWebhookModal"
            :webhook="selectedWebhook"
            :is-open="showWebhookModal"
            @close="closeWebhookModal"
            @save="saveWebhook"
        />
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { Link, router } from '@inertiajs/vue3'
import { useI18n } from 'vue-i18n'
import {
    ArrowLeftIcon,
    BoltIcon,
    CogIcon,
    ShieldCheckIcon,
    GlobeAltIcon,
    AdjustmentsHorizontalIcon,
    PlusIcon,
    PencilIcon,
    TrashIcon,
    EyeIcon,
    EyeSlashIcon,
    CubeTransparentIcon,
    DocumentArrowDownIcon,
    DocumentArrowUpIcon,
    ArrowPathIcon
} from '@heroicons/vue/24/outline'
import WebhookModal from '../Components/Apps/WebhookModal.vue'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    app: {
        type: Object,
        required: true
    },
    installation: {
        type: Object,
        required: true
    },
    currentSettings: {
        type: Object,
        default: () => ({})
    }
})

// State
const activeTab = ref('general')
const testing = ref(false)
const saving = ref(false)
const showApiKey = ref(false)
const showWebhookModal = ref(false)
const selectedWebhook = ref(null)
const importInput = ref(null)

// Settings data
const settings = ref({
    app_name: props.currentSettings.app_name || '',
    enabled: props.currentSettings.enabled !== false,
    auto_update: props.currentSettings.auto_update !== false,
    debug_mode: props.currentSettings.debug_mode || false,
    api_url: props.currentSettings.api_url || '',
    api_key: props.currentSettings.api_key || '',
    timeout: props.currentSettings.timeout || 30,
    rate_limit: props.currentSettings.rate_limit || 1000,
    permissions: props.currentSettings.permissions || []
})

const webhooks = ref(props.currentSettings.webhooks || [])

// Computed
const statusClass = computed(() => {
    return `status-${props.installation.status}`
})

const statusText = computed(() => {
    switch (props.installation.status) {
        case 'active':
            return $t('apps.status.active')
        case 'inactive':
            return $t('apps.status.inactive')
        case 'error':
            return $t('apps.status.error')
        default:
            return props.installation.status
    }
})

const tabs = computed(() => [
    { key: 'general', label: $t('apps.configure.general'), icon: CogIcon },
    { key: 'api', label: $t('apps.configure.api_settings'), icon: GlobeAltIcon },
    { key: 'webhooks', label: $t('apps.webhooks.title'), icon: BoltIcon },
    { key: 'permissions', label: $t('apps.configure.permissions'), icon: ShieldCheckIcon },
    { key: 'advanced', label: $t('apps.configure.advanced'), icon: AdjustmentsHorizontalIcon }
])

const availablePermissions = computed(() => [
    {
        key: 'read_products',
        description: 'Allow the app to read product information',
        icon: CubeTransparentIcon
    },
    {
        key: 'write_products',
        description: 'Allow the app to create and modify products',
        icon: CubeTransparentIcon
    },
    {
        key: 'read_orders',
        description: 'Allow the app to read order information',
        icon: DocumentArrowDownIcon
    },
    {
        key: 'write_orders',
        description: 'Allow the app to create and modify orders',
        icon: DocumentArrowDownIcon
    },
    {
        key: 'read_customers',
        description: 'Allow the app to read customer information',
        icon: ShieldCheckIcon
    },
    {
        key: 'write_customers',
        description: 'Allow the app to create and modify customers',
        icon: ShieldCheckIcon
    }
])

// Methods
const testConnection = async () => {
    testing.value = true
    try {
        await router.post(route('apps.test-connection', props.app.id), {
            settings: settings.value
        }, {
            preserveScroll: true
        })
    } catch (error) {
        console.error('Connection test failed:', error)
    } finally {
        testing.value = false
    }
}

const saveSettings = async () => {
    saving.value = true
    try {
        await router.put(route('apps.configure', props.app.id), {
            settings: settings.value,
            webhooks: webhooks.value
        }, {
            preserveScroll: true
        })
    } catch (error) {
        console.error('Save failed:', error)
    } finally {
        saving.value = false
    }
}

const resetForm = () => {
    settings.value = {
        app_name: props.currentSettings.app_name || '',
        enabled: props.currentSettings.enabled !== false,
        auto_update: props.currentSettings.auto_update !== false,
        debug_mode: props.currentSettings.debug_mode || false,
        api_url: props.currentSettings.api_url || '',
        api_key: props.currentSettings.api_key || '',
        timeout: props.currentSettings.timeout || 30,
        rate_limit: props.currentSettings.rate_limit || 1000,
        permissions: props.currentSettings.permissions || []
    }
    webhooks.value = props.currentSettings.webhooks || []
}

const addWebhook = () => {
    selectedWebhook.value = null
    showWebhookModal.value = true
}

const editWebhook = (webhook) => {
    selectedWebhook.value = webhook
    showWebhookModal.value = true
}

const removeWebhook = (index) => {
    if (confirm($t('apps.webhooks.confirm_delete'))) {
        webhooks.value.splice(index, 1)
    }
}

const testWebhook = async (webhook) => {
    try {
        await router.post(route('apps.test-webhook', props.app.id), {
            webhook: webhook
        }, {
            preserveScroll: true
        })
    } catch (error) {
        console.error('Webhook test failed:', error)
    }
}

const closeWebhookModal = () => {
    showWebhookModal.value = false
    selectedWebhook.value = null
}

const saveWebhook = (webhook) => {
    if (selectedWebhook.value) {
        // Edit existing webhook
        const index = webhooks.value.findIndex(w => w === selectedWebhook.value)
        if (index !== -1) {
            webhooks.value[index] = { ...webhook }
        }
    } else {
        // Add new webhook
        webhooks.value.push({ ...webhook, id: Date.now() })
    }
    closeWebhookModal()
}

const exportSettings = () => {
    const data = {
        settings: settings.value,
        webhooks: webhooks.value,
        exported_at: new Date().toISOString(),
        app_name: props.app.name,
        app_version: props.app.version
    }
    
    const blob = new Blob([JSON.stringify(data, null, 2)], {
        type: 'application/json'
    })
    
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `${props.app.name.toLowerCase().replace(/\s+/g, '-')}-settings.json`
    document.body.appendChild(a)
    a.click()
    document.body.removeChild(a)
    URL.revokeObjectURL(url)
}

const importSettings = (event) => {
    const file = event.target.files[0]
    if (!file) return

    const reader = new FileReader()
    reader.onload = (e) => {
        try {
            const data = JSON.parse(e.target.result)
            if (data.settings) {
                settings.value = { ...settings.value, ...data.settings }
            }
            if (data.webhooks) {
                webhooks.value = data.webhooks
            }
        } catch (error) {
            console.error('Import failed:', error)
            alert($t('apps.messages.import_failed'))
        }
    }
    reader.readAsText(file)
    event.target.value = ''
}

const resetSettings = () => {
    if (confirm($t('apps.messages.confirm_reset_settings'))) {
        settings.value = {
            app_name: '',
            enabled: true,
            auto_update: true,
            debug_mode: false,
            api_url: '',
            api_key: '',
            timeout: 30,
            rate_limit: 1000,
            permissions: []
        }
        webhooks.value = []
    }
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

onMounted(() => {
    // Any initialization logic
})
</script>

<style scoped>
.app-configure {
    max-width: 1200px;
    margin: 0 auto;
    padding: 24px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

.configure-header {
    margin-bottom: 32px;
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 24px;
}

.app-info {
    display: flex;
    gap: 16px;
}

.app-icon .icon-image {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: cover;
}

.app-icon .icon-placeholder {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    font-weight: bold;
}

.app-name {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 4px 0;
}

.app-version {
    color: #6b7280;
    margin: 0 0 8px 0;
}

.app-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    padding: 4px 12px;
    border-radius: 8px;
}

.status-indicator {
    width: 8px;
    height: 8px;
    border-radius: 50%;
}

.status-active {
    color: #10b981;
    background: #d1fae5;
}

.status-active .status-indicator {
    background: #10b981;
}

.status-inactive {
    color: #6b7280;
    background: #f3f4f6;
}

.status-inactive .status-indicator {
    background: #6b7280;
}

.header-actions {
    display: flex;
    gap: 12px;
}

.configure-content {
    flex: 1;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.nav-tabs {
    display: flex;
    background: #f9fafb;
    border-bottom: 1px solid #e5e7eb;
    overflow-x: auto;
}

.nav-tab {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 16px 24px;
    border: none;
    background: none;
    color: #6b7280;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    white-space: nowrap;
    border-bottom: 2px solid transparent;
}

.nav-tab.active {
    color: #3b82f6;
    border-bottom-color: #3b82f6;
    background: white;
}

.nav-tab:hover:not(.active) {
    color: #374151;
    background: rgba(255, 255, 255, 0.5);
}

.tab-icon {
    width: 18px;
    height: 18px;
}

.tab-content {
    padding: 32px;
}

.tab-pane {
    display: flex;
    flex-direction: column;
    gap: 32px;
}

.settings-section {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.settings-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.setting-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.setting-label {
    font-weight: 500;
    color: #374151;
    font-size: 14px;
}

.setting-input {
    padding: 12px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.2s;
}

.setting-input:focus {
    outline: none;
    border-color: #3b82f6;
    box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
}

.setting-help {
    font-size: 12px;
    color: #6b7280;
    margin: 0;
}

.toggle-switch {
    position: relative;
    display: inline-block;
}

.toggle-input {
    opacity: 0;
    width: 0;
    height: 0;
}

.toggle-label {
    display: block;
    width: 48px;
    height: 24px;
    background: #d1d5db;
    border-radius: 12px;
    cursor: pointer;
    transition: background-color 0.2s;
    position: relative;
}

.toggle-label::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 20px;
    height: 20px;
    background: white;
    border-radius: 50%;
    transition: transform 0.2s;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

.toggle-input:checked + .toggle-label {
    background: #3b82f6;
}

.toggle-input:checked + .toggle-label::after {
    transform: translateX(24px);
}

.input-with-toggle {
    position: relative;
}

.toggle-visibility-btn {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6b7280;
    cursor: pointer;
    padding: 4px;
}

.input-with-suffix {
    position: relative;
}

.input-suffix {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: #6b7280;
    font-size: 14px;
    pointer-events: none;
}

.input-with-suffix .setting-input {
    padding-right: 80px;
}

.webhooks-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.webhook-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.webhook-content {
    flex: 1;
}

.webhook-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 12px;
}

.webhook-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.webhook-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: 500;
}

.webhook-status.enabled {
    background: #d1fae5;
    color: #065f46;
}

.webhook-status.disabled {
    background: #fee2e2;
    color: #991b1b;
}

.webhook-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.detail-item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.detail-label {
    color: #6b7280;
    font-weight: 500;
    min-width: 80px;
}

.detail-value {
    color: #111827;
}

.webhook-actions {
    display: flex;
    gap: 8px;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 16px;
}

.permission-item {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    gap: 16px;
}

.permission-content {
    flex: 1;
}

.permission-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.permission-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.permission-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.permission-icon {
    color: #3b82f6;
}

.advanced-actions {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.action-card {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
}

.action-card.danger {
    border-color: #fecaca;
    background: #fef2f2;
}

.action-content {
    flex: 1;
}

.action-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
}

.action-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0;
}

.empty-state {
    text-align: center;
    padding: 48px 24px;
    color: #6b7280;
}

.empty-icon {
    width: 48px;
    height: 48px;
    margin: 0 auto 16px;
    opacity: 0.5;
}

.empty-title {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 8px 0;
}

.empty-description {
    margin: 0;
}

.configure-footer {
    background: white;
    border-top: 1px solid #e5e7eb;
    padding: 24px 0;
    margin-top: 24px;
}

.footer-actions {
    display: flex;
    justify-content: flex-end;
    gap: 12px;
}

.btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 20px;
    border-radius: 8px;
    font-weight: 500;
    font-size: 14px;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
    text-decoration: none;
}

.btn-icon {
    width: 16px;
    height: 16px;
}

.btn-sm {
    padding: 6px 12px;
    font-size: 12px;
}

.btn-sm .btn-icon {
    width: 14px;
    height: 14px;
}

.btn-primary {
    background: #3b82f6;
    color: white;
}

.btn-primary:hover:not(:disabled) {
    background: #2563eb;
}

.btn-secondary {
    background: #6b7280;
    color: white;
}

.btn-secondary:hover:not(:disabled) {
    background: #4b5563;
}

.btn-outline {
    background: white;
    border-color: #d1d5db;
    color: #374151;
}

.btn-outline:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.btn-danger {
    background: #ef4444;
    color: white;
}

.btn-danger:hover:not(:disabled) {
    background: #dc2626;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.hidden {
    display: none;
}

/* Responsive */
@media (max-width: 1024px) {
    .settings-grid,
    .permissions-grid {
        grid-template-columns: 1fr;
    }
    
    .header-content {
        flex-direction: column;
        align-items: stretch;
    }
    
    .header-actions {
        justify-content: flex-end;
    }
}

@media (max-width: 768px) {
    .app-configure {
        padding: 16px;
    }
    
    .tab-content {
        padding: 20px;
    }
    
    .nav-tab {
        padding: 12px 16px;
    }
    
    .webhook-item,
    .permission-item,
    .action-card {
        flex-direction: column;
        align-items: stretch;
    }
    
    .webhook-actions,
    .footer-actions {
        justify-content: stretch;
    }
    
    .footer-actions {
        flex-direction: column;
    }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .app-name {
        color: #f9fafb;
    }
    
    .configure-content {
        background: #1f2937;
        border-color: #374151;
    }
    
    .nav-tabs {
        background: #374151;
        border-color: #4b5563;
    }
    
    .nav-tab {
        color: #d1d5db;
    }
    
    .nav-tab.active {
        background: #1f2937;
    }
    
    .section-title,
    .webhook-name,
    .permission-name,
    .action-title {
        color: #f9fafb;
    }
    
    .setting-input {
        background: #374151;
        border-color: #4b5563;
        color: #f9fafb;
    }
    
    .webhook-item,
    .permission-item,
    .action-card {
        background: #374151;
        border-color: #4b5563;
    }
    
    .action-card.danger {
        background: #450a0a;
        border-color: #7f1d1d;
    }
    
    .configure-footer {
        background: #1f2937;
        border-color: #374151;
    }
}
</style>
