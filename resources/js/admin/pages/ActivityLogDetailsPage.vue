<template>
  <PageContainer
    title="Activity log details"
    description="View full activity log entry."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <router-link
        :to="{ name: 'activity-logs' }"
        class="inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
      >
        Back to activity logs
      </router-link>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Details</h3>
        <dl class="mt-4 space-y-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Date/Time</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ log.created_at_formatted ?? '—' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">User</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ log.user_name ?? '—' }}{{ log.user_email ? ` (${log.user_email})` : '' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Module</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ log.module }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Action</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ log.action }}</dd>
          </div>
          <div v-if="log.record_type || log.record_id != null">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Record</dt>
            <dd class="text-sm font-mono text-zinc-900 dark:text-zinc-100">{{ log.record_type ?? log.subject_type ?? '—' }} #{{ log.record_id ?? log.subject_id ?? '—' }}</dd>
          </div>
          <div v-if="log.description">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ log.description }}</dd>
          </div>
          <div v-if="log.ip_address">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">IP address</dt>
            <dd class="text-sm font-mono text-zinc-900 dark:text-zinc-100">{{ log.ip_address }}</dd>
          </div>
          <div v-if="log.user_agent">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">User agent</dt>
            <dd class="mt-0.5 break-all font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ log.user_agent }}</dd>
          </div>
          <div v-if="log.metadata && Object.keys(log.metadata).length">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Metadata</dt>
            <dd class="mt-1 space-y-2">
              <div v-if="log.metadata.old && Object.keys(log.metadata.old).length">
                <p class="mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Previous values</p>
                <pre class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">{{ JSON.stringify(log.metadata.old, null, 2) }}</pre>
              </div>
              <div v-if="log.metadata.new && Object.keys(log.metadata.new).length">
                <p class="mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">New values</p>
                <pre class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">{{ JSON.stringify(log.metadata.new, null, 2) }}</pre>
              </div>
              <pre v-if="!log.metadata.old && !log.metadata.new" class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200 whitespace-pre-wrap break-words">{{ JSON.stringify(log.metadata, null, 2) }}</pre>
            </dd>
          </div>
        </dl>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getActivityLog } from '../services/activityLogService.js';

const route = useRoute();
const log = ref(null);
const loading = ref(true);
const error = ref(null);

const logId = computed(() => route.params.id);

async function loadLog() {
  if (!logId.value) return;
  loading.value = true;
  error.value = null;
  try {
    log.value = await getActivityLog(logId.value);
  } catch {
    error.value = 'Failed to load activity log.';
    log.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadLog();
});
</script>
