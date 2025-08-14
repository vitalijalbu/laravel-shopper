import { createRouter, createWebHistory } from 'vue-router'
import { useShopperStore } from './stores/shopper'

// Layout Components
import CpLayout from './components/cp-layout.vue'

// Page Components  
import Dashboard from './pages/dashboard.vue'
import CollectionsIndex from './pages/collections-index.vue'
import EntriesIndex from './pages/entries-index.vue'
import EntryCreate from './pages/entry-create.vue'
import EntryEdit from './pages/entry-edit.vue'
import Sites from './pages/sites.vue'
import Users from './pages/users.vue'
import Assets from './pages/assets.vue'

// E-commerce Pages
import ProductsIndex from './pages/products-index.vue'
import OrdersIndex from './pages/orders-index.vue'
import CustomersIndex from './pages/customers-index.vue'
import Inventory from './pages/inventory.vue'
import Analytics from './pages/analytics.vue'

// Auth Pages
import Login from './pages/auth/login.vue'

const routes = [
  // Auth Routes
  {
    path: '/cp/auth/login',
    name: 'cp.auth.login',
    component: Login,
    meta: { 
      requiresAuth: false,
      layout: 'auth'
    }
  },

  // Control Panel Routes (with layout)
  {
    path: '/cp',
    component: CpLayout,
    meta: { 
      requiresAuth: true 
    },
    children: [
      // Dashboard
      {
        path: '',
        name: 'cp.dashboard',
        component: Dashboard,
        meta: { 
          title: 'Dashboard',
          permission: 'access_cp'
        }
      },

      // Collections
      {
        path: 'collections',
        name: 'cp.collections.index',
        component: CollectionsIndex,
        meta: { 
          title: 'Collections',
          permission: 'view_collections'
        }
      },
      {
        path: 'collections/:collection',
        name: 'cp.entries.index',
        component: EntriesIndex,
        meta: { 
          title: 'Entries',
          permission: 'view_entries',
          previewable: true
        }
      },
      {
        path: 'collections/:collection/entries/create',
        name: 'cp.entries.create', 
        component: EntryCreate,
        meta: { 
          title: 'Create Entry',
          permission: 'create_entries',
          previewable: true
        }
      },
      {
        path: 'collections/:collection/entries/:id',
        name: 'cp.entries.edit',
        component: EntryEdit,
        meta: { 
          title: 'Edit Entry',
          permission: 'edit_entries',
          previewable: true
        }
      },

      // E-commerce Shortcuts  
      {
        path: 'ecommerce/products',
        redirect: '/cp/collections/products'
      },
      {
        path: 'ecommerce/orders',
        redirect: '/cp/collections/orders'
      },
      {
        path: 'ecommerce/customers', 
        redirect: '/cp/collections/customers'
      },
      {
        path: 'ecommerce/inventory',
        name: 'cp.ecommerce.inventory',
        component: Inventory,
        meta: { 
          title: 'Inventory Management',
          permission: 'manage_inventory'
        }
      },
      {
        path: 'ecommerce/analytics',
        name: 'cp.ecommerce.analytics',
        component: Analytics,
        meta: { 
          title: 'E-commerce Analytics',
          permission: 'view_analytics'
        }
      },

      // Sites (Multisite)
      {
        path: 'sites',
        name: 'cp.sites.index',
        component: Sites,
        meta: { 
          title: 'Sites',
          permission: 'manage_sites'
        }
      },

      // Users & Permissions
      {
        path: 'users',
        name: 'cp.users.index',
        component: Users,
        meta: { 
          title: 'Users',
          permission: 'view_users'
        }
      },

      // Assets
      {
        path: 'assets',
        name: 'cp.assets.index',
        component: Assets,
        meta: { 
          title: 'Assets',
          permission: 'view_assets'
        }
      }
    ]
  },

  // Frontend Preview Routes
  {
    path: '/preview/:collection/:id',
    name: 'preview.entry',
    component: () => import('./pages/preview.vue'),
    meta: { 
      requiresAuth: true,
      layout: 'preview'
    }
  },

  // Catch-all 404
  {
    path: '/:pathMatch(.*)*',
    name: 'not-found',
    redirect: '/cp'
  }
]

const router = createRouter({
  history: createWebHistory('/'),
  routes
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
  const shopperStore = useShopperStore()
  
  // Check if route requires authentication
  if (to.meta.requiresAuth !== false) {
    const isAuthenticated = shopperStore.currentUser !== null
    
    if (!isAuthenticated) {
      // Try to load user from session/token
      try {
        const response = await fetch('/cp/api/user')
        if (response.ok) {
          const user = await response.json()
          shopperStore.setCurrentUser(user)
          shopperStore.setPermissions(user.permissions || [])
        } else {
          return next('/cp/auth/login')
        }
      } catch (error) {
        return next('/cp/auth/login')
      }
    }
  }

  // Check permissions
  if (to.meta.permission) {
    const hasPermission = shopperStore.hasPermission(to.meta.permission)
    
    if (!hasPermission) {
      shopperStore.addToast('You do not have permission to access this page', 'error')
      return next('/cp')
    }
  }

  // Set page title
  if (to.meta.title) {
    document.title = `${to.meta.title} | Shopper`
  }

  next()
})

// After each navigation
router.afterEach((to, from) => {
  // Track page views for analytics
  if (to.meta.permission && to.meta.permission.includes('view_analytics')) {
    // Track analytics page view
  }
})

export default router
