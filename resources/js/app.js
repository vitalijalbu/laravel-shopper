import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { createPinia } from "pinia";
import { ZiggyVue } from "ziggy-js";
import "@/components/Icons";

// Import global styles
import "../css/app.css";

// Configure CSRF token for requests
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.Laravel = {
        csrfToken: token.content
    };
}

createInertiaApp({
  resolve: (name) => {
    const pages = import.meta.glob("./pages/**/*.vue", { eager: true });
    return pages[`./pages/${name}.vue`];
  },
  setup({ el, App, props, plugin }) {
    const app = createApp({ render: () => h(App, props) })
      .use(plugin)
      .use(createPinia())
      .use(ZiggyVue);

    // Configure CSRF token for Inertia requests
    if (window.Laravel && window.Laravel.csrfToken) {
      app.config.globalProperties.$csrf = window.Laravel.csrfToken;
    }

    // Global Properties
    app.config.globalProperties.$shopperConfig = window.ShopperConfig || {};

    // Global Components Registration
    import("@/components/icon.vue").then((module) =>
      app.component("Icon", module.default),
    );
    import("@/components/page.vue").then((module) =>
      app.component("Page", module.default),
    );
    import("@/components/data-table.vue").then((module) =>
      app.component("DataTable", module.default),
    );
    import("@/components/modal.vue").then((module) =>
      app.component("Modal", module.default),
    );

    // Error Handler
    app.config.errorHandler = (err, vm, info) => {
      console.error("Vue Error:", err, info);

      // Send to error reporting service
      if (window.Sentry) {
        window.Sentry.captureException(err, {
          contexts: {
            vue: {
              componentName: vm?.$options?.name,
              info: info,
            },
          },
        });
      }
    };

    return app.mount(el);
  },
});

// Export for debugging
if (import.meta.env.DEV) {
  window.ShopperApp = app;
}
