<template>
  <PageContainer title="Reports" description="Download attendance, marks, and homework reports for your scope.">
    <div class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Attendance Summary</h3>
        <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">Export attendance records for a class and date range (CSV).</p>
        <div class="flex flex-wrap items-end gap-3">
          <div class="flex flex-col gap-1">
            <label for="att-class" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
            <select id="att-class" v-model="attendanceForm.class_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">Select</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="att-section" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</label>
            <select id="att-section" v-model="attendanceForm.section_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">Select</option>
              <option v-for="s in sectionOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="att-from" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From</label>
            <input id="att-from" v-model="attendanceForm.date_from" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
          </div>
          <div class="flex flex-col gap-1">
            <label for="att-to" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">To</label>
            <input id="att-to" v-model="attendanceForm.date_to" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
          </div>
          <button
            type="button"
            class="min-h-[44px] rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700"
            :disabled="!attendanceFormValid || reportLoading"
            @click="downloadAttendance"
          >
            Download CSV
          </button>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Marks Sheet</h3>
        <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">Export marks for an exam and class/section (CSV).</p>
        <div class="flex flex-wrap items-end gap-3">
          <div class="flex flex-col gap-1">
            <label for="marks-exam" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Exam</label>
            <select id="marks-exam" v-model="marksForm.exam_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">Select</option>
              <option v-for="e in exams" :key="e.id" :value="e.id">{{ e.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="marks-class" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
            <select id="marks-class" v-model="marksForm.class_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">Select</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="marks-section" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</label>
            <select id="marks-section" v-model="marksForm.section_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">Select</option>
              <option v-for="s in sectionOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
            </select>
          </div>
          <button
            type="button"
            class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700"
            :disabled="!marksFormValid || reportLoading"
            @click="downloadMarks"
          >
            Download CSV
          </button>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Homework Summary</h3>
        <p class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">Export list of homework (CSV). Optional filters.</p>
        <div class="flex flex-wrap items-end gap-3">
          <div class="flex flex-col gap-1">
            <label for="hw-class" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
            <select id="hw-class" v-model="homeworkForm.class_id" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
              <option value="">All</option>
              <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
            </select>
          </div>
          <div class="flex flex-col gap-1">
            <label for="hw-from" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From</label>
            <input id="hw-from" v-model="homeworkForm.date_from" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
          </div>
          <div class="flex flex-col gap-1">
            <label for="hw-to" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">To</label>
            <input id="hw-to" v-model="homeworkForm.date_to" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
          </div>
          <button
            type="button"
            class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700"
            :disabled="reportLoading"
            @click="downloadHomework"
          >
            Download CSV
          </button>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/authStore.js';
import PageContainer from '../components/PageContainer.vue';
import { getExams } from '../services/examService.js';
import { downloadAttendanceSummary, downloadMarksSheet, downloadHomeworkSummary } from '../services/reportService.js';

const authStore = useAuthStore();
const assignments = computed(() => authStore.assignments ?? []);
const currentSession = computed(() => authStore.currentAcademicSession);

const classOptions = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    const k = a.class_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.class_id, name: a.class_name }));
});
const sectionOptions = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    const k = a.section_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.section_id, name: a.section_name }));
});

const exams = ref([]);
const reportLoading = ref(false);
const attendanceForm = ref({
  academic_session_id: '',
  class_id: '',
  section_id: '',
  date_from: '',
  date_to: '',
});
const marksForm = ref({ exam_id: '', class_id: '', section_id: '' });
const homeworkForm = ref({ class_id: '', date_from: '', date_to: '' });

const attendanceFormValid = computed(() => {
  return currentSession.value?.id && attendanceForm.value.class_id && attendanceForm.value.section_id && attendanceForm.value.date_from && attendanceForm.value.date_to;
});
const marksFormValid = computed(() => {
  return marksForm.value.exam_id && marksForm.value.class_id && marksForm.value.section_id;
});

async function loadExams() {
  try {
    exams.value = await getExams({});
  } catch {
    exams.value = [];
  }
}

function setDefaultDates() {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 1, 0);
  attendanceForm.value.date_from = start.toISOString().slice(0, 10);
  attendanceForm.value.date_to = end.toISOString().slice(0, 10);
}

async function downloadAttendance() {
  if (!attendanceFormValid.value) return;
  reportLoading.value = true;
  try {
    await downloadAttendanceSummary({
      academic_session_id: currentSession.value.id,
      class_id: attendanceForm.value.class_id,
      section_id: attendanceForm.value.section_id,
      date_from: attendanceForm.value.date_from,
      date_to: attendanceForm.value.date_to,
    });
  } catch (e) {
    alert(e.response?.data?.message ?? 'Download failed.');
  } finally {
    reportLoading.value = false;
  }
}

async function downloadMarks() {
  if (!marksFormValid.value) return;
  reportLoading.value = true;
  try {
    await downloadMarksSheet({
      exam_id: marksForm.value.exam_id,
      class_id: marksForm.value.class_id,
      section_id: marksForm.value.section_id,
    });
  } catch (e) {
    alert(e.response?.data?.message ?? 'Download failed.');
  } finally {
    reportLoading.value = false;
  }
}

async function downloadHomework() {
  reportLoading.value = true;
  try {
    const params = {};
    if (homeworkForm.value.class_id) params.class_id = homeworkForm.value.class_id;
    if (homeworkForm.value.date_from) params.date_from = homeworkForm.value.date_from;
    if (homeworkForm.value.date_to) params.date_to = homeworkForm.value.date_to;
    if (currentSession.value?.id) params.academic_session_id = currentSession.value.id;
    await downloadHomeworkSummary(params);
  } catch (e) {
    alert(e.response?.data?.message ?? 'Download failed.');
  } finally {
    reportLoading.value = false;
  }
}

onMounted(() => {
  loadExams();
  if (currentSession.value?.id) {
    attendanceForm.value.academic_session_id = currentSession.value.id;
  }
  setDefaultDates();
});
</script>
