<template>
  <router-view />
</template>

<script setup>
import { onMounted } from "vue";
import { useShopperStore } from "./stores/shopper";

const shopperStore = useShopperStore();

onMounted(() => {
  // Initialize app-wide settings
  if (window.ShopperConfig) {
    // Set any global configuration
    const config = window.ShopperConfig;

    // Set current site if multisite
    if (config.sites && config.current_site) {
      shopperStore.setPreference("currentSite", config.current_site);
    }

    // Set user preferences
    if (config.user_preferences) {
      Object.entries(config.user_preferences).forEach(([key, value]) => {
        shopperStore.setPreference(key, value);
      });
    }
  }
});
</script>
