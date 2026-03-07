import { reactive } from 'vue';

const state = reactive({
  open: false,
  title: 'Confirm',
  message: '',
  confirmLabel: 'Confirm',
  cancelLabel: 'Cancel',
  variant: 'normal',
  loading: false,
});

let resolvePromise = null;

/**
 * Open the shared confirmation modal. Returns a Promise that resolves to true if user
 * confirmed, false if cancelled or closed.
 * @param {Object} options
 * @param {string} options.title
 * @param {string} options.message
 * @param {string} [options.confirmLabel='Confirm']
 * @param {string} [options.cancelLabel='Cancel']
 * @param {string} [options.variant='normal'] - 'normal' | 'destructive'
 * @returns {Promise<boolean>}
 */
export function openConfirmation(options = {}) {
  state.title = options.title ?? 'Confirm';
  state.message = options.message ?? '';
  state.confirmLabel = options.confirmLabel ?? 'Confirm';
  state.cancelLabel = options.cancelLabel ?? 'Cancel';
  state.variant = options.variant ?? 'normal';
  state.loading = false;
  state.open = true;

  return new Promise((resolve) => {
    resolvePromise = resolve;
  });
}

/**
 * Resolve the promise and optionally close. Call with true when user confirmed
 * (caller may do async work first, then call closeConfirmation()).
 * Call with false when user cancelled (closes immediately).
 */
export function resolveConfirmation(value) {
  if (resolvePromise) {
    resolvePromise(value);
    resolvePromise = null;
  }
  if (value === false) {
    state.open = false;
  }
  state.loading = false;
}

export function closeConfirmation() {
  state.open = false;
  state.loading = false;
}

export function setConfirmationLoading(loading) {
  state.loading = !!loading;
}

/**
 * Composable for confirmation modal. In the root (e.g. App.vue) use getConfirmationState()
 * to bind the modal. In children use openConfirmation() to show the modal.
 */
export function useConfirmation() {
  return {
    confirmationState: state,
    openConfirmation,
    resolveConfirmation,
    closeConfirmation,
    setConfirmationLoading,
  };
}
