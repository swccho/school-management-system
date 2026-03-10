<template>
  <div
    class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
    :class="{ 'transition hover:border-zinc-300 dark:hover:border-zinc-700': to }"
  >
    <p v-if="label" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">{{ label }}</p>
    <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
      <slot name="value">{{ value }}</slot>
    </p>
    <router-link
      v-if="to"
      :to="to"
      class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
    >
      {{ linkText }}
    </router-link>
  </div>
</template>

<script setup>
defineProps({
  label: { type: String, default: '' },
  value: { type: [String, Number], default: '' },
  to: { type: String, default: '' },
  linkText: { type: String, default: 'View →' },
});
</script>
