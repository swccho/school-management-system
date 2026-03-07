<template>
  <PageContainer
    title="Exams"
    description="Create and manage exams. Configure target classes and subject marks before marks entry."
  >
    <template #actions>
      <router-link
        to="/admin/exams/create"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Create Exam
      </router-link>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search exams…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-session"
          v-model="filters.academic_session_id"
          label="Session"
          :options="sessions"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search sessions…"
          clearable
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-type"
          v-model="filters.exam_type_id"
          label="Exam type"
          :options="examTypes"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search exam types…"
          clearable
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
          <option value="draft">Draft</option>
          <option value="active">Active</option>
          <option value="completed">Completed</option>
          <option value="archived">Archived</option>
        </select>
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
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
        <div v-else-if="listError" class="p-8 text-center text-sm text-red-600 dark:text-red-400">{{ listError }}</div>
        <div v-else-if="exams.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          {{ hasActiveFilters ? 'No exams match your filters.' : 'No exams yet. Click "Create Exam" to add one.' }}
        </div>
        <div v-else class="sidenav-scroll overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Exam type</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Start</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">End</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Result date</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="e in exams"
                :key="e.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ e.name }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ e.academic_session_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ e.exam_type_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ e.start_date_formatted ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ e.end_date_formatted ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ e.result_publish_date_formatted ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="statusClass(e.status)"
                  >
                    {{ e.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <router-link
                    :to="`/admin/exams/${e.id}`"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <span class="mx-1 text-zinc-300 dark:text-zinc-600">|</span>
                  <router-link
                    :to="`/admin/exams/${e.id}/edit`"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    Edit
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
  </PageContainer>
</template>

<script setup>
import { computed, ref, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getExams, getAcademicSessions, getExamTypes } from '../services/examService.js';

function initialFilters() {
  return { search: '', academic_session_id: null, exam_type_id: null, status: '' };
}

const loading = ref(true);
const listError = ref(null);
const exams = ref([]);
const sessions = ref([]);
const examTypes = ref([]);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(f.search?.trim() || f.academic_session_id || f.exam_type_id || f.status);
});

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.academic_session_id) params.academic_session_id = f.academic_session_id;
  if (f.exam_type_id) params.exam_type_id = f.exam_type_id;
  if (f.status) params.status = f.status;
  return params;
}

function applyFilters() {
  fetch();
}

function resetFilters() {
  filters.value = initialFilters();
  fetch();
}

function statusClass(s) {
  const map = { draft: 'bg-zinc-100 text-zinc-600', active: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', completed: 'bg-blue-100 text-blue-800', archived: 'bg-zinc-100 text-zinc-500' };
  return map[s] ?? 'bg-zinc-100 text-zinc-600';
}

async function fetch() {
  loading.value = true;
  listError.value = null;
  try {
    const params = buildParams();
    exams.value = await getExams(params);
  } catch {
    listError.value = 'Failed to load exams.';
    exams.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    const [sess, types] = await Promise.all([getAcademicSessions(), getExamTypes()]);
    sessions.value = sess;
    examTypes.value = types;
  } catch {
    sessions.value = [];
    examTypes.value = [];
  }
  await fetch();
});
</script>
