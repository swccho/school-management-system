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

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
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
          id="filter-class"
          v-model="filters.class_id"
          label="Class"
          :options="classes"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search classes…"
          clearable
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-teacher"
          v-model="filters.teacher_id"
          label="Teacher"
          :options="teachers"
          label-key="full_name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search teachers…"
          clearable
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-section"
          v-model="filters.section_id"
          label="Section"
          :options="sections"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search sections…"
          clearable
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-subject"
          v-model="filters.subject_id"
          label="Subject"
          :options="subjects"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search subjects…"
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
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
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
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading assignments…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="assignments.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          {{ hasActiveFilters ? 'No assignments match your filters.' : 'No assignments yet. Click "Assign Teacher" to add one.' }}
        </div>
        <div v-else class="sidenav-scroll overflow-x-auto">
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

    <TeacherSubjectAssignmentForm
      v-model="modalOpen"
      :assignment="editing"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import TeacherSubjectAssignmentForm from '../components/TeacherSubjectAssignmentForm.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getAssignments,
  getAcademicSessions,
  getClasses,
  getSections,
  getTeachers,
  getSubjects,
} from '../services/teacherSubjectAssignmentService.js';

const toast = useToast();

function initialFilters() {
  return { academic_session_id: null, class_id: null, teacher_id: null, section_id: null, subject_id: null, status: '' };
}

const loading = ref(true);
const error = ref(null);
const assignments = ref([]);
const sessions = ref([]);
const classes = ref([]);
const teachers = ref([]);
const sections = ref([]);
const subjects = ref([]);
const modalOpen = ref(false);
const editing = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(f.academic_session_id || f.class_id || f.teacher_id || f.section_id || f.subject_id || f.status);
});

watch(() => filters.value.class_id, (newVal) => {
  filters.value.section_id = null;
  if (newVal) {
    getSections(newVal).then((s) => { sections.value = s; }).catch(() => { sections.value = []; });
  } else {
    sections.value = [];
  }
});

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.academic_session_id) params.academic_session_id = f.academic_session_id;
  if (f.class_id) params.class_id = f.class_id;
  if (f.teacher_id) params.teacher_id = f.teacher_id;
  if (f.section_id) params.section_id = f.section_id;
  if (f.subject_id) params.subject_id = f.subject_id;
  if (f.status) params.status = f.status;
  return params;
}

function applyFilters() {
  fetch();
}

function resetFilters() {
  filters.value = initialFilters();
  sections.value = [];
  fetch();
}

async function loadFilters() {
  try {
    const [sess, cls, teach, subj] = await Promise.all([
      getAcademicSessions(),
      getClasses(),
      getTeachers(),
      getSubjects(),
    ]);
    sessions.value = sess;
    classes.value = cls;
    teachers.value = teach;
    subjects.value = subj;
    if (filters.value.class_id) {
      sections.value = await getSections(filters.value.class_id);
    }
  } catch {
    sessions.value = [];
    classes.value = [];
    teachers.value = [];
    subjects.value = [];
  }
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = buildParams();
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
