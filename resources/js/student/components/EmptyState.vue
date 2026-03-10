<template>
  <div class="flex flex-col items-center justify-center rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
    <div v-if="$slots.icon" class="mb-3 text-zinc-400 dark:text-zinc-500">
      <slot name="icon" />
    </div>
    <h3 v-if="title" class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ title }}</h3>
    <p v-if="description" class="mt-1 max-w-sm text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
    <div v-if="$slots.action" class="mt-4">
      <slot name="action" />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
});
</script>
