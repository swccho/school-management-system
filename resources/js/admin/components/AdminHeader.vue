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
        <h1 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
          {{ pageTitle }}
        </h1>
        <p v-if="pageSubtitle" class="text-xs text-zinc-500 dark:text-zinc-400">
          {{ pageSubtitle }}
        </p>
      </div>
    </div>
    <div class="flex items-center gap-3">
      <span class="text-sm text-zinc-500 dark:text-zinc-400">Admin</span>
      <div class="h-8 w-8 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300">
        A
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import { useAppStore } from '../stores/appStore.js';

const route = useRoute();
const appStore = useAppStore();

const toggleSidebar = () => appStore.toggleSidebar();

const pageTitle = computed(() => route.meta?.title ?? 'Admin');
const pageSubtitle = computed(() => route.meta?.subtitle ?? null);
</script>
