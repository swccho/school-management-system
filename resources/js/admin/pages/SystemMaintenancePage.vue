<template>
  <PageContainer
    title="Maintenance Mode"
    description="Enable or disable maintenance mode. When enabled, access to the system may be restricted for users."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Current status</h3>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
          Maintenance mode is
          <span
            class="font-medium"
            :class="maintenanceMode ? 'text-amber-600 dark:text-amber-400' : 'text-emerald-600 dark:text-emerald-400'"
          >
            {{ maintenanceMode ? 'enabled' : 'disabled' }}
          </span>
        </p>
        <p class="mt-2 text-sm text-zinc-500 dark:text-zinc-500">
          Enabling maintenance mode may restrict access for other users. Use it when performing updates or critical changes.
        </p>
        <div class="mt-4">
          <button
            type="button"
            :class="maintenanceMode
              ? 'rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700 dark:border-emerald-500 dark:bg-emerald-500 dark:hover:bg-emerald-600'
              : 'rounded-lg border border-amber-600 bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700 dark:border-amber-500 dark:bg-amber-500 dark:hover:bg-amber-600'"
            :disabled="toggling"
            @click="maintenanceMode ? confirmDisable() : confirmEnable()"
          >
            {{ toggling ? 'Please wait…' : (maintenanceMode ? 'Disable maintenance mode' : 'Enable maintenance mode') }}
          </button>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getMaintenanceStatus, enableMaintenance, disableMaintenance } from '../services/maintenanceService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const toggling = ref(false);
const maintenanceMode = ref(false);

async function fetchStatus() {
  loading.value = true;
  try {
    const data = await getMaintenanceStatus();
    maintenanceMode.value = data.maintenance_mode ?? false;
  } catch {
    maintenanceMode.value = false;
  } finally {
    loading.value = false;
  }
}

async function confirmEnable() {
  const confirmed = await openConfirmation({
    title: 'Enable maintenance mode',
    message: 'Enabling maintenance mode may restrict access for other users. Are you sure?',
    confirmLabel: 'Enable',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  toggling.value = true;
  try {
    await enableMaintenance();
    maintenanceMode.value = true;
    closeConfirmation();
    toast.success('Maintenance mode enabled successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to enable maintenance mode.');
  } finally {
    setConfirmationLoading(false);
    toggling.value = false;
  }
}

async function confirmDisable() {
  const confirmed = await openConfirmation({
    title: 'Disable maintenance mode',
    message: 'Disable maintenance mode to restore normal access?',
    confirmLabel: 'Disable',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  toggling.value = true;
  try {
    await disableMaintenance();
    maintenanceMode.value = false;
    closeConfirmation();
    toast.success('Maintenance mode disabled successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to disable maintenance mode.');
  } finally {
    setConfirmationLoading(false);
    toggling.value = false;
  }
}

onMounted(() => {
  fetchStatus();
});
</script>
