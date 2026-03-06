<template>
  <div class="flex flex-col items-center justify-center py-16 text-center">
    <h2 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Page not found</h2>
    <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-400">
      The page you're looking for doesn't exist or has been moved.
    </p>
    <router-link
      to="/admin/dashboard"
      class="mt-6 inline-flex items-center rounded-lg border border-zinc-200 bg-white px-4 py-2 text-sm font-medium text-zinc-700 shadow-sm hover:bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
    >
      Back to Dashboard
    </router-link>
  </div>
</template>

<script setup>
</script>
