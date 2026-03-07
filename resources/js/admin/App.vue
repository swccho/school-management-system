<template>
  <div>
    <router-view />
    <AppToaster />
    <ConfirmationModal
      :open="confirmationState.open"
      :title="confirmationState.title"
      :message="confirmationState.message"
      :confirm-label="confirmationState.confirmLabel"
      :cancel-label="confirmationState.cancelLabel"
      :variant="confirmationState.variant"
      :loading="confirmationState.loading"
      @confirm="handleConfirm"
      @cancel="handleCancel"
    />
  </div>
</template>

<script setup>
import { useConfirmation } from '../shared/composables/useConfirmation.js';
import AppToaster from '../shared/components/feedback/AppToaster.vue';
import ConfirmationModal from '../shared/components/feedback/ConfirmationModal.vue';

const {
  confirmationState,
  resolveConfirmation,
} = useConfirmation();

function handleConfirm() {
  resolveConfirmation(true);
}

function handleCancel() {
  resolveConfirmation(false);
}
</script>
