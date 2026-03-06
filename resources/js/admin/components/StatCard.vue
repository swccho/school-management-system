<template>
  <div
    class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900"
  >
    <div class="flex items-start justify-between gap-3">
      <div class="min-w-0 flex-1">
        <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
          {{ title }}
        </p>
        <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
          {{ value }}
        </p>
        <p v-if="supportingText" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
          {{ supportingText }}
        </p>
      </div>
      <div
        v-if="$slots.icon"
        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400"
      >
        <slot name="icon" />
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  title: { type: String, required: true },
  value: { type: [String, Number], required: true },
  supportingText: { type: String, default: '' },
});
</script>
