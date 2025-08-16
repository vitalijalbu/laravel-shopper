<template>
  <div class="cp-nav">
    <!-- Logo & Brand -->
    <div class="nav-brand">
      <router-link to="/cp" class="brand-link">
        <div class="brand-logo">
          <svg viewBox="0 0 24 24" class="brand-icon">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
          </svg>
        </div>
        <span class="brand-text">Shopper</span>
      </router-link>
    </div>

    <!-- Site Selector (Multisite) -->
    <div v-if="isMultisite" class="site-selector">
      <select v-model="currentSite" class="site-select" @change="changeSite">
        <option v-for="site in sites" :key="site.handle" :value="site.handle">
          {{ site.name }}
        </option>
      </select>
    </div>

    <!-- Main Navigation -->
    <nav class="nav-main">
      <ul class="nav-sections">
        <li v-for="(section, key) in navigation" :key="key" class="nav-section">
          <div
            v-if="section.children && Object.keys(section.children).length > 0"
            class="nav-section-with-children"
          >
            <!-- Section Header -->
            <div
              class="nav-section-header"
              :class="{ 'section-expanded': expandedSections.includes(key) }"
              @click="toggleSection(key)"
            >
              <router-link
                :to="section.url"
                class="nav-section-link"
                :class="{ 'nav-active': isActive(section.url) }"
              >
                <icon :name="section.icon" class="nav-icon" />
                <span class="nav-text">{{ section.display }}</span>
              </router-link>
              <button class="section-toggle" @click.stop="toggleSection(key)">
                <icon
                  :name="
                    expandedSections.includes(key)
                      ? 'chevron-down'
                      : 'chevron-right'
                  "
                  class="toggle-icon"
                />
              </button>
            </div>

            <!-- Section Children -->
            <div
              v-show="expandedSections.includes(key)"
              class="nav-section-children"
            >
              <ul class="nav-children-list">
                <li
                  v-for="(child, childKey) in section.children"
                  :key="childKey"
                  class="nav-child"
                >
                  <router-link
                    :to="child.url"
                    class="nav-child-link"
                    :class="{ 'nav-active': isActive(child.url) }"
                  >
                    <icon :name="child.icon" class="nav-child-icon" />
                    <span class="nav-child-text">{{ child.display }}</span>
                    <span v-if="child.badge" class="nav-badge">{{
                      child.badge
                    }}</span>
                  </router-link>
                </li>
              </ul>
            </div>
          </div>

          <!-- Simple Section (no children) -->
          <div v-else class="nav-section-simple">
            <router-link
              :to="section.url"
              class="nav-section-link"
              :class="{ 'nav-active': isActive(section.url) }"
            >
              <icon :name="section.icon" class="nav-icon" />
              <span class="nav-text">{{ section.display }}</span>
              <span v-if="section.badge" class="nav-badge">{{
                section.badge
              }}</span>
            </router-link>
          </div>
        </li>
      </ul>
    </nav>

    <!-- User Menu -->
    <div class="nav-user">
      <div class="user-info">
        <div class="user-avatar">
          <img v-if="user.avatar" :src="user.avatar" :alt="user.name" />
          <div v-else class="avatar-placeholder">
            {{ user.name?.charAt(0)?.toUpperCase() }}
          </div>
        </div>

        <div class="user-details">
          <div class="user-name">{{ user.name }}</div>
          <div class="user-role">{{ user.role }}</div>
        </div>
      </div>

      <div class="user-menu">
        <button class="user-menu-toggle" @click="showUserMenu = !showUserMenu">
          <icon name="dots-vertical" class="menu-icon" />
        </button>

        <div
          v-if="showUserMenu"
          class="user-menu-dropdown"
          @click.away="showUserMenu = false"
        >
          <router-link to="/cp/account" class="menu-item">
            <icon name="user" class="menu-item-icon" />
            Account
          </router-link>
          <router-link to="/cp/preferences" class="menu-item">
            <icon name="cog" class="menu-item-icon" />
            Preferences
          </router-link>
          <div class="menu-divider"></div>
          <button @click="logout" class="menu-item menu-item-danger">
            <icon name="logout" class="menu-item-icon" />
            Logout
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from "vue";
import { router, usePage } from "@inertiajs/vue3";
import { route } from "ziggy-js";
import { useShopperStore } from "../stores/shopper";

const props = defineProps({
  navigation: {
    type: Object,
    default: () => ({}),
  },
  user: {
    type: Object,
    default: () => ({}),
  },
  sites: {
    type: Array,
    default: () => [],
  },
});

const page = usePage();
const shopperStore = useShopperStore();

// State
const expandedSections = ref([]);
const showUserMenu = ref(false);
const currentSite = ref("default");

// Computed
const isMultisite = computed(() => props.sites.length > 1);

// Methods
const isActive = (url) => {
  return page.url.startsWith(url);
};

const toggleSection = (key) => {
  const index = expandedSections.value.indexOf(key);
  if (index > -1) {
    expandedSections.value.splice(index, 1);
  } else {
    expandedSections.value.push(key);
  }

  // Save preference
  shopperStore.setPreference("expandedNavSections", expandedSections.value);
};

const changeSite = () => {
  // Handle site change
  shopperStore.setPreference("currentSite", currentSite.value);
  // Emit event or trigger site change logic
};

