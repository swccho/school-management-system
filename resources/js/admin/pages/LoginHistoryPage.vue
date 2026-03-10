<template>
  <PageContainer
    title="Login History"
    description="View login and logout activity."
  >
    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="User name, email, IP…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[120px]">
        <label for="filter-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="filter-status"
          v-model="filters.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="success">Success</option>
          <option value="failed">Failed</option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-date-from"
          v-model="filters.date_from"
          label="Date from"
          placeholder="From"
          clearable
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-date-to"
          v-model="filters.date_to"
          label="Date to"
          placeholder="To"
          clearable
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
        Loading login history…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="histories.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No login history found.
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">User</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Login at</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Logout at</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">IP</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">User agent</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="h in histories"
              :key="h.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.user_name ?? '—' }}{{ h.user_email ? ` (${h.user_email})` : '' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.login_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.logout_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="h.status === 'success'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'"
                >
                  {{ h.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ h.ip_address ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400 max-w-xs truncate" :title="h.user_agent">{{ h.user_agent ?? '—' }}</td>
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
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { getLoginHistory } from '../services/systemMonitoringService.js';

function initialFilters() {
  return { search: '', status: '', date_from: '', date_to: '' };
}

const loading = ref(true);
const error = ref(null);
const histories = ref([]);
const filters = ref(initialFilters());

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.status) params.status = f.status;
  if (f.date_from) params.date_from = f.date_from;
  if (f.date_to) params.date_to = f.date_to;
  return params;
}

function applyFilters() {
  fetchHistory();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchHistory();
}

async function fetchHistory() {
  loading.value = true;
  error.value = null;
  try {
    histories.value = await getLoginHistory(buildParams());
  } catch {
    error.value = 'Failed to load login history.';
    histories.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  fetchHistory();
});
</script>
