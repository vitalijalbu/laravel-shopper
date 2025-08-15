<template>
    <Teleport to="body">
        <div v-if="isOpen" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
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
                            <h2 class="app-name">{{ app.name }}</h2>
                            <p class="app-author">{{ $t('apps.details.author') }}: {{ app.author }}</p>
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
                                <span class="rating-text">
                                    {{ app.rating.toFixed(1) }} ({{ app.reviews_count }} {{ $t('apps.reviews.title').toLowerCase() }})
                                </span>
                            </div>
                        </div>
                    </div>
                    <button @click="closeModal" class="close-button">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="modal-body">
                    <div class="content-grid">
                        <!-- Main Content -->
                        <div class="main-content">
                            <!-- Screenshots -->
                            <div v-if="app.screenshots && app.screenshots.length > 0" class="section">
                                <h3 class="section-title">{{ $t('apps.details.screenshots') }}</h3>
                                <div class="screenshots-grid">
                                    <img
                                        v-for="(screenshot, index) in app.screenshots"
                                        :key="index"
                                        :src="screenshot"
                                        :alt="`${app.name} screenshot ${index + 1}`"
                                        class="screenshot"
                                        @click="openScreenshot(screenshot)"
                                    />
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="section">
                                <h3 class="section-title">{{ $t('apps.details.description') }}</h3>
                                <div class="description" v-html="app.description_html || app.description"></div>
                            </div>

                            <!-- Features -->
                            <div v-if="app.features && app.features.length > 0" class="section">
                                <h3 class="section-title">{{ $t('apps.details.features') }}</h3>
                                <ul class="features-list">
                                    <li v-for="feature in app.features" :key="feature" class="feature-item">
                                        <CheckIcon class="feature-icon" />
                                        {{ feature }}
                                    </li>
                                </ul>
                            </div>

                            <!-- Permissions -->
                            <div v-if="app.permissions && app.permissions.length > 0" class="section">
                                <h3 class="section-title">{{ $t('apps.configure.permissions') }}</h3>
                                <div class="permissions-grid">
                                    <div
                                        v-for="permission in app.permissions"
                                        :key="permission"
                                        class="permission-item"
                                    >
                                        <ShieldCheckIcon class="permission-icon" />
                                        {{ $t(`apps.permissions.${permission}`) }}
                                    </div>
                                </div>
                            </div>

                            <!-- Reviews -->
                            <div class="section">
                                <div class="reviews-header">
                                    <h3 class="section-title">{{ $t('apps.reviews.title') }}</h3>
                                    <button v-if="!isInstalled" @click="writeReview" class="write-review-btn">
                                        {{ $t('apps.reviews.write_review') }}
                                    </button>
                                </div>
                                <div v-if="app.reviews && app.reviews.length > 0" class="reviews-list">
                                    <div
                                        v-for="review in app.reviews.slice(0, 3)"
                                        :key="review.id"
                                        class="review-item"
                                    >
                                        <div class="review-header">
                                            <div class="reviewer-info">
                                                <strong>{{ review.user_name }}</strong>
                                                <div class="review-stars">
                                                    <StarIcon
                                                        v-for="n in 5"
                                                        :key="n"
                                                        :class="[
                                                            'star',
                                                            n <= review.rating ? 'star-filled' : 'star-empty'
                                                        ]"
                                                    />
                                                </div>
                                            </div>
                                            <span class="review-date">{{ formatDate(review.created_at) }}</span>
                                        </div>
                                        <p class="review-content">{{ review.content }}</p>
                                    </div>
                                </div>
                                <div v-else class="no-reviews">
                                    {{ $t('apps.reviews.no_reviews') }}
                                </div>
                            </div>
                        </div>

                        <!-- Sidebar -->
                        <div class="sidebar">
                            <!-- Install/Action Buttons -->
                            <div class="action-section">
                                <template v-if="isInstalled">
                                    <div class="installed-status">
                                        <CheckCircleIcon class="status-icon" />
                                        <span>{{ $t('apps.status.installed') }}</span>
                                    </div>
                                    <div class="installed-actions">
                                        <button
                                            v-if="installedApp.status === 'active'"
                                            @click="handleConfigure"
                                            class="btn-configure"
                                        >
                                            {{ $t('apps.actions.configure') }}
                                        </button>
                                        <button
                                            v-else
                                            @click="handleActivate"
                                            class="btn-activate"
                                        >
                                            {{ $t('apps.actions.activate') }}
                                        </button>
                                        <button
                                            @click="handleUninstall"
                                            class="btn-uninstall"
                                        >
                                            {{ $t('apps.actions.uninstall') }}
                                        </button>
                                    </div>
                                </template>
                                <template v-else>
                                    <!-- Pricing Info -->
                                    <div class="pricing-card">
                                        <div v-if="app.price === 0" class="price-free">
                                            {{ $t('apps.pricing.free') }}
                                        </div>
                                        <div v-else class="price-paid">
                                            <span class="price">{{ formatPrice(app.price) }}</span>
                                            <span v-if="app.billing_type === 'monthly'" class="billing-type">
                                                /{{ $t('apps.pricing.month') }}
                                            </span>
                                            <span v-else-if="app.billing_type === 'yearly'" class="billing-type">
                                                /{{ $t('apps.pricing.year') }}
                                            </span>
                                        </div>
                                        <div v-if="app.trial_days > 0" class="trial-info">
                                            {{ $t('apps.pricing.trial_days', { days: app.trial_days }) }}
                                        </div>
                                    </div>

                                    <button
                                        @click="handleInstall"
                                        class="btn-install"
                                        :disabled="!compatible || installing"
                                    >
                                        <span v-if="installing">{{ $t('apps.actions.installing') }}...</span>
                                        <span v-else>{{ $t('apps.actions.install') }}</span>
                                    </button>
                                </template>
                            </div>

                            <!-- App Info -->
                            <div class="info-section">
                                <div class="info-item">
                                    <span class="info-label">{{ $t('apps.details.version') }}</span>
                                    <span class="info-value">{{ app.version }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">{{ $t('apps.details.install_count') }}</span>
                                    <span class="info-value">{{ formatNumber(app.installs) }}</span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">{{ $t('apps.details.last_updated') }}</span>
                                    <span class="info-value">{{ formatDate(app.updated_at) }}</span>
                                </div>
                                <div v-if="app.file_size" class="info-item">
                                    <span class="info-label">{{ $t('apps.details.file_size') }}</span>
                                    <span class="info-value">{{ formatFileSize(app.file_size) }}</span>
                                </div>
                            </div>

                            <!-- Links -->
                            <div v-if="hasLinks" class="links-section">
                                <a
                                    v-if="app.website_url"
                                    :href="app.website_url"
                                    target="_blank"
                                    class="info-link"
                                >
                                    <GlobeAltIcon class="link-icon" />
                                    {{ $t('apps.details.website') }}
                                </a>
                                <a
                                    v-if="app.documentation_url"
                                    :href="app.documentation_url"
                                    target="_blank"
                                    class="info-link"
                                >
                                    <DocumentTextIcon class="link-icon" />
                                    {{ $t('apps.details.documentation') }}
                                </a>
                                <a
                                    v-if="app.support_url"
                                    :href="app.support_url"
                                    target="_blank"
                                    class="info-link"
                                >
                                    <QuestionMarkCircleIcon class="link-icon" />
                                    {{ $t('apps.details.support') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed } from 'vue'
import { useI18n } from 'vue-i18n'
import {
    XMarkIcon,
    StarIcon,
    CheckIcon,
    CheckCircleIcon,
    ShieldCheckIcon,
    GlobeAltIcon,
    DocumentTextIcon,
    QuestionMarkCircleIcon
} from '@heroicons/vue/24/outline'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    app: {
        type: Object,
        required: true
    },
    isOpen: {
        type: Boolean,
        default: false
    },
    installedApps: {
        type: Array,
        default: () => []
    }
})

