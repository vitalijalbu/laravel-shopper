<template>
  <div class="social-auth">
    <!-- Available Providers -->
    <div v-if="availableProviders.length > 0" class="social-providers">
      <div class="social-providers-title">
        <span class="text-sm text-gray-600 dark:text-gray-400">
          {{ t("social.labels.available_providers") }}
        </span>
      </div>

      <div class="social-providers-grid">
        <button
          v-for="provider in availableProviders"
          :key="provider.key"
          :disabled="loading"
          :class="[
            'social-provider-button',
            `social-provider-${provider.key}`,
            {
              'social-provider-loading': loading,
              'social-provider-disabled': loading,
            },
          ]"
          @click="handleSocialAuth(provider.key)"
        >
          <div class="social-provider-content">
            <!-- Provider Icon -->
            <div class="social-provider-icon">
              <component
                :is="getProviderIconComponent(provider.key)"
                class="w-5 h-5"
              />
            </div>

            <!-- Provider Text -->
            <span class="social-provider-text">
              {{
                t(
                  authMode === "register"
                    ? "social.actions.register_with"
                    : "social.actions.login_with",
                  { provider: provider.name },
                )
              }}
            </span>

            <!-- Loading Spinner -->
            <div
              v-if="loading && loadingProvider === provider.key"
              class="social-provider-spinner"
            >
              <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                <circle
                  class="opacity-25"
                  cx="12"
                  cy="12"
                  r="10"
                  stroke="currentColor"
                  stroke-width="4"
                />
                <path
                  class="opacity-75"
                  fill="currentColor"
                  d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                />
              </svg>
            </div>
          </div>
        </button>
      </div>
    </div>

    <!-- Or Divider (when showing traditional login form too) -->
    <div
      v-if="showDivider && availableProviders.length > 0"
      class="social-divider"
    >
      <div class="social-divider-line"></div>
      <span class="social-divider-text">{{ t("common.or") }}</span>
      <div class="social-divider-line"></div>
    </div>

    <!-- Connected Accounts (for authenticated users) -->
    <div v-if="user && showConnectedAccounts" class="connected-accounts">
      <h3 class="connected-accounts-title">
        {{ t("social.labels.connected_accounts") }}
      </h3>

      <div v-if="connectedProviders.length > 0" class="connected-providers">
        <div
          v-for="account in connectedProviders"
          :key="account.provider"
          class="connected-provider"
        >
          <div class="connected-provider-info">
            <div class="connected-provider-icon">
              <component
                :is="getProviderIconComponent(account.provider)"
                class="w-5 h-5"
              />
            </div>
            <div class="connected-provider-details">
              <div class="connected-provider-name">
                {{ account.name }}
              </div>
              <div class="connected-provider-meta">
                {{
                  t("social.labels.linked_on", {
                    date: formatDate(account.linked_at),
                  })
                }}
              </div>
            </div>
          </div>

          <button
            v-if="account.can_unlink"
            :disabled="unlinkingProvider === account.provider"
            class="connected-provider-unlink"
            @click="handleUnlinkAccount(account.provider)"
          >
            {{
              unlinkingProvider === account.provider
                ? t("social.status.unlinking")
                : t("social.buttons.unlink")
            }}
          </button>
        </div>
      </div>

      <div v-else class="no-connected-accounts">
        <p class="text-gray-500 dark:text-gray-400 text-sm">
          {{ t("social.labels.no_connected_accounts") }}
        </p>
      </div>
    </div>

    <!-- Error Display -->
    <div v-if="error" class="social-error">
      <div class="social-error-content">
        <svg class="social-error-icon" fill="currentColor" viewBox="0 0 20 20">
          <path
            fill-rule="evenodd"
            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
            clip-rule="evenodd"
          />
        </svg>
        <span class="social-error-message">{{ error }}</span>
      </div>
      <button class="social-error-dismiss" @click="error = null">×</button>
    </div>

    <!-- Success Display -->
    <div v-if="success" class="social-success">
      <div class="social-success-content">
        <svg
          class="social-success-icon"
          fill="currentColor"
          viewBox="0 0 20 20"
        >
          <path
            fill-rule="evenodd"
            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
            clip-rule="evenodd"
          />
        </svg>
        <span class="social-success-message">{{ success }}</span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { useTranslations } from "@/composables/useTranslations";

// Icons - you might want to use a proper icon library
import GoogleIcon from "@/Components/Icons/GoogleIcon.vue";
import FacebookIcon from "@/Components/Icons/FacebookIcon.vue";
import TwitterIcon from "@/Components/Icons/TwitterIcon.vue";
import GitHubIcon from "@/Components/Icons/GitHubIcon.vue";
import LinkedInIcon from "@/Components/Icons/LinkedInIcon.vue";
import AppleIcon from "@/Components/Icons/AppleIcon.vue";
import DiscordIcon from "@/Components/Icons/DiscordIcon.vue";
import MicrosoftIcon from "@/Components/Icons/MicrosoftIcon.vue";

