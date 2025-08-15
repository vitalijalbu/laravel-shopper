<template>
    <div class="installed-app-card" :class="{ 'system-app': installation.app.system }">
        <!-- Selection Checkbox -->
        <div class="card-header">
            <input
                v-if="!installation.app.system"
                v-model="isSelected"
                type="checkbox"
                class="selection-checkbox"
                @change="handleSelection"
            />
            <div class="app-status" :class="statusClass">
                <div class="status-indicator"></div>
                <span>{{ statusText }}</span>
            </div>
        </div>

        <!-- App Content -->
        <div class="card-content">
            <!-- App Icon and Info -->
            <div class="app-header">
                <div class="app-icon">
                    <img
                        v-if="installation.app.icon"
                        :src="installation.app.icon"
                        :alt="installation.app.name"
                        class="icon-image"
                    />
                    <div v-else class="icon-placeholder">
                        {{ installation.app.name.charAt(0).toUpperCase() }}
                    </div>
                </div>
                
                <div class="app-info">
                    <h3 class="app-name">{{ installation.app.name }}</h3>
                    <p class="app-description">{{ installation.app.description }}</p>
                    <div class="app-meta">
                        <span class="app-version">v{{ installation.app.version }}</span>
                        <span class="app-author">{{ installation.app.author }}</span>
                    </div>
                </div>
            </div>

            <!-- Installation Info -->
            <div class="installation-info">
                <div class="info-item">
                    <span class="info-label">{{ $t('apps.installed.installed_on') }}:</span>
                    <span class="info-value">{{ formatDate(installation.installed_at) }}</span>
                </div>
                <div v-if="installation.last_used_at" class="info-item">
                    <span class="info-label">{{ $t('apps.analytics.last_used') }}:</span>
                    <span class="info-value">{{ formatDate(installation.last_used_at) }}</span>
                </div>
                <div v-if="hasSubscription" class="info-item">
                    <span class="info-label">{{ $t('apps.subscription.status') }}:</span>
                    <span class="info-value" :class="subscriptionStatusClass">
                        {{ subscriptionStatusText }}
                    </span>
                </div>
            </div>

            <!-- Quick Stats -->
            <div v-if="installation.analytics" class="quick-stats">
                <div class="stat-item">
                    <div class="stat-value">{{ formatNumber(installation.analytics.api_calls || 0) }}</div>
                    <div class="stat-label">{{ $t('apps.analytics.api_calls') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ (installation.analytics.uptime || 0).toFixed(1) }}%</div>
                    <div class="stat-label">{{ $t('apps.analytics.uptime') }}</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ (installation.analytics.success_rate || 0).toFixed(1) }}%</div>
                    <div class="stat-label">{{ $t('apps.analytics.success_rate') }}</div>
                </div>
            </div>

            <!-- Warnings/Alerts -->
            <div v-if="hasWarnings" class="warnings-section">
                <div v-if="hasUpdate" class="warning-item update-warning">
                    <ExclamationTriangleIcon class="warning-icon" />
                    <span>{{ $t('apps.status.update_available') }}</span>
                    <button @click="handleUpdate" class="update-btn">
                        {{ $t('apps.actions.update') }}
                    </button>
                </div>
                <div v-if="subscriptionExpiring" class="warning-item subscription-warning">
                    <ClockIcon class="warning-icon" />
                    <span>{{ $t('apps.subscription.expires_on', { date: formatDate(installation.subscription_expires_at) }) }}</span>
                    <button @click="handleRenew" class="renew-btn">
                        {{ $t('apps.subscription.renew') }}
                    </button>
                </div>
                <div v-if="hasErrors" class="warning-item error-warning">
                    <ExclamationCircleIcon class="warning-icon" />
                    <span>{{ $t('apps.analytics.errors') }}: {{ installation.analytics.error_count }}</span>
                    <button @click="viewErrorLogs" class="view-logs-btn">
                        {{ $t('apps.actions.view_logs') }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card-actions">
            <div class="primary-actions">
                <button
                    v-if="installation.status === 'inactive'"
                    @click="$emit('activate', installation)"
                    class="btn btn-success"
                    :disabled="loading"
                >
                    <PlayIcon class="btn-icon" />
                    {{ $t('apps.actions.activate') }}
                </button>
                <button
                    v-else-if="installation.status === 'active'"
                    @click="$emit('deactivate', installation)"
                    class="btn btn-warning"
                    :disabled="loading || installation.app.system"
                >
                    <PauseIcon class="btn-icon" />
                    {{ $t('apps.actions.deactivate') }}
                </button>
                
                <button
                    @click="$emit('configure', installation)"
                    class="btn btn-secondary"
                    :disabled="installation.status !== 'active'"
                >
                    <CogIcon class="btn-icon" />
                    {{ $t('apps.actions.configure') }}
                </button>
            </div>

            <div class="secondary-actions">
                <button
                    @click="$emit('view-analytics', installation)"
                    class="btn btn-outline"
                    title="{{ $t('apps.analytics.title') }}"
                >
                    <ChartBarIcon class="btn-icon" />
                </button>
                
                <div class="dropdown" ref="dropdownRef">
                    <button
                        @click="toggleDropdown"
                        class="btn btn-outline dropdown-trigger"
                        title="{{ $t('apps.actions.more') }}"
                    >
                        <EllipsisVerticalIcon class="btn-icon" />
                    </button>
                    <div v-if="showDropdown" class="dropdown-menu">
                        <button
                            v-if="hasUpdate"
                            @click="handleUpdate"
                            class="dropdown-item"
                        >
                            <ArrowDownTrayIcon class="dropdown-icon" />
                            {{ $t('apps.actions.update') }}
                        </button>
                        <button
                            @click="exportSettings"
                            class="dropdown-item"
                        >
                            <DocumentArrowDownIcon class="dropdown-icon" />
                            {{ $t('apps.actions.export_settings') }}
                        </button>
                        <button
                            @click="resetSettings"
                            class="dropdown-item"
                        >
                            <ArrowPathIcon class="dropdown-icon" />
                            {{ $t('apps.configure.reset_settings') }}
                        </button>
                        <div class="dropdown-divider"></div>
                        <button
                            @click="$emit('uninstall', installation)"
                            class="dropdown-item danger"
                            :disabled="installation.app.system"
                        >
                            <TrashIcon class="dropdown-icon" />
                            {{ $t('apps.actions.uninstall') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'
import { useI18n } from 'vue-i18n'
import {
    PlayIcon,
    PauseIcon,
    CogIcon,
    ChartBarIcon,
    EllipsisVerticalIcon,
    ExclamationTriangleIcon,
    ExclamationCircleIcon,
    ClockIcon,
    ArrowDownTrayIcon,
    DocumentArrowDownIcon,
    ArrowPathIcon,
    TrashIcon
} from '@heroicons/vue/24/outline'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    installation: {
        type: Object,
        required: true
    },
    selected: {
        type: Boolean,
        default: false
    }
})

// Emits
const emit = defineEmits([
    'activate',
    'deactivate', 
    'configure',
    'uninstall',
    'view-analytics',
    'selection-changed'
])

// State
const loading = ref(false)
const showDropdown = ref(false)
const dropdownRef = ref(null)
const isSelected = ref(props.selected)

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

const hasSubscription = computed(() => {
    return props.installation.subscription_status && 
           props.installation.app.price > 0
})

const subscriptionStatusClass = computed(() => {
    const status = props.installation.subscription_status
    return {
        'text-green-600': status === 'active',
        'text-yellow-600': status === 'trial',
        'text-red-600': ['expired', 'cancelled'].includes(status)
    }
})

const subscriptionStatusText = computed(() => {
    const status = props.installation.subscription_status
    if (!status) return ''
    
    return $t(`apps.subscription.${status}`)
})

const hasUpdate = computed(() => {
    return props.installation.available_version && 
           props.installation.available_version !== props.installation.app.version
})

const subscriptionExpiring = computed(() => {
    if (!props.installation.subscription_expires_at) return false
    
    const expiresAt = new Date(props.installation.subscription_expires_at)
    const daysLeft = Math.ceil((expiresAt - new Date()) / (1000 * 60 * 60 * 24))
    
    return daysLeft <= 7 && daysLeft > 0
})

const hasErrors = computed(() => {
    return props.installation.analytics?.error_count > 0
})

const hasWarnings = computed(() => {
    return hasUpdate.value || subscriptionExpiring.value || hasErrors.value
})

// Methods
const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M'
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toString()
}

