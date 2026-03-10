<template>
  <PageContainer title="Exams & Marks" description="View exams and enter marks for your assigned subjects.">
    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[160px]">
        <label for="filter-session" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</label>
        <select
          id="filter-session"
          v-model="filters.academic_session_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchExams"
        >
          <option value="">All</option>
          <option v-for="s in filterOptions.academic_sessions" :key="s.id" :value="s.id">
            {{ s.name }}{{ s.is_current ? ' (current)' : '' }}
          </option>
        </select>
      </div>
      <div class="min-w-[120px]">
        <label for="filter-class" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</label>
        <select
          id="filter-class"
          v-model="filters.class_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchExams"
        >
          <option value="">All</option>
          <option v-for="c in filterOptions.classes" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="min-w-[120px]">
        <label for="filter-section" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</label>
        <select
          id="filter-section"
          v-model="filters.section_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchExams"
        >
          <option value="">All</option>
          <option v-for="s in filterOptions.sections" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <label for="filter-subject" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</label>
        <select
          id="filter-subject"
          v-model="filters.subject_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchExams"
        >
          <option value="">All</option>
          <option v-for="s in filterOptions.subjects" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <label for="filter-exam-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Exam type</label>
        <select
          id="filter-exam-type"
          v-model="filters.exam_type_id"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchExams"
        >
          <option value="">All</option>
          <option v-for="t in filterOptions.exam_types" :key="t.id" :value="t.id">{{ t.name }}</option>
        </select>
      </div>
    </div>

    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
    <div v-else-if="exams.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No exams match your filters for your assigned subjects.
    </div>
    <div v-else class="space-y-3">
      <div
        v-for="exam in exams"
        :key="exam.id"
        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div>
          <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ exam.name }}</p>
          <p class="mt-1 flex flex-wrap items-center gap-2 text-sm text-zinc-500 dark:text-zinc-400">
            <span>{{ exam.exam_type }} · {{ exam.start_date }} – {{ exam.end_date }} · {{ exam.subjects_count }} subject(s)</span>
            <span
              v-if="exam.marks_entry_open"
              class="rounded bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
            >
              Entry open
            </span>
            <span
              v-else
              class="rounded bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300"
            >
              Entry closed
            </span>
            <span
              class="rounded px-2 py-0.5 text-xs font-medium"
              :class="exam.status === 'active' ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
            >
              {{ exam.status }}
            </span>
          </p>
        </div>
        <div class="flex gap-2">
          <router-link
            :to="`/teacher/exams/${exam.id}/marks`"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          >
            Enter marks
          </router-link>
          <router-link
            :to="`/teacher/exams/${exam.id}/summary`"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          >
            Performance summary
          </router-link>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, reactive } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getExams, getExamFilterOptions } from '../services/examService.js';

const loading = ref(true);
const exams = ref([]);
const filterOptions = ref({
  academic_sessions: [],
  classes: [],
  sections: [],
  subjects: [],
  exam_types: [],
});
const filters = reactive({
  academic_session_id: '',
  class_id: '',
  section_id: '',
  subject_id: '',
  exam_type_id: '',
});

function buildParams() {
  const p = {};
  if (filters.academic_session_id) p.academic_session_id = filters.academic_session_id;
  if (filters.class_id) p.class_id = filters.class_id;
  if (filters.section_id) p.section_id = filters.section_id;
  if (filters.subject_id) p.subject_id = filters.subject_id;
  if (filters.exam_type_id) p.exam_type_id = filters.exam_type_id;
  return p;
}

async function fetchExams() {
  loading.value = true;
  try {
    exams.value = await getExams(buildParams());
  } catch {
    exams.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    const options = await getExamFilterOptions();
    filterOptions.value = options ?? filterOptions.value;
  } catch {
    // keep default empty options
  }
  await fetchExams();
});
</script>
