<template>
  <Teleport to="body">
    <Transition name="modal">
      <div
        v-if="open"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="titleId"
        aria-describedby="messageId"
        @keydown.escape="handleCancel"
      >
        <div
          class="fixed inset-0 bg-zinc-900/60 dark:bg-zinc-950/70"
          aria-hidden="true"
          @click="handleCancel"
        />
        <div
          class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white shadow-xl dark:border-zinc-700 dark:bg-zinc-900"
          role="document"
          @click.stop
        >
          <div class="p-6">
            <h2
              :id="titleId"
              class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
            >
              {{ title }}
            </h2>
            <p
              :id="messageId"
              class="mt-2 text-sm text-zinc-600 dark:text-zinc-400"
            >
              {{ message }}
            </p>
          </div>
          <div class="flex justify-end gap-2 border-t border-zinc-200 px-6 py-4 dark:border-zinc-700">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              :disabled="loading"
              @click="handleCancel"
            >
              {{ cancelLabel }}
            </button>
            <button
              type="button"
              class="rounded-lg px-4 py-2 text-sm font-medium text-white transition-colors disabled:opacity-50"
              :class="confirmButtonClass"
              :disabled="loading"
              @click="handleConfirm"
            >
              <span v-if="loading">Please wait…</span>
              <span v-else>{{ confirmLabel }}</span>
            </button>
          </div>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  open: { type: Boolean, default: false },
  title: { type: String, default: 'Confirm' },
  message: { type: String, default: '' },
  confirmLabel: { type: String, default: 'Confirm' },
  cancelLabel: { type: String, default: 'Cancel' },
  variant: { type: String, default: 'normal' }, // 'normal' | 'destructive'
  loading: { type: Boolean, default: false },
});

const emit = defineEmits(['confirm', 'cancel', 'update:open']);

const titleId = 'confirmation-modal-title';
const messageId = 'confirmation-modal-message';

const confirmButtonClass = computed(() => {
  if (props.variant === 'destructive') {
    return 'border border-red-600 bg-red-600 hover:bg-red-700 dark:border-red-500 dark:bg-red-600 dark:hover:bg-red-700';
  }
  return 'border border-zinc-900 bg-zinc-900 hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200';
});

function handleConfirm() {
  if (props.loading) return;
  emit('confirm');
}

function handleCancel() {
  if (props.loading) return;
  emit('cancel');
  emit('update:open', false);
}
</script>

<style scoped>
.modal-enter-active,
.modal-leave-active {
  transition: opacity 0.15s ease;
}
.modal-enter-from,
.modal-leave-to {
  opacity: 0;
}
.modal-enter-active .relative,
.modal-leave-active .relative {
  transition: transform 0.15s ease;
}
.modal-enter-from .relative,
.modal-leave-to .relative {
  transform: scale(0.98);
}
</style>