const toggleDropdown = () => {
    showDropdown.value = !showDropdown.value
}

const closeDropdown = (event) => {
    if (dropdownRef.value && !dropdownRef.value.contains(event.target)) {
        showDropdown.value = false
    }
}

const handleSelection = () => {
    emit('selection-changed', props.installation.id, isSelected.value)
}

const handleUpdate = () => {
    // Handle app update
    showDropdown.value = false
}

const handleRenew = () => {
    // Handle subscription renewal
}

const exportSettings = () => {
    // Handle settings export
    showDropdown.value = false
}

const resetSettings = () => {
    if (confirm($t('apps.messages.confirm_reset_settings'))) {
        // Handle settings reset
    }
    showDropdown.value = false
}

const viewErrorLogs = () => {
    // Handle error logs viewing
}

onMounted(() => {
    document.addEventListener('click', closeDropdown)
})

onUnmounted(() => {
    document.removeEventListener('click', closeDropdown)
})
</script>

<style scoped>
.installed-app-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s ease;
    position: relative;
}

.installed-app-card:hover {
    border-color: #d1d5db;
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
}

.installed-app-card.system-app {
    border-color: #fbbf24;
    background: linear-gradient(135deg, #fffbeb 0%, white 100%);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.selection-checkbox {
    width: 16px;
    height: 16px;
    border-radius: 4px;
    border: 1px solid #d1d5db;
    cursor: pointer;
}

.app-status {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 12px;
    font-weight: 500;
    padding: 4px 8px;
    border-radius: 6px;
}

.status-indicator {
    width: 6px;
    height: 6px;
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

.status-error {
    color: #ef4444;
    background: #fee2e2;
}

.status-error .status-indicator {
    background: #ef4444;
}

.card-content {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.app-header {
    display: flex;
    gap: 12px;
}

.app-icon .icon-image {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    object-fit: cover;
}

.app-icon .icon-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 10px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: bold;
}

.app-info {
    flex: 1;
    min-width: 0;
}

.app-name {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 4px 0;
}

.app-description {
    font-size: 14px;
    color: #6b7280;
    margin: 0 0 8px 0;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    line-clamp: 2;
}

.app-meta {
    display: flex;
    gap: 12px;
    font-size: 12px;
    color: #9ca3af;
}

.installation-info {
    background: #f9fafb;
    padding: 12px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 12px;
}

.info-label {
    color: #6b7280;
}

.info-value {
    color: #111827;
    font-weight: 500;
}

.quick-stats {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
    padding: 12px;
    background: #f9fafb;
    border-radius: 8px;
}

.stat-item {
    text-align: center;
}

.stat-value {
    font-size: 18px;
    font-weight: 700;
    color: #111827;
}

.stat-label {
    font-size: 10px;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-top: 2px;
}

.warnings-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.warning-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
}

.warning-icon {
    width: 14px;
    height: 14px;
    flex-shrink: 0;
}

.update-warning {
    background: #dbeafe;
    color: #1e40af;
}

.subscription-warning {
    background: #fef3c7;
    color: #92400e;
}

.error-warning {
    background: #fee2e2;
    color: #b91c1c;
}

.update-btn,
.renew-btn,
.view-logs-btn {
    margin-left: auto;
    padding: 2px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 500;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
}

.update-btn {
    background: #3b82f6;
    color: white;
}

.update-btn:hover {
    background: #2563eb;
}

.renew-btn {
    background: #f59e0b;
    color: white;
}

.renew-btn:hover {
    background: #d97706;
}

.view-logs-btn {
    background: #ef4444;
    color: white;
}

.view-logs-btn:hover {
    background: #dc2626;
}

.card-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
}