const logout = async () => {
  try {
    await fetch("/cp/auth/logout", { method: "POST" });
    window.location.href = "/cp/auth/login";
  } catch (error) {
    console.error("Logout error:", error);
  }
};

// Auto-expand current section
const autoExpandCurrentSection = () => {
  const path = page.url;

  Object.keys(props.navigation).forEach((key) => {
    const section = props.navigation[key];

    if (section.children) {
      Object.values(section.children).forEach((child) => {
        if (path.startsWith(child.url)) {
          if (!expandedSections.value.includes(key)) {
            expandedSections.value.push(key);
          }
        }
      });
    }
  });
};

// Load preferences
const loadPreferences = () => {
  const savedExpanded = shopperStore.getPreference("expandedNavSections", []);
  expandedSections.value = savedExpanded;

  const savedSite = shopperStore.getPreference("currentSite", "default");
  currentSite.value = savedSite;
};

onMounted(() => {
  loadPreferences();
  autoExpandCurrentSection();
});
</script>

<style scoped>
.cp-nav {
  width: 280px;
  height: 100vh;
  background: #1f2937;
  border-right: 1px solid #374151;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}

.nav-brand {
  padding: 1rem;
  border-bottom: 1px solid #374151;
}

.brand-link {
  display: flex;
  align-items: center;
  text-decoration: none;
  color: white;
}

.brand-logo {
  width: 32px;
  height: 32px;
  margin-right: 12px;
}

.brand-icon {
  width: 100%;
  height: 100%;
  fill: #3b82f6;
}

.brand-text {
  font-size: 1.25rem;
  font-weight: 600;
}

.site-selector {
  padding: 1rem;
  border-bottom: 1px solid #374151;
}

.site-select {
  width: 100%;
  padding: 0.5rem;
  background: #374151;
  color: white;
  border: 1px solid #4b5563;
  border-radius: 0.375rem;
  font-size: 0.875rem;
}

.nav-main {
  flex: 1;
  overflow-y: auto;
  padding: 1rem 0;
}

.nav-sections {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-section {
  margin-bottom: 0.5rem;
}

.nav-section-header {
  display: flex;
  align-items: center;
  position: relative;
}

.nav-section-link,
.nav-child-link {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #d1d5db;
  text-decoration: none;
  transition: all 0.2s;
  flex: 1;
}

.nav-section-link:hover,
.nav-child-link:hover {
  background: #374151;
  color: white;
}

.nav-active {
  background: #1e40af !important;
  color: white !important;
}

.nav-icon,
.nav-child-icon {
  width: 20px;
  height: 20px;
  margin-right: 0.75rem;
  flex-shrink: 0;
}

.nav-text,
.nav-child-text {
  font-size: 0.875rem;
  font-weight: 500;
  flex: 1;
}

.nav-badge {
  background: #ef4444;
  color: white;
  font-size: 0.75rem;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  margin-left: 0.5rem;
}

.section-toggle {
  background: none;
  border: none;
  color: #9ca3af;
  padding: 0.5rem;
  cursor: pointer;
  margin-right: 0.5rem;
}

.toggle-icon {
  width: 16px;
  height: 16px;
}

.nav-section-children {
  background: #111827;
}

.nav-children-list {
  list-style: none;
  padding: 0;
  margin: 0;
}

.nav-child {
  padding-left: 1rem;
}

.nav-child-link {
  padding: 0.5rem 1rem;
  font-size: 0.8125rem;
}

.nav-user {
  padding: 1rem;
  border-top: 1px solid #374151;
}

.user-info {
  display: flex;
  align-items: center;
  margin-bottom: 0.5rem;
}

.user-avatar {
  width: 32px;
  height: 32px;
  border-radius: 50%;
  margin-right: 0.75rem;
  overflow: hidden;
}

.user-avatar img {
  width: 100%;
  height: 100%;
  object-fit: cover;
}

.avatar-placeholder {
  width: 100%;
  height: 100%;
  background: #3b82f6;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: 600;
  font-size: 0.875rem;
}

.user-details {
  flex: 1;
  min-width: 0;
}

.user-name {
  color: white;
  font-size: 0.875rem;
  font-weight: 500;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

.user-role {
  color: #9ca3af;
  font-size: 0.75rem;
}

.user-menu {
  position: relative;
}

.user-menu-toggle {
  background: none;
  border: none;
  color: #9ca3af;
  padding: 0.25rem;
  cursor: pointer;
  position: absolute;
  top: -2rem;
  right: 0;
}

.menu-icon {
  width: 16px;
  height: 16px;
}

.user-menu-dropdown {
  position: absolute;
  bottom: 100%;
  right: 0;
  background: white;
  border: 1px solid #e5e7eb;
  border-radius: 0.5rem;
  box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
  min-width: 200px;
  z-index: 50;
}

.menu-item {
  display: flex;
  align-items: center;
  padding: 0.75rem 1rem;
  color: #374151;
  text-decoration: none;
  font-size: 0.875rem;
  border: none;
  background: none;
  width: 100%;
  cursor: pointer;
  transition: background-color 0.2s;
}

.menu-item:hover {
  background: #f3f4f6;
}

.menu-item-danger {
  color: #dc2626;
}

.menu-item-danger:hover {
  background: #fef2f2;
}

.menu-item-icon {
  width: 16px;
  height: 16px;
  margin-right: 0.75rem;
}

.menu-divider {
  height: 1px;
  background: #e5e7eb;
  margin: 0.5rem 0;
}
</style>
