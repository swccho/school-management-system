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
      <div>
        <h1 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ pageTitle }}</h1>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ teacherName }}</span>
      <button
        type="button"
        class="rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
        @click="handleLogout"
      >
        Log out
      </button>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAppStore } from '../stores/appStore.js';
import { useAuthStore } from '../stores/authStore.js';
import { useTeacherAuth } from '../composables/useTeacherAuth.js';

const route = useRoute();
const router = useRouter();
const appStore = useAppStore();
const authStore = useAuthStore();
const { logout } = useTeacherAuth();

const toggleSidebar = () => appStore.toggleSidebar();
const pageTitle = computed(() => route.meta?.title ?? 'Teachers Portal');
const teacherName = computed(() => authStore.teacherName);

async function handleLogout() {
  await logout();
  router.push('/teacher/login');
}
</script>
