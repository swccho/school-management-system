<template>
  <PageContainer
    title="Active Sessions"
    description="Users currently logged in (sessions active in the last 30 minutes)."
  >
    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="User name, email…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="applyFilters"
        >
          Apply Filters
        </button>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading active sessions…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="sessions.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No active sessions.
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">User</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Last activity</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">IP</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Device / Browser</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="s in sessions"
              :key="s.session_id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.user_name ?? '—' }}{{ s.user_email ? ` (${s.user_email})` : '' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.last_activity_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.ip_address ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 max-w-xs truncate" :title="s.user_agent">{{ s.user_agent ?? '—' }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getActiveSessions } from '../services/systemMonitoringService.js';

function initialFilters() {
  return { search: '' };
}

const loading = ref(true);
const error = ref(null);
const sessions = ref([]);
const filters = ref(initialFilters());

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  return params;
}

function applyFilters() {
  fetchSessions();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchSessions();
}

async function fetchSessions() {
  loading.value = true;
  error.value = null;
  try {
    sessions.value = await getActiveSessions(buildParams());
  } catch {
    error.value = 'Failed to load active sessions.';
    sessions.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchSessions();
});
</script>
