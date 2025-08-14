import { createApp, h } from 'vue'
import { createInertiaApp } from '@inertiajs/vue3'
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers'
import Layout from './Components/Layout/Layout.vue'

createInertiaApp({
  title: (title) => `${title} | Shopper Admin`,
  resolve: (name) => {
    const page = resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    )
    
    // Apply layout to all pages
    page.then((module) => {
      module.default.layout = module.default.layout || Layout
    })
    
    return page
  },
  setup({ el, App, props, plugin }) {
    return createApp({ render: () => h(App, props) })
      .use(plugin)
      .mount(el)
  },
})
