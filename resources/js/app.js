import { createApp } from 'vue'
import { createPinia } from 'pinia'
import router from './router'
import './components/icons'

// Import main layout component
import App from './App.vue'

// Import global styles
import '../css/app.css'

// Create Vue app
const app = createApp(App)

// Install Pinia for state management
app.use(createPinia())

// Install Vue Router
app.use(router)

// Global Properties
app.config.globalProperties.$shopperConfig = window.ShopperConfig || {}

// Global Components Registration
import Icon from './components/icon.vue'
import Page from './components/page.vue'
import DataTable from './components/data-table.vue'
import Modal from './components/modal.vue'
import ConfirmModal from './components/confirm-modal.vue'

app.component('Icon', Icon)
app.component('Page', Page) 
app.component('DataTable', DataTable)
app.component('Modal', Modal)
app.component('ConfirmModal', ConfirmModal)

// Error Handler
app.config.errorHandler = (err, vm, info) => {
  console.error('Vue Error:', err, info)
  
  // Send to error reporting service
  if (window.Sentry) {
    window.Sentry.captureException(err, {
      contexts: {
        vue: {
          componentName: vm.$options.name,
          propsData: vm.$options.propsData,
          info: info
        }
      }
    })
  }
}

// Mount the app
app.mount('#app')

// Hot Module Replacement (HMR)
if (import.meta.hot) {
  import.meta.hot.accept()
}

// Service Worker Registration (for PWA features)
if ('serviceWorker' in navigator && import.meta.env.PROD) {
  window.addEventListener('load', () => {
    navigator.serviceWorker.register('/sw.js')
      .then((registration) => {
        console.log('SW registered: ', registration)
      })
      .catch((registrationError) => {
        console.log('SW registration failed: ', registrationError)
      })
  })
}

// Export for debugging
if (import.meta.env.DEV) {
  window.ShopperApp = app
}