const props = defineProps({
  authMode: {
    type: String,
    default: "login", // 'login' or 'register'
  },
  showDivider: {
    type: Boolean,
    default: true,
  },
  showConnectedAccounts: {
    type: Boolean,
    default: false,
  },
  intendedUrl: {
    type: String,
    default: null,
  },
  apiMode: {
    type: Boolean,
    default: false, // Use API endpoints instead of web routes
  },
});

const emit = defineEmits(["success", "error", "loading-change"]);

// Composables
const { t } = useTranslations();
const page = usePage();

// Data
const availableProviders = ref([]);
const connectedProviders = ref([]);
const loading = ref(false);
const loadingProvider = ref(null);
const unlinkingProvider = ref(null);
const error = ref(null);
const success = ref(null);

// Computed
const user = computed(() => page.props.auth?.user || null);

// Methods
const loadProviders = async () => {
  try {
    const response = await fetch(
      route(
        props.apiMode ? "api.auth.social.providers" : "auth.social.providers",
      ),
    );
    const data = await response.json();

    if (data.success) {
      availableProviders.value = Object.entries(data.data.providers).map(
        ([key, provider]) => ({
          key,
          name: provider.name,
          auth_url: provider.auth_url,
          icon: provider.icon,
          color: provider.color,
        }),
      );
    }
  } catch (err) {
    console.error("Failed to load OAuth providers:", err);
  }
};

const loadConnectedProviders = async () => {
  if (!user.value || !props.showConnectedAccounts) return;

  try {
    const response = await fetch(route("api.auth.social.connected"), {
      headers: {
        Authorization: `Bearer ${getAuthToken()}`,
        Accept: "application/json",
      },
    });

    const data = await response.json();

    if (data.success) {
      connectedProviders.value = data.data.connected_providers || [];
    }
  } catch (err) {
    console.error("Failed to load connected providers:", err);
  }
};

const handleSocialAuth = async (provider) => {
  if (loading.value) return;

  try {
    loading.value = true;
    loadingProvider.value = provider;
    emit("loading-change", true);
    clearMessages();

    if (props.apiMode) {
      await handleApiAuth(provider);
    } else {
      await handleWebAuth(provider);
    }
  } catch (err) {
    handleError(
      t("social.messages.errors.authentication_failed", { provider }),
    );
  } finally {
    loading.value = false;
    loadingProvider.value = null;
    emit("loading-change", false);
  }
};

const handleApiAuth = async (provider) => {
  // Get redirect URL
  const redirectResponse = await fetch(
    route("api.auth.social.redirect", provider),
    {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        Accept: "application/json",
      },
      body: JSON.stringify({
        intended_url: props.intendedUrl,
      }),
    },
  );

  const redirectData = await redirectResponse.json();

  if (!redirectData.success) {
    throw new Error(redirectData.message);
  }

  // Open popup or redirect
  if (window.innerWidth < 768) {
    // Mobile: direct redirect
    window.location.href = redirectData.data.auth_url;
  } else {
    // Desktop: popup window
    await handlePopupAuth(provider, redirectData.data.auth_url);
  }
};

const handleWebAuth = async (provider) => {
  const authUrl = route("auth.social.redirect", provider);
  const params = new URLSearchParams();

  if (props.intendedUrl) {
    params.set("intended", props.intendedUrl);
  }

  const fullUrl = params.toString() ? `${authUrl}?${params}` : authUrl;

  if (window.innerWidth < 768) {
    // Mobile: direct redirect
    window.location.href = fullUrl;
  } else {
    // Desktop: popup window
    await handlePopupAuth(provider, fullUrl);
  }
};

const handlePopupAuth = (provider, authUrl) => {
  return new Promise((resolve, reject) => {
    const popup = window.open(
      authUrl,
      `${provider}_oauth`,
      "width=500,height=600,scrollbars=yes,resizable=yes",
    );

    const checkClosed = setInterval(() => {
      if (popup.closed) {
        clearInterval(checkClosed);
        // Check for success in localStorage or trigger page refresh
        const authResult = localStorage.getItem("oauth_result");
        if (authResult) {
          const result = JSON.parse(authResult);
          localStorage.removeItem("oauth_result");

          if (result.success) {
            handleSuccess(result.message, result.data);
            resolve(result);
          } else {
            handleError(result.message);
            reject(new Error(result.message));
          }
        } else {
          // Fallback: refresh page to check auth state
          window.location.reload();
        }
      }
    }, 1000);

    // Timeout after 5 minutes
    setTimeout(() => {
      clearInterval(checkClosed);
      if (!popup.closed) {
        popup.close();
        reject(new Error("Authentication timeout"));
      }
    }, 300000);
  });
};