// Emits
const emit = defineEmits(['close', 'install', 'uninstall', 'configure'])

// State
const installing = ref(false)

// Computed
const isInstalled = computed(() => {
    return props.installedApps.some(ia => ia.app.id === props.app.id)
})

const installedApp = computed(() => {
    return props.installedApps.find(ia => ia.app.id === props.app.id)
})

const compatible = computed(() => {
    // Add compatibility logic here
    return true
})

const hasLinks = computed(() => {
    return props.app.website_url || props.app.documentation_url || props.app.support_url
})

// Methods
const closeModal = () => {
    emit('close')
}

const handleInstall = async () => {
    installing.value = true
    try {
        await emit('install', props.app)
        closeModal()
    } finally {
        installing.value = false
    }
}

const handleUninstall = () => {
    emit('uninstall', props.app)
    closeModal()
}

const handleConfigure = () => {
    emit('configure', props.app)
    closeModal()
}

const handleActivate = () => {
    // Handle activation logic
}

const writeReview = () => {
    // Handle write review logic
}

const openScreenshot = (screenshot) => {
    // Handle screenshot preview
    window.open(screenshot, '_blank')
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

const formatPrice = (price) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: 'USD'
    }).format(price)
}

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatFileSize = (bytes) => {
    const sizes = ['Bytes', 'KB', 'MB', 'GB']
    if (bytes === 0) return '0 Byte'
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)))
    return Math.round(bytes / Math.pow(1024, i) * 100) / 100 + ' ' + sizes[i]
}
</script>

