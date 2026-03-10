<template>
  <PageContainer
    title="Academic Sessions"
    description="Manage academic years and sessions. Set one session as current for the school."
  >
    <template #actions>
      <button
        type="button"
        class="inline-flex items-center gap-2 rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        <Plus class="h-4 w-4" />
        Add Session
      </button>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search sessions…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[220px]">
        <DateRangePicker
          id="filter-date-range"
          v-model="filters.date_range"
          label="Date range"
          placeholder="From start date to end date…"
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
        Loading sessions…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="sessions.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No sessions match your filters.' : 'No sessions yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Start Date</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">End Date</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Current</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="session in sessions"
              :key="session.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ session.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ session.code ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ session.start_date_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ session.end_date_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  v-if="session.is_current"
                  class="inline-flex rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-300"
                >
                  Current
                </span>
                <span v-else class="text-sm text-zinc-400 dark:text-zinc-500">—</span>
              </td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="session.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ session.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(session)"
                  >
                    Edit
                  </button>
                  <template v-if="!session.is_current">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="setCurrent(session)"
                    >
                      Set as current
                    </button>
                  </template>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <AcademicSessionForm
      v-model="modalOpen"
      :session="editingSession"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Plus } from 'lucide-vue-next';
import PageContainer from '../components/PageContainer.vue';
import AcademicSessionForm from '../components/AcademicSessionForm.vue';
import DateRangePicker from '../../shared/components/form/DateRangePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getSessions,
  setCurrentSession,
} from '../services/academicSessionService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const initialFilters = () => ({
  search: '',
  date_range: { date_from: '', date_to: '' },
});

const loading = ref(true);
const error = ref(null);
const sessions = ref([]);
const modalOpen = ref(false);
const editingSession = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  const range = f.date_range;
  return !!(f.search?.trim() || (range?.date_from && range?.date_to));
});

function openCreateModal() {
  editingSession.value = null;
  modalOpen.value = true;
}

function openEditModal(session) {
  editingSession.value = session;
  modalOpen.value = true;
}

function onSaved() {
  fetchSessions();
  toast.success(editingSession.value ? 'Academic session updated successfully.' : 'Academic session created successfully.');
}

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  const range = f.date_range;
  if (range?.date_from && range?.date_to) {
    params.date_from = range.date_from;
    params.date_to = range.date_to;
  }
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
    const params = buildParams();
    sessions.value = await getSessions(params);
  } catch {
    error.value = 'Failed to load sessions.';
    sessions.value = [];
  } finally {
    loading.value = false;
  }
}

async function setCurrent(session) {
  const confirmed = await openConfirmation({
    title: 'Set current session',
    message: 'Set this session as current? The current session badge will move to this one.',
    confirmLabel: 'Set Current',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await setCurrentSession(session.id);
    await fetchSessions();
    closeConfirmation();
    toast.success('Session set as current successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to set current session.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(fetchSessions);
</script>
