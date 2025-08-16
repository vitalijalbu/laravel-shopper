<template>
    <div class="test-page">
        <h1 class="text-3xl font-bold text-blue-600 mb-4">Test Vue Component</h1>
        
        <div class="max-w-7xl mx-auto p-6 bg-white rounded-lg shadow">
            <p class="text-gray-700 mb-4">Testing Vue and Tailwind integration</p>
            
            <button 
                @click="incrementCounter"
                class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700"
            >
                Counter: {{ counter }}
            </button>
            
            <div class="mt-4">
                <Icon name="github" class="w-6 h-6 text-gray-600" />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'

const counter = ref(0)

const incrementCounter = () => {
    counter.value++
}
</script>

<style scoped>
.test-page {
    min-height: 100vh;
    background-color: #f9fafb;
    padding: 2rem;
}
</style>
