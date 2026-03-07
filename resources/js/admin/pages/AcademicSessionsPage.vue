<template>
  <PageContainer
    title="Academic Sessions"
    description="Manage academic years and sessions. Set one session as current for the school."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Session
      </button>
    </template>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading sessions…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="sessions.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No sessions yet. Add one to get started.
      </div>
      <div v-else class="overflow-x-auto">
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
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ formatDate(session.start_date) }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ formatDate(session.end_date) }}</td>
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
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import AcademicSessionForm from '../components/AcademicSessionForm.vue';
import {
  getSessions,
  setCurrentSession,
} from '../services/academicSessionService.js';

const loading = ref(true);
const error = ref(null);
const sessions = ref([]);
const modalOpen = ref(false);
const editingSession = ref(null);

function formatDate(value) {
  if (!value) return '—';
  try {
    const d = new Date(value + 'T00:00:00');
    return d.toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric' });
  } catch {
    return value;
  }
}

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
}

async function fetchSessions() {
  loading.value = true;
  error.value = null;
  try {
    sessions.value = await getSessions();
  } catch {
    error.value = 'Failed to load sessions.';
    sessions.value = [];
  } finally {
    loading.value = false;
  }
}

async function setCurrent(session) {
  if (!confirm('Set this session as current? The current session badge will move to this one.')) {
    return;
  }
  try {
    await setCurrentSession(session.id);
    await fetchSessions();
  } catch {
    error.value = 'Failed to set current session.';
  }
}

onMounted(fetchSessions);
</script>
