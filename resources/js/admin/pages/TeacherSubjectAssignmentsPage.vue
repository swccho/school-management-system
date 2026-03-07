<template>
  <PageContainer
    title="Teacher Subject Assignments"
    description="Assign teachers to teach subjects in classes and sections for an academic session."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreate"
      >
        Assign Teacher
      </button>
    </template>

    <div class="space-y-4">
      <div class="flex flex-wrap items-end gap-3">
        <div>
          <label for="filter-session" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</label>
          <select
            id="filter-session"
            v-model="filterSessionId"
            class="mt-1 block rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            @change="fetch"
          >
            <option value="">All</option>
            <option
              v-for="s in sessions"
              :key="s.id"
              :value="s.id"
            >
              {{ s.name }}
            </option>
          </select>
        </div>
        <div>
          <label for="filter-class" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</label>
          <select
            id="filter-class"
            v-model="filterClassId"
            class="mt-1 block rounded-lg border border-zinc-300 bg-white px-3 py-1.5 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            @change="fetch"
          >
            <option value="">All</option>
            <option
              v-for="c in classes"
              :key="c.id"
              :value="c.id"
            >
              {{ c.name }}
            </option>
          </select>
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

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading assignments…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="assignments.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No assignments yet. Click “Assign Teacher” to add one.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[700px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Teacher</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="a in assignments"
                :key="a.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ a.teacher_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.academic_session_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.class_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.section_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.subject_name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="a.status === 'active'
                      ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                      : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                  >
                    {{ a.status }}
                  </span>
                </td>
                <td class="px-4 py-3">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEdit(a)"
                  >
                    Edit
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>

    <TeacherSubjectAssignmentForm
      v-model="modalOpen"
      :assignment="editing"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import TeacherSubjectAssignmentForm from '../components/TeacherSubjectAssignmentForm.vue';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getAssignments,
  getAcademicSessions,
  getClasses,
} from '../services/teacherSubjectAssignmentService.js';

const toast = useToast();

const loading = ref(true);
const error = ref(null);
const assignments = ref([]);
const sessions = ref([]);
const classes = ref([]);
const modalOpen = ref(false);
const editing = ref(null);
const filterSessionId = ref('');
const filterClassId = ref('');
const filterStatus = ref('');

async function loadFilters() {
  try {
    [sessions.value, classes.value] = await Promise.all([
      getAcademicSessions(),
      getClasses(),
    ]);
  } catch {
    sessions.value = [];
    classes.value = [];
  }
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filterSessionId.value) params.academic_session_id = filterSessionId.value;
    if (filterClassId.value) params.class_id = filterClassId.value;
    if (filterStatus.value) params.status = filterStatus.value;
    assignments.value = await getAssignments(params);
  } catch {
    error.value = 'Failed to load assignments.';
    assignments.value = [];
  } finally {
    loading.value = false;
  }
}

function openCreate() {
  editing.value = null;
  modalOpen.value = true;
}

function openEdit(a) {
  editing.value = a;
  modalOpen.value = true;
}

function onSaved() {
  fetch();
  toast.success(editing.value ? 'Assignment updated successfully.' : 'Assignment saved successfully.');
}

onMounted(async () => {
  await loadFilters();
  await fetch();
});
</script>
