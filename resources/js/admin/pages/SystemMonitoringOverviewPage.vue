<template>
  <PageContainer
    title="System Monitoring"
    description="Overview of login activity and active sessions."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Recent logins (7 days)</p>
          <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ overview.recent_logins_count ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Active sessions</p>
          <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ overview.active_sessions_count ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Failed logins (7 days)</p>
          <p class="mt-2 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ overview.failed_logins_count ?? 0 }}</p>
        </div>
      </div>

      <div class="flex flex-wrap gap-4">
        <router-link
          :to="{ name: 'login-history' }"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        >
          View login history
        </router-link>
        <router-link
          :to="{ name: 'active-sessions' }"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
          View active sessions
        </router-link>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getMonitoringOverview } from '../services/systemMonitoringService.js';

const loading = ref(true);
const overview = ref({});

async function fetchOverview() {
  loading.value = true;
  try {
    overview.value = await getMonitoringOverview();
  } catch {
    overview.value = {};
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchOverview();
});
</script>
