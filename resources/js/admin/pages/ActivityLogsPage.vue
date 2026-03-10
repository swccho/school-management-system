<template>
  <PageContainer
    title="System Logs"
    description="View system activity and audit trail. Logs are read-only."
  >
    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Description, module, user name…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[160px]">
        <label for="filter-user" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">User</label>
        <select
          id="filter-user"
          v-model="filters.user_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All users</option>
          <option v-for="u in filterOptions.users" :key="u.id" :value="u.id">
            {{ u.name }} ({{ u.email }})
          </option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <label for="filter-module" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Module</label>
        <select
          id="filter-module"
          v-model="filters.module"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All modules</option>
          <option v-for="m in filterOptions.modules" :key="m" :value="m">{{ m }}</option>
        </select>
      </div>
      <div class="min-w-[120px]">
        <label for="filter-action" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Action</label>
        <select
          id="filter-action"
          v-model="filters.action"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All actions</option>
          <option v-for="a in filterOptions.actions" :key="a" :value="a">{{ a }}</option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-created-from"
          v-model="filters.created_from"
          label="From date"
          placeholder="From"
          clearable
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-created-to"
          v-model="filters.created_to"
          label="To date"
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
          Apply
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
        Loading activity logs…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="logs.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No activity logs found.
      </div>
      <div v-else>
        <div class="sidenav-scroll overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Date</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">User</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Action</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Module</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">IP</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="log in logs"
                :key="log.id"
                class="cursor-pointer border-b border-zinc-100 transition-colors hover:bg-zinc-50 dark:border-zinc-800/50 dark:hover:bg-zinc-800/50 last:border-0"
                @click="openDrawer(log.id)"
              >
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ log.created_at_formatted ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ log.user_name ?? 'System' }}{{ log.user_email ? ` (${log.user_email})` : '' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                    :class="actionBadgeClass(log.action)"
                  >
                    {{ log.action }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ log.module }}</td>
                <td class="max-w-xs truncate px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400" :title="log.description">{{ log.description ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-500">{{ log.ip_address ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
        <div
          v-if="meta.last_page > 1"
          class="flex flex-wrap items-center justify-between gap-2 border-t border-zinc-200 px-4 py-3 dark:border-zinc-800"
        >
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            Showing {{ meta.from ?? 0 }}–{{ meta.to ?? 0 }} of {{ meta.total }}
          </p>
          <div class="flex items-center gap-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              :disabled="meta.current_page <= 1"
              @click="goToPage(meta.current_page - 1)"
            >
              Previous
            </button>
            <span class="text-sm text-zinc-600 dark:text-zinc-400">
                Page {{ meta.current_page }} of {{ meta.last_page }}
              </span>
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              :disabled="meta.current_page >= meta.last_page"
              @click="goToPage(meta.current_page + 1)"
            >
              Next
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Log details drawer -->
    <Teleport to="body">
      <div
        v-if="drawerOpen"
        class="fixed inset-0 z-50 flex"
        role="dialog"
        aria-modal="true"
        aria-labelledby="log-drawer-title"
      >
        <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" @click="closeDrawer" />
        <div
          class="relative ml-auto flex h-full w-full max-w-lg flex-col border-l border-zinc-200 bg-white shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        >
          <div class="flex shrink-0 items-center justify-between border-b border-zinc-200 px-4 py-3 dark:border-zinc-800">
            <h2 id="log-drawer-title" class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
              Log details
            </h2>
            <button
              type="button"
              class="rounded-lg p-2 text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-zinc-800 dark:hover:text-zinc-300"
              aria-label="Close"
              @click="closeDrawer"
            >
              <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </div>
          <div class="sidenav-scroll flex-1 overflow-y-auto p-4">
            <div v-if="drawerLoading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
              Loading…
            </div>
            <div v-else-if="drawerError" class="py-4 text-center text-sm text-red-600 dark:text-red-400">
              {{ drawerError }}
            </div>
            <div v-else-if="selectedLog" class="space-y-4">
              <dl class="space-y-3 text-sm">
                <div>
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Date / Time</dt>
                  <dd class="mt-0.5 text-zinc-900 dark:text-zinc-100">{{ selectedLog.created_at_formatted ?? '—' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">User</dt>
                  <dd class="mt-0.5 text-zinc-900 dark:text-zinc-100">{{ selectedLog.user_name ?? '—' }}{{ selectedLog.user_email ? ` (${selectedLog.user_email})` : '' }}</dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Action</dt>
                  <dd class="mt-0.5">
                    <span
                      class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                      :class="actionBadgeClass(selectedLog.action)"
                    >
                      {{ selectedLog.action }}
                    </span>
                  </dd>
                </div>
                <div>
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Module</dt>
                  <dd class="mt-0.5 text-zinc-900 dark:text-zinc-100">{{ selectedLog.module }}</dd>
                </div>
                <div v-if="selectedLog.record_type || selectedLog.record_id">
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Record</dt>
                  <dd class="mt-0.5 font-mono text-zinc-700 dark:text-zinc-300">
                    {{ selectedLog.record_type ?? '—' }} #{{ selectedLog.record_id ?? '—' }}
                  </dd>
                </div>
                <div v-if="selectedLog.description">
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
                  <dd class="mt-0.5 text-zinc-900 dark:text-zinc-100">{{ selectedLog.description }}</dd>
                </div>
                <div v-if="selectedLog.ip_address">
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">IP address</dt>
                  <dd class="mt-0.5 font-mono text-zinc-700 dark:text-zinc-300">{{ selectedLog.ip_address }}</dd>
                </div>
                <div v-if="selectedLog.user_agent">
                  <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">User agent</dt>
                  <dd class="mt-0.5 break-all font-mono text-xs text-zinc-700 dark:text-zinc-300">{{ selectedLog.user_agent }}</dd>
                </div>
              </dl>
              <div v-if="selectedLog.metadata && Object.keys(selectedLog.metadata).length" class="space-y-2">
                <h3 class="text-xs font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Metadata</h3>
                <div v-if="selectedLog.metadata.old || selectedLog.metadata.new" class="space-y-3">
                  <div v-if="selectedLog.metadata.old && Object.keys(selectedLog.metadata.old).length">
                    <p class="mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">Previous values</p>
                    <pre class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">{{ JSON.stringify(selectedLog.metadata.old, null, 2) }}</pre>
                  </div>
                  <div v-if="selectedLog.metadata.new && Object.keys(selectedLog.metadata.new).length">
                    <p class="mb-1 text-xs font-medium text-zinc-600 dark:text-zinc-400">New values</p>
                    <pre class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 font-mono text-xs text-zinc-800 dark:border-zinc-700 dark:bg-zinc-800/50 dark:text-zinc-200">{{ JSON.stringify(selectedLog.metadata.new, null, 2) }}</pre>
                  </div>
                </div>
                <div v-else class="rounded-lg border border-zinc-200 bg-zinc-50 p-3 dark:border-zinc-700 dark:bg-zinc-800/50">
                  <pre class="whitespace-pre-wrap break-words font-mono text-xs text-zinc-800 dark:text-zinc-200">{{ JSON.stringify(selectedLog.metadata, null, 2) }}</pre>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </Teleport>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { getActivityLogs, getActivityLogFilterOptions, getActivityLog } from '../services/activityLogService.js';

function initialFilters() {
  return {
    search: '',
    user_id: '',
    module: '',
    action: '',
    created_from: '',
    created_to: '',
  };
}

const loading = ref(true);
const error = ref(null);
const logs = ref([]);
const meta = ref({
  current_page: 1,
  last_page: 1,
  per_page: 25,
  total: 0,
  from: null,
  to: null,
});
const filters = ref(initialFilters());
const filterOptions = ref({ users: [], modules: [], actions: [] });
const drawerOpen = ref(false);
const drawerLoading = ref(false);
const drawerError = ref(null);
const selectedLog = ref(null);
const selectedLogId = ref(null);

function actionBadgeClass(action) {
  const a = (action || '').toLowerCase();
  if (a === 'create') return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
  if (a === 'update' || a === 'assign') return 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300';
  if (a === 'delete') return 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300';
  if (a === 'login') return 'bg-violet-100 text-violet-800 dark:bg-violet-900/40 dark:text-violet-300';
  if (a === 'logout') return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
  if (a === 'publish' || a === 'activate') return 'bg-teal-100 text-teal-800 dark:bg-teal-900/40 dark:text-teal-300';
  if (a === 'deactivate' || a === 'reset-password') return 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300';
  return 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300';
}

function buildParams(page = 1) {
  const f = filters.value;
  const params = { page, per_page: 25 };
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.user_id) params.user_id = f.user_id;
  if (f.module?.trim()) params.module = f.module.trim();
  if (f.action?.trim()) params.action = f.action.trim();
  if (f.created_from) params.created_from = f.created_from;
  if (f.created_to) params.created_to = f.created_to;
  return params;
}

function applyFilters() {
  fetchLogs(1);
}

function resetFilters() {
  filters.value = initialFilters();
  fetchLogs(1);
}

function goToPage(page) {
  fetchLogs(page);
}

async function fetchFilterOptions() {
  try {
    filterOptions.value = await getActivityLogFilterOptions();
  } catch {
    filterOptions.value = { users: [], modules: [], actions: [] };
  }
}

async function fetchLogs(page = 1) {
  loading.value = true;
  error.value = null;
  try {
    const res = await getActivityLogs(buildParams(page));
    logs.value = res.data ?? res;
    meta.value = res.meta ?? {
      current_page: 1,
      last_page: 1,
      per_page: 25,
      total: logs.value.length,
      from: 1,
      to: logs.value.length,
    };
  } catch {
    error.value = 'Failed to load activity logs.';
    logs.value = [];
  } finally {
    loading.value = false;
  }
}

async function openDrawer(id) {
  selectedLogId.value = id;
  drawerOpen.value = true;
  selectedLog.value = null;
  drawerError.value = null;
  drawerLoading.value = true;
  try {
    selectedLog.value = await getActivityLog(id);
  } catch {
    drawerError.value = 'Failed to load log details.';
  } finally {
    drawerLoading.value = false;
  }
}

function closeDrawer() {
  drawerOpen.value = false;
  selectedLogId.value = null;
  selectedLog.value = null;
}

onMounted(() => {
  fetchFilterOptions();
  fetchLogs();
});
</script>
