<template>
  <Teleport to="body">
    <div
      class="pointer-events-none fixed bottom-0 right-0 z-50 flex w-full flex-col items-end gap-2 p-4 sm:max-w-sm"
      aria-live="polite"
      role="region"
      aria-label="Notifications"
    >
      <TransitionGroup name="toast">
        <div
          v-for="t in toasts"
          :key="t.id"
          class="pointer-events-auto flex w-full items-start gap-3 rounded-lg border px-4 py-3 shadow-lg transition-shadow"
          :class="toastClass(t.type)"
          role="alert"
        >
          <span v-if="icon(t.type)" class="shrink-0 text-lg" aria-hidden="true">{{ icon(t.type) }}</span>
          <p class="min-w-0 flex-1 text-sm font-medium">{{ t.message }}</p>
          <button
            type="button"
            class="shrink-0 rounded p-1 opacity-70 hover:opacity-100 focus:outline-none focus:ring-2 focus:ring-inset"
            :class="iconButtonClass(t.type)"
            aria-label="Dismiss"
            @click="dismiss(t.id)"
          >
            <span aria-hidden="true">×</span>
          </button>
        </div>
      </TransitionGroup>
    </div>
  </Teleport>
</template>

<script setup>
import { toasts, useToast } from '../../composables/useToast.js';

const { dismiss } = useToast();

function toastClass(type) {
  const base = 'border bg-white dark:bg-zinc-900';
  switch (type) {
    case 'success':
      return `${base} border-emerald-200 text-emerald-800 dark:border-emerald-800 dark:text-emerald-200 dark:bg-emerald-900/20`;
    case 'error':
      return `${base} border-red-200 text-red-800 dark:border-red-800 dark:text-red-200 dark:bg-red-900/20`;
    case 'warning':
      return `${base} border-amber-200 text-amber-800 dark:border-amber-800 dark:text-amber-200 dark:bg-amber-900/20`;
    case 'info':
    default:
      return `${base} border-zinc-200 text-zinc-800 dark:border-zinc-700 dark:text-zinc-200`;
  }
}

function icon(type) {
  switch (type) {
    case 'success':
      return '✓';
    case 'error':
      return '✕';
    case 'warning':
      return '!';
    case 'info':
    default:
      return 'i';
  }
}

function iconButtonClass(type) {
  switch (type) {
    case 'success':
      return 'text-emerald-600 focus:ring-emerald-500 dark:text-emerald-400';
    case 'error':
      return 'text-red-600 focus:ring-red-500 dark:text-red-400';
    case 'warning':
      return 'text-amber-600 focus:ring-amber-500 dark:text-amber-400';
    case 'info':
    default:
      return 'text-zinc-600 focus:ring-zinc-500 dark:text-zinc-400';
  }
}
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active {
  transition: all 0.2s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(1rem);
}
.toast-move {
  transition: transform 0.2s ease;
}
</style>
