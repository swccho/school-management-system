<template>
  <div class="animate-pulse space-y-4" :class="classNames">
    <template v-if="variant === 'cards'">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div v-for="i in 4" :key="i" class="h-24 rounded-xl bg-zinc-200 dark:bg-zinc-700" />
      </div>
      <div class="h-48 rounded-xl bg-zinc-200 dark:bg-zinc-700" />
    </template>
    <template v-else-if="variant === 'table'">
      <div class="h-10 w-full rounded-t-lg bg-zinc-200 dark:bg-zinc-700" />
      <div v-for="i in rows" :key="i" class="flex gap-4 border-b border-zinc-100 py-3 dark:border-zinc-800">
        <div class="h-4 flex-1 rounded bg-zinc-200 dark:bg-zinc-700" />
        <div class="h-4 w-24 rounded bg-zinc-200 dark:bg-zinc-700" />
        <div class="h-4 w-20 rounded bg-zinc-200 dark:bg-zinc-700" />
      </div>
    </template>
    <template v-else>
      <div class="h-6 w-3/4 rounded bg-zinc-200 dark:bg-zinc-700" />
      <div class="h-4 w-full rounded bg-zinc-200 dark:bg-zinc-700" />
      <div class="h-4 w-5/6 rounded bg-zinc-200 dark:bg-zinc-700" />
      <div class="h-4 w-4/6 rounded bg-zinc-200 dark:bg-zinc-700" />
    </template>
  </div>
</template>

<script setup>
defineProps({
  variant: { type: String, default: 'default' },
  rows: { type: Number, default: 5 },
  classNames: { type: String, default: '' },
});
</script>