<style scoped>
.modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 1000;
    padding: 20px;
}

.modal-content {
    background: white;
    border-radius: 16px;
    width: 100%;
    max-width: 1200px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.app-info {
    display: flex;
    gap: 16px;
}

.app-icon .icon-image {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    object-fit: cover;
}

.app-icon .icon-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 16px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 32px;
    font-weight: bold;
}

.app-details h2.app-name {
    font-size: 28px;
    font-weight: 700;
    color: #111827;
    margin: 0 0 8px 0;
}

.app-author {
    color: #6b7280;
    margin: 0 0 12px 0;
}

.app-rating {
    display: flex;
    align-items: center;
    gap: 8px;
}

.stars {
    display: flex;
    gap: 2px;
}

.star {
    width: 16px;
    height: 16px;
}

.star-filled {
    color: #fbbf24;
}

.star-empty {
    color: #d1d5db;
}

.rating-text {
    color: #6b7280;
    font-size: 14px;
}

.close-button {
    background: none;
    border: none;
    cursor: pointer;
    color: #6b7280;
    padding: 8px;
    border-radius: 8px;
    transition: background-color 0.2s;
}

.close-button:hover {
    background: #f3f4f6;
}

.modal-body {
    flex: 1;
    overflow-y: auto;
    padding: 24px;
}

.content-grid {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 32px;
}

.section {
    margin-bottom: 32px;
}

.section-title {
    font-size: 20px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 16px 0;
}

.screenshots-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.screenshot {
    width: 100%;
    height: 150px;
    object-fit: cover;
    border-radius: 8px;
    cursor: pointer;
    transition: transform 0.2s;
}

.screenshot:hover {
    transform: scale(1.05);
}

.description {
    color: #374151;
    line-height: 1.6;
}

.features-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.feature-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 0;
    color: #374151;
}

.feature-icon {
    width: 20px;
    height: 20px;
    color: #10b981;
    flex-shrink: 0;
}

.permissions-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 12px;
}

.permission-item {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    background: #f9fafb;
    border-radius: 8px;
    font-size: 14px;
    color: #374151;
}

.permission-icon {
    width: 16px;
    height: 16px;
    color: #3b82f6;
    flex-shrink: 0;
}

