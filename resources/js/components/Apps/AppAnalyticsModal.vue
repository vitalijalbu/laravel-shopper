<template>
    <Teleport to="body">
        <div v-if="isOpen" class="modal-overlay" @click="closeModal">
            <div class="modal-content" @click.stop>
                <div class="modal-header">
                    <div class="header-info">
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
                        <div class="app-details">
                            <h2 class="app-name">{{ installation.app.name }}</h2>
                            <p class="analytics-subtitle">{{ $t('apps.analytics.title') }}</p>
                        </div>
                    </div>
                    <button @click="closeModal" class="close-button">
                        <XMarkIcon class="w-6 h-6" />
                    </button>
                </div>

                <div class="modal-body">
                    <!-- Time Range Selector -->
                    <div class="time-range-selector">
                        <div class="range-buttons">
                            <button
                                v-for="range in timeRanges"
                                :key="range.value"
                                @click="selectedTimeRange = range.value"
                                :class="['range-btn', { active: selectedTimeRange === range.value }]"
                            >
                                {{ range.label }}
                            </button>
                        </div>
                        <div class="custom-range" v-if="selectedTimeRange === 'custom'">
                            <input
                                v-model="customStartDate"
                                type="date"
                                class="date-input"
                            />
                            <span class="date-separator">{{ $t('apps.analytics.to') }}</span>
                            <input
                                v-model="customEndDate"
                                type="date"
                                class="date-input"
                            />
                        </div>
                    </div>

                    <!-- Key Metrics -->
                    <div class="metrics-grid">
                        <div class="metric-card">
                            <div class="metric-icon usage-icon">
                                <ChartBarIcon class="w-6 h-6" />
                            </div>
                            <div class="metric-info">
                                <div class="metric-value">{{ formatNumber(analytics.total_usage || 0) }}</div>
                                <div class="metric-label">{{ $t('apps.analytics.total_usage') }}</div>
                                <div class="metric-change" :class="usageChangeClass">
                                    {{ usageChangeText }}
                                </div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-icon performance-icon">
                                <BoltIcon class="w-6 h-6" />
                            </div>
                            <div class="metric-info">
                                <div class="metric-value">{{ (analytics.success_rate || 0).toFixed(1) }}%</div>
                                <div class="metric-label">{{ $t('apps.analytics.success_rate') }}</div>
                                <div class="metric-change" :class="successRateChangeClass">
                                    {{ successRateChangeText }}
                                </div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-icon uptime-icon">
                                <ClockIcon class="w-6 h-6" />
                            </div>
                            <div class="metric-info">
                                <div class="metric-value">{{ (analytics.uptime || 0).toFixed(1) }}%</div>
                                <div class="metric-label">{{ $t('apps.analytics.uptime') }}</div>
                                <div class="metric-change" :class="uptimeChangeClass">
                                    {{ uptimeChangeText }}
                                </div>
                            </div>
                        </div>

                        <div class="metric-card">
                            <div class="metric-icon api-icon">
                                <CpuChipIcon class="w-6 h-6" />
                            </div>
                            <div class="metric-info">
                                <div class="metric-value">{{ formatNumber(analytics.api_calls || 0) }}</div>
                                <div class="metric-label">{{ $t('apps.analytics.api_calls') }}</div>
                                <div class="metric-change" :class="apiCallsChangeClass">
                                    {{ apiCallsChangeText }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Charts Section -->
                    <div class="charts-section">
                        <!-- Usage Chart -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <h3 class="chart-title">{{ $t('apps.analytics.usage') }}</h3>
                                <div class="chart-controls">
                                    <select v-model="usageChartType" class="chart-select">
                                        <option value="line">{{ $t('apps.analytics.line_chart') }}</option>
                                        <option value="bar">{{ $t('apps.analytics.bar_chart') }}</option>
                                    </select>
                                </div>
                            </div>
                            <div class="chart-container">
                                <canvas ref="usageChart" class="analytics-chart"></canvas>
                            </div>
                        </div>

                        <!-- Performance Chart -->
                        <div class="chart-card">
                            <div class="chart-header">
                                <h3 class="chart-title">{{ $t('apps.analytics.performance') }}</h3>
                            </div>
                            <div class="chart-container">
                                <canvas ref="performanceChart" class="analytics-chart"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Detailed Stats -->
                    <div class="detailed-stats">
                        <div class="stats-grid">
                            <div class="stat-section">
                                <h4 class="section-title">{{ $t('apps.analytics.usage_breakdown') }}</h4>
                                <div class="stat-items">
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.daily_avg') }}</span>
                                        <span class="stat-value">{{ formatNumber(analytics.daily_average || 0) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.peak_usage') }}</span>
                                        <span class="stat-value">{{ formatNumber(analytics.peak_usage || 0) }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.off_peak_usage') }}</span>
                                        <span class="stat-value">{{ formatNumber(analytics.off_peak_usage || 0) }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-section">
                                <h4 class="section-title">{{ $t('apps.analytics.error_analysis') }}</h4>
                                <div class="stat-items">
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.total_errors') }}</span>
                                        <span class="stat-value error">{{ analytics.error_count || 0 }}</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.error_rate') }}</span>
                                        <span class="stat-value error">{{ (analytics.error_rate || 0).toFixed(2) }}%</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.last_error') }}</span>
                                        <span class="stat-value">
                                            {{ analytics.last_error_at ? formatDate(analytics.last_error_at) : $t('apps.analytics.no_errors') }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="stat-section">
                                <h4 class="section-title">{{ $t('apps.analytics.response_times') }}</h4>
                                <div class="stat-items">
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.avg_response_time') }}</span>
                                        <span class="stat-value">{{ (analytics.avg_response_time || 0).toFixed(0) }}ms</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.fastest_response') }}</span>
                                        <span class="stat-value success">{{ (analytics.min_response_time || 0).toFixed(0) }}ms</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">{{ $t('apps.analytics.slowest_response') }}</span>
                                        <span class="stat-value warning">{{ (analytics.max_response_time || 0).toFixed(0) }}ms</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div v-if="recentActivity.length > 0" class="activity-section">
                        <h4 class="section-title">{{ $t('apps.analytics.recent_activity') }}</h4>
                        <div class="activity-list">
                            <div
                                v-for="activity in recentActivity"
                                :key="activity.id"
                                class="activity-item"
                            >
                                <div class="activity-icon" :class="getActivityIconClass(activity.type)">
                                    <component :is="getActivityIcon(activity.type)" class="w-4 h-4" />
                                </div>
                                <div class="activity-content">
                                    <div class="activity-message">{{ activity.message }}</div>
                                    <div class="activity-time">{{ formatDateTime(activity.created_at) }}</div>
                                </div>
                                <div v-if="activity.status" class="activity-status" :class="activity.status">
                                    {{ $t(`apps.analytics.status_${activity.status}`) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Export Options -->
                    <div class="export-section">
                        <h4 class="section-title">{{ $t('apps.analytics.export_data') }}</h4>
                        <div class="export-buttons">
                            <button @click="exportCSV" class="btn btn-outline">
                                <DocumentTextIcon class="btn-icon" />
                                {{ $t('apps.analytics.export_csv') }}
                            </button>
                            <button @click="exportPDF" class="btn btn-outline">
                                <DocumentArrowDownIcon class="btn-icon" />
                                {{ $t('apps.analytics.export_pdf') }}
                            </button>
                            <button @click="exportJSON" class="btn btn-outline">
                                <CodeBracketIcon class="btn-icon" />
                                {{ $t('apps.analytics.export_json') }}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </Teleport>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted, watch } from 'vue'
import { useI18n } from 'vue-i18n'
import {
    XMarkIcon,
    ChartBarIcon,
    BoltIcon,
    ClockIcon,
    CpuChipIcon,
    DocumentTextIcon,
    DocumentArrowDownIcon,
    CodeBracketIcon,
    CheckCircleIcon,
    ExclamationTriangleIcon,
    XCircleIcon,
    InformationCircleIcon
} from '@heroicons/vue/24/outline'

const { t: $t } = useI18n()

// Props
const props = defineProps({
    installation: {
        type: Object,
        required: true
    },
    isOpen: {
        type: Boolean,
        default: false
    }
})

// Emits
const emit = defineEmits(['close'])

// State
const selectedTimeRange = ref('7d')
const customStartDate = ref('')
const customEndDate = ref('')
const usageChartType = ref('line')
const usageChart = ref(null)
const performanceChart = ref(null)

// Mock data - replace with actual API calls
const analytics = ref({
    total_usage: 15420,
    success_rate: 98.5,
    uptime: 99.2,
    api_calls: 8750,
    daily_average: 2203,
    peak_usage: 3450,
    off_peak_usage: 1200,
    error_count: 12,
    error_rate: 0.14,
    last_error_at: '2024-01-15T10:30:00Z',
    avg_response_time: 245,
    min_response_time: 89,
    max_response_time: 1205,
    usage_change: 12.5,
    success_rate_change: -0.3,
    uptime_change: 0.8,
    api_calls_change: 23.1
})

const recentActivity = ref([
    {
        id: 1,
        type: 'success',
        message: 'API call completed successfully',
        created_at: '2024-01-15T14:30:00Z',
        status: 'success'
    },
    {
        id: 2,
        type: 'warning',
        message: 'High response time detected (1.2s)',
        created_at: '2024-01-15T14:25:00Z',
        status: 'warning'
    },
    {
        id: 3,
        type: 'error',
        message: 'Connection timeout to external service',
        created_at: '2024-01-15T14:20:00Z',
        status: 'error'
    },
    {
        id: 4,
        type: 'info',
        message: 'Configuration updated',
        created_at: '2024-01-15T14:15:00Z',
        status: 'info'
    }
])

// Computed
const timeRanges = computed(() => [
    { value: '24h', label: $t('apps.analytics.last_24h') },
    { value: '7d', label: $t('apps.analytics.last_7d') },
    { value: '30d', label: $t('apps.analytics.last_30d') },
    { value: '90d', label: $t('apps.analytics.last_90d') },
    { value: 'custom', label: $t('apps.analytics.custom_range') }
])

const usageChangeClass = computed(() => ({
    'metric-change-positive': analytics.value.usage_change > 0,
    'metric-change-negative': analytics.value.usage_change < 0
}))

const usageChangeText = computed(() => {
    const change = analytics.value.usage_change
    const sign = change > 0 ? '+' : ''
    return `${sign}${change.toFixed(1)}%`
})

const successRateChangeClass = computed(() => ({
    'metric-change-positive': analytics.value.success_rate_change > 0,
    'metric-change-negative': analytics.value.success_rate_change < 0
}))

const successRateChangeText = computed(() => {
    const change = analytics.value.success_rate_change
    const sign = change > 0 ? '+' : ''
    return `${sign}${change.toFixed(1)}%`
})

const uptimeChangeClass = computed(() => ({
    'metric-change-positive': analytics.value.uptime_change > 0,
    'metric-change-negative': analytics.value.uptime_change < 0
}))

const uptimeChangeText = computed(() => {
    const change = analytics.value.uptime_change
    const sign = change > 0 ? '+' : ''
    return `${sign}${change.toFixed(1)}%`
})

const apiCallsChangeClass = computed(() => ({
    'metric-change-positive': analytics.value.api_calls_change > 0,
    'metric-change-negative': analytics.value.api_calls_change < 0
}))

const apiCallsChangeText = computed(() => {
    const change = analytics.value.api_calls_change
    const sign = change > 0 ? '+' : ''
    return `${sign}${change.toFixed(1)}%`
})

// Methods
const closeModal = () => {
    emit('close')
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

const formatDate = (date) => {
    return new Date(date).toLocaleDateString()
}

const formatDateTime = (date) => {
    return new Date(date).toLocaleString()
}

const getActivityIcon = (type) => {
    const icons = {
        success: CheckCircleIcon,
        warning: ExclamationTriangleIcon,
        error: XCircleIcon,
        info: InformationCircleIcon
    }
    return icons[type] || InformationCircleIcon
}

const getActivityIconClass = (type) => {
    return `activity-icon-${type}`
}

const exportCSV = () => {
    // Handle CSV export
    console.log('Exporting CSV...')
}

const exportPDF = () => {
    // Handle PDF export
    console.log('Exporting PDF...')
}

const exportJSON = () => {
    // Handle JSON export
    console.log('Exporting JSON...')
}

const initializeCharts = () => {
    // Initialize Chart.js charts here
    // This would require Chart.js to be installed and imported
    console.log('Initializing charts...')
}

const updateCharts = () => {
    // Update charts based on selected time range
    console.log('Updating charts for range:', selectedTimeRange.value)
}

// Watchers
watch(selectedTimeRange, updateCharts)

onMounted(() => {
    if (props.isOpen) {
        initializeCharts()
    }
})

watch(() => props.isOpen, (isOpen) => {
    if (isOpen) {
        setTimeout(initializeCharts, 100)
    }
})
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
    max-width: 1400px;
    max-height: 90vh;
    overflow: hidden;
    display: flex;
    flex-direction: column;
    box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 24px;
    border-bottom: 1px solid #e5e7eb;
}

.header-info {
    display: flex;
    align-items: center;
    gap: 16px;
}

.app-icon .icon-image {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    object-fit: cover;
}

.app-icon .icon-placeholder {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    background: linear-gradient(135deg, #3b82f6, #1d4ed8);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    font-weight: bold;
}

.app-name {
    font-size: 20px;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.analytics-subtitle {
    color: #6b7280;
    margin: 4px 0 0 0;
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
    display: flex;
    flex-direction: column;
    gap: 24px;
}

.time-range-selector {
    background: #f9fafb;
    padding: 16px;
    border-radius: 12px;
}

.range-buttons {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
}

.range-btn {
    padding: 8px 16px;
    border: 1px solid #d1d5db;
    border-radius: 8px;
    background: white;
    color: #374151;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
}

.range-btn.active {
    background: #3b82f6;
    color: white;
    border-color: #3b82f6;
}

.range-btn:hover:not(.active) {
    background: #f3f4f6;
}

.custom-range {
    display: flex;
    align-items: center;
    gap: 12px;
    margin-top: 12px;
}

.date-input {
    padding: 8px 12px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
    font-size: 14px;
}

.date-separator {
    color: #6b7280;
    font-size: 14px;
}

.metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 16px;
}

.metric-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 16px;
}

.metric-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.usage-icon {
    background: #dbeafe;
    color: #1d4ed8;
}

.performance-icon {
    background: #d1fae5;
    color: #065f46;
}

.uptime-icon {
    background: #fef3c7;
    color: #92400e;
}

.api-icon {
    background: #ede9fe;
    color: #6b21a8;
}

.metric-info {
    flex: 1;
}

.metric-value {
    font-size: 24px;
    font-weight: 700;
    color: #111827;
    line-height: 1;
}

.metric-label {
    color: #6b7280;
    font-size: 14px;
    margin-top: 4px;
}

.metric-change {
    font-size: 12px;
    font-weight: 500;
    margin-top: 4px;
}

.metric-change-positive {
    color: #059669;
}

.metric-change-negative {
    color: #dc2626;
}

.charts-section {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 24px;
}

.chart-card {
    background: white;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: 20px;
}

.chart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
}

.chart-title {
    font-size: 16px;
    font-weight: 600;
    color: #111827;
    margin: 0;
}

.chart-select {
    padding: 4px 8px;
    border: 1px solid #d1d5db;
    border-radius: 4px;
    font-size: 12px;
}

.chart-container {
    height: 300px;
    position: relative;
}

.analytics-chart {
    width: 100%;
    height: 100%;
}

.detailed-stats {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 24px;
}

.stat-section {
    background: white;
    border-radius: 8px;
    padding: 16px;
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: #111827;
    margin: 0 0 12px 0;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.stat-items {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.stat-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 8px 0;
    border-bottom: 1px solid #f3f4f6;
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-label {
    color: #6b7280;
    font-size: 13px;
}

.stat-value {
    font-weight: 600;
    color: #111827;
    font-size: 13px;
}

.stat-value.success {
    color: #059669;
}

.stat-value.warning {
    color: #d97706;
}

.stat-value.error {
    color: #dc2626;
}

.activity-section {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
}

.activity-list {
    display: flex;
    flex-direction: column;
    gap: 12px;
}

.activity-item {
    background: white;
    border-radius: 8px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 12px;
}

.activity-icon {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.activity-icon-success {
    background: #d1fae5;
    color: #059669;
}

.activity-icon-warning {
    background: #fef3c7;
    color: #d97706;
}

.activity-icon-error {
    background: #fee2e2;
    color: #dc2626;
}

.activity-icon-info {
    background: #dbeafe;
    color: #2563eb;
}

.activity-content {
    flex: 1;
}

.activity-message {
    color: #111827;
    font-size: 14px;
    font-weight: 500;
}

.activity-time {
    color: #6b7280;
    font-size: 12px;
    margin-top: 2px;
}

.activity-status {
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.activity-status.success {
    background: #d1fae5;
    color: #059669;
}

.activity-status.warning {
    background: #fef3c7;
    color: #d97706;
}

.activity-status.error {
    background: #fee2e2;
    color: #dc2626;
}

.activity-status.info {
    background: #dbeafe;
    color: #2563eb;
}

.export-section {
    background: #f9fafb;
    border-radius: 12px;
    padding: 20px;
}

.export-buttons {
    display: flex;
    gap: 12px;
    flex-wrap: wrap;
}

.btn {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 16px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.2s;
    border: 1px solid transparent;
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

.btn-icon {
    width: 16px;
    height: 16px;
}

/* Responsive */
@media (max-width: 1024px) {
    .charts-section {
        grid-template-columns: 1fr;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 768px) {
    .modal-content {
        margin: 10px;
        max-width: none;
    }
    
    .metrics-grid {
        grid-template-columns: 1fr;
    }
    
    .range-buttons {
        flex-direction: column;
    }
    
    .range-btn {
        text-align: center;
    }
    
    .custom-range {
        flex-direction: column;
        align-items: stretch;
    }
    
    .export-buttons {
        flex-direction: column;
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
    
    .time-range-selector,
    .detailed-stats,
    .activity-section,
    .export-section {
        background: #374151;
    }
    
    .metric-card,
    .chart-card,
    .stat-section,
    .activity-item {
        background: #374151;
        border-color: #4b5563;
    }
    
    .metric-value,
    .chart-title,
    .section-title,
    .activity-message {
        color: #f9fafb;
    }
    
    .range-btn {
        background: #1f2937;
        border-color: #4b5563;
        color: #d1d5db;
    }
    
    .range-btn.active {
        background: #3b82f6;
        color: white;
    }
    
    .range-btn:hover:not(.active) {
        background: #4b5563;
    }
    
    .date-input,
    .chart-select {
        background: #1f2937;
        border-color: #4b5563;
        color: #f9fafb;
    }
    
    .btn-outline {
        background: #1f2937;
        border-color: #4b5563;
        color: #d1d5db;
    }
    
    .btn-outline:hover {
        background: #4b5563;
    }
}
</style>
