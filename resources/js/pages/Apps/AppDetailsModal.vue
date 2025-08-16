<template>
    <div v-if="show" class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black bg-opacity-25" @click="$emit('close')"></div>
            
            <div class="relative bg-white rounded-lg shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-2xl font-bold text-gray-900">{{ app.name }}</h2>
                        <button @click="$emit('close')" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                    
                    <div class="space-y-6">
                        <div class="flex items-center space-x-4">
                            <div class="w-16 h-16 bg-gray-100 rounded-xl flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                                </svg>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">{{ app.name }}</h3>
                                <p class="text-gray-500">by {{ app.author }}</p>
                                <div class="flex items-center space-x-2 mt-1">
                                    <div class="flex items-center space-x-1">
                                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                        </svg>
                                        <span class="text-sm text-gray-500">{{ app.rating || '4.8' }}</span>
                                    </div>
                                    <span class="text-lg font-bold text-blue-600">{{ app.price || 'Free' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Description</h4>
                            <p class="text-gray-600">{{ app.description }}</p>
                        </div>
                        
                        <div>
                            <h4 class="font-medium text-gray-900 mb-2">Features</h4>
                            <ul class="list-disc list-inside text-gray-600 space-y-1">
                                <li v-for="feature in app.features || defaultFeatures" :key="feature">{{ feature }}</li>
                            </ul>
                        </div>
                        
                        <div class="flex justify-end space-x-3 pt-6 border-t">
                            <button @click="$emit('close')" class="px-4 py-2 text-gray-700 border border-gray-300 rounded-md hover:bg-gray-50">
                                Cancel
                            </button>
                            <button class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Install App
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
defineProps({
    show: {
        type: Boolean,
        default: false
    },
    app: {
        type: Object,
        required: true
    }
})

defineEmits(['close'])

const defaultFeatures = [
    'Easy installation and setup',
    'Full integration with Control Panel',
    'Regular updates and support',
    'Secure and reliable'
]
</script>
