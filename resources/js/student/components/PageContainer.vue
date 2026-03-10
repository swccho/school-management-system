<template>
  <div>
    <h2 v-if="title" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ title }}</h2>
    <p v-if="description" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ description }}</p>
    <div class="mt-4">
      <slot />
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
});
</script>