.reviews-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.write-review-btn {
    background: #3b82f6;
    color: white;
    border: none;
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    cursor: pointer;
    transition: background-color 0.2s;
}

.write-review-btn:hover {
    background: #2563eb;
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.review-item {
    padding: 16px;
    background: #f9fafb;
    border-radius: 8px;
}

.review-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.reviewer-info {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.review-stars {
    display: flex;
    gap: 2px;
}

.review-stars .star {
    width: 14px;
    height: 14px;
}

.review-date {
    color: #6b7280;
    font-size: 12px;
}

.review-content {
    color: #374151;
    margin: 0;
    line-height: 1.5;
}

.no-reviews {
    text-align: center;
    color: #6b7280;
    padding: 32px;
}

.sidebar {
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.action-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
}

.installed-status {
    display: flex;
    align-items: center;
    gap: 8px;
    color: #10b981;
    font-weight: 500;
    margin-bottom: 16px;
}

.status-icon {
    width: 20px;
    height: 20px;
}

.installed-actions {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.pricing-card {
    text-align: center;
    margin-bottom: 16px;
}

.price-free {
    font-size: 24px;
    font-weight: 700;
    color: #10b981;
}

.price-paid .price {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
}

.billing-type {
    color: #6b7280;
    font-size: 16px;
}

.trial-info {
    margin-top: 8px;
    color: #f59e0b;
    font-size: 14px;
    font-weight: 500;
}

.btn-install,
.btn-configure,
.btn-activate,
.btn-uninstall {
    width: 100%;
    padding: 12px;
    border-radius: 8px;
    font-weight: 600;
    border: none;
    cursor: pointer;
    transition: background-color 0.2s;
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

.info-section {
    background: #f9fafb;
    padding: 20px;
    border-radius: 12px;
}

.info-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #e5e7eb;
}

.info-item:last-child {
    border-bottom: none;
}

.info-label {
    color: #6b7280;
    font-size: 14px;
}

.info-value {
    color: #111827;
    font-weight: 500;
    font-size: 14px;
}

.links-section {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.info-link {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: #f3f4f6;
    border-radius: 8px;
    color: #374151;
    text-decoration: none;
    font-size: 14px;
    transition: background-color 0.2s;
}

.info-link:hover {
    background: #e5e7eb;
}

.link-icon {
    width: 16px;
    height: 16px;
    flex-shrink: 0;
}

/* Responsive */
@media (max-width: 1024px) {
    .content-grid {
        grid-template-columns: 1fr;
    }
    
    .sidebar {
        order: -1;
    }
}

@media (max-width: 640px) {
    .modal-content {
        margin: 10px;
        max-width: none;
    }
    
    .modal-header {
        padding: 16px;
    }
    
    .modal-body {
        padding: 16px;
    }
    
    .app-info {
        flex-direction: column;
        gap: 12px;
    }
}

/* Dark mode */
@media (prefers-color-scheme: dark) {
    .modal-content {
        background: #1f2937;
    }
    
    .modal-header {
        border-color: #374151;
    }
    
    .app-name {
        color: #f9fafb;
    }
    
    .close-button:hover {
        background: #374151;
    }
    
    .section-title {
        color: #f9fafb;
    }
    
    .description {
        color: #d1d5db;
    }
    
    .feature-item {
        color: #d1d5db;
    }
    
    .permission-item {
        background: #374151;
        color: #d1d5db;
    }
    
    .review-item {
        background: #374151;
    }
    
    .review-content {
        color: #d1d5db;
    }
    
    .action-section,
    .info-section {
        background: #374151;
    }
    
    .price-paid .price {
        color: #f9fafb;
    }
    
    .info-value {
        color: #f9fafb;
    }
    
    .info-link {
        background: #4b5563;
        color: #d1d5db;
    }
    
    .info-link:hover {
        background: #6b7280;
    }
}
</style>
