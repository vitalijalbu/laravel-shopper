<template>
    <div class="app-card" :class="{ 'featured': featured }">
        <div class="app-card-content">
            <!-- App Icon -->
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

            <!-- App Info -->
            <div class="app-info">
                <div class="app-header">
                    <h3 class="app-name">{{ app.name }}</h3>
                    <div class="app-rating" v-if="app.rating > 0">
                        <div class="stars">
                            <StarIcon
                                v-for="n in 5"
                                :key="n"
                                :class="[
                                    'star',
                                    n <= Math.round(app.rating) ? 'star-filled' : 'star-empty'
                                ]"
                            />
                        </div>
                        <span class="rating-text">{{ app.rating.toFixed(1) }}</span>
                    </div>
                </div>

                <p class="app-description">{{ app.description }}</p>

                <div class="app-meta">
                    <span class="app-author">{{ $t('apps.details.author') }}: {{ app.author }}</span>
                    <span class="app-installs">{{ formatNumber(app.installs) }} {{ $t('apps.details.install_count').toLowerCase() }}</span>
                </div>

                <!-- Price -->
                <div class="app-pricing">
                    <span v-if="app.price === 0" class="price-free">
                        {{ $t('apps.pricing.free') }}
                    </span>
                    <span v-else class="price-paid">
                        {{ formatPrice(app.price) }}
                        <span v-if="app.billing_type === 'monthly'" class="billing-type">
                            /{{ $t('apps.pricing.month') }}
                        </span>
                        <span v-else-if="app.billing_type === 'yearly'" class="billing-type">
                            /{{ $t('apps.pricing.year') }}
                        </span>
                    </span>
                    <span v-if="app.trial_days > 0" class="trial-info">
                        {{ $t('apps.pricing.trial_days', { days: app.trial_days }) }}
                    </span>
                </div>

                <!-- Categories -->
                <div class="app-categories">
                    <span class="category-tag">
                        {{ $t(`apps.categories.${app.category}`) }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="app-actions">
            <template v-if="isInstalled">
                <div class="installed-status">
                    <CheckCircleIcon class="status-icon" />
                    <span>{{ $t('apps.status.installed') }}</span>
                </div>
                <div class="action-buttons">
                    <button
                        v-if="installedApp.status === 'active'"
                        @click="$emit('configure', app)"
                        class="btn-configure"
                    >
                        {{ $t('apps.actions.configure') }}
                    </button>
                    <button
                        v-else
                        @click="$emit('activate', app)"
                        class="btn-activate"
                    >
                        {{ $t('apps.actions.activate') }}
                    </button>
                    <button
                        @click="$emit('uninstall', app)"
                        class="btn-uninstall"
                    >
                        {{ $t('apps.actions.uninstall') }}
                    </button>
                </div>
            </template>
            <template v-else>
                <button
                    @click="$emit('install', app)"
                    class="btn-install"
                    :disabled="!compatible"
                >
                    {{ $t('apps.actions.install') }}
                </button>
                <button
                    @click="$emit('view-details', app)"
                    class="btn-details"
                >
                    {{ $t('apps.actions.view_details') }}
                </button>
            </template>
        </div>

        <!-- Featured Badge -->
        <div v-if="featured" class="featured-badge">
            {{ $t('apps.store.featured') }}
        </div>

        <!-- Update Available Badge -->
        <div v-if="hasUpdate" class="update-badge">
            {{ $t('apps.status.update_available') }}
        </div>
    </div>
</template>

<script setup>
import { computed } from 'vue'
import { useI18n } from 'vue-i18n'
import { StarIcon, CheckCircleIcon } from '@heroicons/vue/24/solid'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    app: {
        type: Object,
        required: true
    },
    featured: {
        type: Boolean,
        default: false
    },
    installedApps: {
        type: Array,
        default: () => []
    }
})

// Emits
defineEmits(['install', 'uninstall', 'activate', 'configure', 'view-details'])

// Computed
const isInstalled = computed(() => {
    return props.installedApps.some(ia => ia.app.id === props.app.id)
})

const installedApp = computed(() => {
    return props.installedApps.find(ia => ia.app.id === props.app.id)
})

const hasUpdate = computed(() => {
    if (!isInstalled.value) return false
    return installedApp.value?.app.version !== props.app.version
})

const compatible = computed(() => {
    // Add compatibility logic here
    return true
})

