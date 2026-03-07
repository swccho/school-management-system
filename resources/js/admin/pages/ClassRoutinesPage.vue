<template>
  <PageContainer
    title="Class Routines"
    description="Create and manage class timetables by session, class and section."
  >
    <template #actions>
      <router-link
        to="/admin/routines/create"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Create Routine
      </router-link>
    </template>

    <div class="space-y-4">
      <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
          Filters
        </p>
        <div class="flex flex-wrap items-end gap-4">
          <div class="min-w-[180px]">
            <SearchableSelect
              id="filter-session"
              v-model="filterSessionId"
              label="Session"
              :options="sessions"
              label-key="name"
              value-key="id"
              placeholder="All"
              search-placeholder="Search sessions…"
              clearable
              @update:model-value="fetch"
            />
          </div>
          <div class="min-w-[180px]">
            <SearchableSelect
              id="filter-class"
              v-model="filterClassId"
              label="Class"
              :options="classes"
              label-key="name"
              value-key="id"
              placeholder="All"
              search-placeholder="Search classes…"
              clearable
              @update:model-value="fetch"
            />
          </div>
          <div class="min-w-[180px]">
            <SearchableSelect
              id="filter-section"
              v-model="filterSectionId"
              label="Section"
              :options="sections"
              label-key="name"
              value-key="id"
              placeholder="All"
              search-placeholder="Search sections…"
              clearable
              @update:model-value="fetch"
            />
          </div>
          <div>
            <label for="filter-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="filter-status"
              v-model="filterStatus"
              class="mt-1 block rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              @change="fetch"
            >
              <option value="">All</option>
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading routines…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="routines.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No routines yet. Click “Create Routine” to add one.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Effective from</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Effective to</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="r in routines"
                :key="r.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ r.title || '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.academic_session_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.class_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.section_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.effective_from_formatted ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.effective_to_formatted ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="r.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ r.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <router-link
                    :to="`/admin/routines/${r.id}`"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <span class="mx-1 text-zinc-300 dark:text-zinc-600">|</span>
                  <router-link
                    :to="`/admin/routines/${r.id}/edit`"
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
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted, watch } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getClassRoutines, getAcademicSessions, getClasses, getSections } from '../services/classRoutineService.js';

const loading = ref(true);
const error = ref(null);
const routines = ref([]);
const sessions = ref([]);
const classes = ref([]);
const sections = ref([]);
const filterSessionId = ref('');
const filterClassId = ref('');
const filterSectionId = ref('');
const filterStatus = ref('');

async function loadFilters() {
  try {
    const [sess, cls, sec] = await Promise.all([
      getAcademicSessions(),
      getClasses(),
      getSections(),
    ]);
    sessions.value = sess;
    classes.value = cls;
    sections.value = sec;
  } catch {
    sessions.value = [];
    classes.value = [];
    sections.value = [];
  }
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filterSessionId.value) params.academic_session_id = filterSessionId.value;
    if (filterClassId.value) params.class_id = filterClassId.value;
    if (filterSectionId.value) params.section_id = filterSectionId.value;
    if (filterStatus.value) params.status = filterStatus.value;
    routines.value = await getClassRoutines(params);
  } catch {
    error.value = 'Failed to load routines.';
    routines.value = [];
  } finally {
    loading.value = false;
  }
}

watch(filterClassId, () => {
  filterSectionId.value = '';
  if (filterClassId.value) {
    getSections(filterClassId.value).then((s) => { sections.value = s; });
  } else {
    sections.value = [];
  }
});

onMounted(async () => {
  await loadFilters();
  await fetch();
});
</script>
