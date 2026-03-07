import { reactive } from 'vue';

const toasts = reactive([]);
const DEFAULT_DURATION = 5000;
let nextId = 0;
let timeoutIds = new Map();

function addToast(type, message, duration = DEFAULT_DURATION) {
  const id = ++nextId;
  const entry = {
    id,
    type,
    message,
    duration,
  };
  toasts.push(entry);

  if (duration > 0) {
    const timeoutId = setTimeout(() => {
      removeToast(id);
      timeoutIds.delete(id);
    }, duration);
    timeoutIds.set(id, timeoutId);
  }

  return id;
}

function removeToast(id) {
  const index = toasts.findIndex((t) => t.id === id);
  if (index !== -1) toasts.splice(index, 1);
  const tid = timeoutIds.get(id);
  if (tid) {
    clearTimeout(tid);
    timeoutIds.delete(id);
  }
}

/**
 * Global toast API. Use in any component via useToast().
 */
export function useToast() {
  return {
    toasts,
    success(message, duration = DEFAULT_DURATION) {
      return addToast('success', message, duration);
    },
    error(message, duration = DEFAULT_DURATION) {
      return addToast('error', message, duration);
    },
    warning(message, duration = DEFAULT_DURATION) {
      return addToast('warning', message, duration);
    },
    info(message, duration = DEFAULT_DURATION) {
      return addToast('info', message, duration);
    },
    dismiss(id) {
      removeToast(id);
    },
  };
}

export { toasts };
