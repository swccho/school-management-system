<template>
  <header class="sticky top-0 z-20 flex h-14 shrink-0 items-center justify-between border-b border-zinc-200 bg-white px-4 dark:border-zinc-800 dark:bg-zinc-900 md:px-6">
    <div class="flex items-center gap-4">
      <button
        type="button"
        class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300 md:hidden"
        aria-label="Toggle sidebar"
        @click="toggleSidebar"
      >
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
      <div class="flex min-w-0 flex-1 items-center gap-3">
        <div class="min-w-0">
          <h1 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
            {{ pageTitle }}
          </h1>
          <p v-if="pageSubtitle" class="text-xs text-zinc-500 dark:text-zinc-400">
            {{ pageSubtitle }}
          </p>
        </div>
        <HelpLink />
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ userDisplayName }}</span>
      <button
        type="button"
        class="rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
        @click="handleLogout"
      >
        Log out
      </button>
      <div class="h-8 w-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300">
        {{ userInitial }}
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppStore } from '../stores/appStore.js';
import { useAuth } from '../composables/useAuth.js';
import HelpLink from './HelpLink.vue';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const { user, logout } = useAuth();

const toggleSidebar = () => appStore.toggleSidebar();

const pageTitle = computed(() => route.meta?.title ?? 'Admin');
const pageSubtitle = computed(() => route.meta?.subtitle ?? null);

const userDisplayName = computed(() => user.value?.name ?? 'Admin');
const userInitial = computed(() => (user.value?.name ? user.value.name.charAt(0).toUpperCase() : 'A'));

async function handleLogout() {
  await logout();
  router.push('/admin/login');
}
</script>
