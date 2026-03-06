<template>
  <div class="space-y-6">
    <header v-if="title || $slots.actions" class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
      <div>
        <h2 v-if="title" class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
          {{ title }}
        </h2>
        <p v-if="description" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
          {{ description }}
        </p>
      </div>
      <div v-if="$slots.actions" class="flex items-center gap-2">
        <slot name="actions" />
      </div>
    </header>
    <slot />
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, default: '' },
  description: { type: String, default: '' },
});
</script>