.primary-actions {
    display: flex;
    gap: 8px;
}

.secondary-actions {
    display: flex;
    gap: 4px;
    position: relative;
}

.btn {
    display: flex;
    align-items: center;
    gap: 6px;
    padding: 8px 12px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 500;
    border: 1px solid transparent;
    cursor: pointer;
    transition: all 0.2s;
}

.btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.btn-icon {
    width: 14px;
    height: 14px;
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
}

.dropdown {
    position: relative;
}

.dropdown-menu {
    position: absolute;
    top: 100%;
    right: 0;
    margin-top: 4px;
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 8px;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    padding: 4px;
    min-width: 160px;
    z-index: 10;
}

.dropdown-item {
    display: flex;
    align-items: center;
    gap: 8px;
    width: 100%;
    padding: 8px 12px;
    border: none;
    background: none;
    color: #374151;
    font-size: 12px;
    text-align: left;
    border-radius: 4px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.dropdown-item:hover:not(:disabled) {
    background: #f3f4f6;
}

.dropdown-item.danger {
    color: #ef4444;
}

.dropdown-item.danger:hover:not(:disabled) {
    background: #fee2e2;
}

.dropdown-item:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.dropdown-icon {
    width: 14px;
    height: 14px;
}

.dropdown-divider {
    height: 1px;
    background: #e5e7eb;
    margin: 4px 0;
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .installed-app-card {
        background: #1f2937;
        border-color: #374151;
    }
    
    .installed-app-card:hover {
        border-color: #4b5563;
    }
    
    .installed-app-card.system-app {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-color: #f59e0b;
    }
    
    .app-name {
        color: #f9fafb;
    }
    
    .app-description {
        color: #d1d5db;
    }
    
    .info-value {
        color: #f9fafb;
    }
    
    .installation-info,
    .quick-stats {
        background: #374151;
    }
    
    .stat-value {
        color: #f9fafb;
    }
    
    .btn-outline {
        background: #374151;
        border-color: #4b5563;
        color: #d1d5db;
    }
    
    .btn-outline:hover {
        background: #4b5563;
    }
    
    .dropdown-menu {
        background: #1f2937;
        border-color: #374151;
    }
    
    .dropdown-item {
        color: #d1d5db;
    }
    
    .dropdown-item:hover:not(:disabled) {
        background: #374151;
    }
}
</style>