// Methods
const formatNumber = (num) => {
    if (num >= 1000000) {
        return (num / 1000000).toFixed(1) + 'M'
    }
    if (num >= 1000) {
        return (num / 1000).toFixed(1) + 'K'
    }
    return num.toString()
}

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price)
}
</script>

<style scoped>
.app-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.2s ease;
    position: relative;
    display: flex;
    flex-direction: column;
    height: 100%;
}

.app-card:hover {
    border-color: #3b82f6;
    box-shadow: 0 10px 25px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
    transform: translateY(-2px);
}

.app-card.featured {
    border-color: #f59e0b;
    background: linear-gradient(135deg, #fffbeb 0%, white 100%);
}

.app-card-content {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.app-icon {
    align-self: center;
}

.icon-image {
    width: 64px;
    height: 64px;
    border-radius: 12px;
    object-fit: cover;
}

.icon-placeholder {
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

.app-info {
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.app-header {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.app-name {
    font-size: 18px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.app-rating {
    display: flex;
    align-items: center;
    gap: 6px;
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    width: 14px;
    height: 14px;
}

.star-filled {
    color: #fbbf24;
}

.star-empty {
    color: #d1d5db;
}

.rating-text {
    font-size: 12px;
    color: #6b7280;
}

.app-description {
    font-size: 14px;
    color: #6b7280;
    line-height: 1.4;
    margin: 0;
    overflow: hidden;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
}

.app-meta {
    display: flex;
    flex-direction: column;
    gap: 4px;
    font-size: 12px;
    color: #9ca3af;
}

.app-pricing {
    display: flex;
    align-items: center;
    gap: 8px;
    flex-wrap: wrap;
}

.price-free {
    color: #059669;
    font-weight: 600;
}

.price-paid {
    color: #1f2937;
    font-weight: 600;
}

.billing-type {
    font-weight: normal;
    color: #6b7280;
}

.trial-info {
    font-size: 11px;
    color: #f59e0b;
    background: #fffbeb;
    padding: 2px 6px;
    border-radius: 4px;
}

.app-categories {
    display: flex;
    gap: 6px;
    flex-wrap: wrap;
}

.category-tag {
    font-size: 11px;
    background: #f3f4f6;
    color: #374151;
    padding: 4px 8px;
    border-radius: 6px;
    font-weight: 500;
}

.app-actions {
    margin-top: 16px;
    padding-top: 16px;
    border-top: 1px solid #f3f4f6;
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.installed-status {
    display: flex;
    align-items: center;
    gap: 6px;
    color: #059669;
    font-size: 14px;
    font-weight: 500;
}

.status-icon {
    width: 16px;
    height: 16px;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-install,
.btn-details,
.btn-configure,
.btn-activate,
.btn-uninstall {
    padding: 8px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    transition: all 0.2s ease;
    border: none;
    cursor: pointer;
    flex: 1;
}

.btn-install {
    background: #3b82f6;
    color: white;
}

.btn-install:hover:not(:disabled) {
    background: #2563eb;
}

.btn-install:disabled {
    background: #d1d5db;
    cursor: not-allowed;
}

.btn-details {
    background: #f3f4f6;
    color: #374151;
}

.btn-details:hover {
    background: #e5e7eb;
}

.btn-configure,
.btn-activate {
    background: #10b981;
    color: white;
}

.btn-configure:hover,
.btn-activate:hover {
    background: #059669;
}

.btn-uninstall {
    background: #ef4444;
    color: white;
}

.btn-uninstall:hover {
    background: #dc2626;
}

.featured-badge {
    position: absolute;
    top: -6px;
    right: -6px;
    background: #f59e0b;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.update-badge {
    position: absolute;
    top: -6px;
    left: -6px;
    background: #3b82f6;
    color: white;
    font-size: 10px;
    font-weight: 600;
    padding: 4px 8px;
    border-radius: 4px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Dark mode styles */
@media (prefers-color-scheme: dark) {
    .app-card {
        background: #1f2937;
        border-color: #374151;
    }
    
    .app-card:hover {
        border-color: #3b82f6;
    }
    
    .app-card.featured {
        background: linear-gradient(135deg, #1f2937 0%, #111827 100%);
        border-color: #f59e0b;
    }
    
    .app-name {
        color: #f9fafb;
    }
    
    .app-description {
        color: #d1d5db;
    }
    
    .price-paid {
        color: #f9fafb;
    }
    
    .category-tag {
        background: #374151;
        color: #d1d5db;
    }
    
    .btn-details {
        background: #374151;
        color: #d1d5db;
    }
    
    .btn-details:hover {
        background: #4b5563;
    }
}
</style>