const handleUnlinkAccount = async (provider) => {
  if (unlinkingProvider.value) return;

  try {
    unlinkingProvider.value = provider;
    clearMessages();

    const response = await fetch(route("api.auth.social.unlink", provider), {
      method: "DELETE",
      headers: {
        Authorization: `Bearer ${getAuthToken()}`,
        Accept: "application/json",
      },
    });

    const data = await response.json();

    if (data.success) {
      handleSuccess(data.message);
      await loadConnectedProviders();
    } else {
      handleError(data.message);
    }
  } catch (err) {
    handleError(t("social.messages.errors.unlinking_failed", { provider }));
  } finally {
    unlinkingProvider.value = null;
  }
};

const getProviderIconComponent = (provider) => {
  const iconMap = {
    google: GoogleIcon,
    facebook: FacebookIcon,
    twitter: TwitterIcon,
    github: GitHubIcon,
    linkedin: LinkedInIcon,
    apple: AppleIcon,
    discord: DiscordIcon,
    microsoft: MicrosoftIcon,
  };

  return iconMap[provider] || "div";
};

const getAuthToken = () => {
  // Get token from meta tag, localStorage, or Sanctum cookie
  const token = document.querySelector('meta[name="auth-token"]')?.content;
  if (token) return token;

  return localStorage.getItem("auth_token");
};

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString(page.props.locale || "en", {
    year: "numeric",
    month: "short",
    day: "numeric",
  });
};

const handleSuccess = (message, data = null) => {
  success.value = message;
  emit("success", { message, data });

  setTimeout(() => {
    success.value = null;
  }, 5000);
};

const handleError = (message) => {
  error.value = message;
  emit("error", message);
};

const clearMessages = () => {
  error.value = null;
  success.value = null;
};

// Lifecycle
onMounted(async () => {
  await loadProviders();
  await loadConnectedProviders();
});

// Watch for user changes
watch(
  () => user.value,
  async () => {
    if (props.showConnectedAccounts) {
      await loadConnectedProviders();
    }
  },
);
</script>

<style scoped>
.social-auth {
  @apply space-y-4;
}

.social-providers-title {
  @apply text-center mb-3;
}

.social-providers-grid {
  @apply space-y-2;
}

.social-provider-button {
  @apply w-full flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors duration-200;
}

.social-provider-content {
  @apply flex items-center justify-center space-x-3 w-full;
}

.social-provider-icon {
  @apply flex-shrink-0;
}

.social-provider-text {
  @apply flex-1 text-left;
}

.social-provider-spinner {
  @apply flex-shrink-0;
}

/* Provider-specific styling */
.social-provider-google:hover {
  @apply border-red-300 bg-red-50 dark:border-red-600 dark:bg-red-900/20;
}

.social-provider-facebook:hover {
  @apply border-blue-300 bg-blue-50 dark:border-blue-600 dark:bg-blue-900/20;
}

.social-provider-twitter:hover {
  @apply border-sky-300 bg-sky-50 dark:border-sky-600 dark:bg-sky-900/20;
}

.social-provider-github:hover {
  @apply border-gray-400 bg-gray-100 dark:border-gray-500 dark:bg-gray-900/20;
}

.social-divider {
  @apply flex items-center py-4;
}

.social-divider-line {
  @apply flex-1 border-t border-gray-300 dark:border-gray-600;
}

.social-divider-text {
  @apply px-4 text-sm text-gray-500 dark:text-gray-400;
}

.connected-accounts {
  @apply space-y-4;
}

.connected-accounts-title {
  @apply text-lg font-medium text-gray-900 dark:text-white;
}

.connected-providers {
  @apply space-y-3;
}

.connected-provider {
  @apply flex items-center justify-between p-3 border border-gray-200 dark:border-gray-700 rounded-lg;
}

.connected-provider-info {
  @apply flex items-center space-x-3;
}

.connected-provider-icon {
  @apply flex-shrink-0;
}

.connected-provider-details {
  @apply flex flex-col;
}

.connected-provider-name {
  @apply font-medium text-gray-900 dark:text-white;
}

.connected-provider-meta {
  @apply text-sm text-gray-500 dark:text-gray-400;
}

.connected-provider-unlink {
  @apply text-sm text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300 font-medium;
}

.no-connected-accounts {
  @apply text-center py-6;
}

.social-error {
  @apply flex items-center justify-between p-4 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-lg;
}

.social-error-content {
  @apply flex items-center space-x-2;
}

.social-error-icon {
  @apply w-5 h-5 text-red-500 dark:text-red-400 flex-shrink-0;
}

.social-error-message {
  @apply text-sm text-red-800 dark:text-red-200;
}

.social-error-dismiss {
  @apply text-red-500 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 text-xl leading-none;
}

.social-success {
  @apply flex items-center p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg;
}

.social-success-content {
  @apply flex items-center space-x-2;
}

.social-success-icon {
  @apply w-5 h-5 text-green-500 dark:text-green-400 flex-shrink-0;
}

.social-success-message {
  @apply text-sm text-green-800 dark:text-green-200;
}
</style>
