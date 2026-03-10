<template>
  <PageContainer title="Analytics" description="View trends for your classes and subjects.">
    <div class="mb-4 flex flex-wrap items-end gap-3">
      <div class="flex flex-col gap-1">
        <label for="filter-class" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</label>
        <select id="filter-class" v-model="filters.class_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="c in classOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-section" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Section</label>
        <select id="filter-section" v-model="filters.section_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="s in sectionOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-subject" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</label>
        <select id="filter-subject" v-model="filters.subject_id" class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="s in subjectOptions" :key="s.id" :value="s.id">{{ s.name }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-date-from" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From</label>
        <input id="filter-date-from" v-model="filters.date_from" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-date-to" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">To</label>
        <input id="filter-date-to" v-model="filters.date_to" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <button type="button" class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-700" @click="loadAll">Apply</button>
    </div>

    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <template v-else>
      <div class="space-y-6">
        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Attendance trends (by week)</h3>
          <div v-if="!attendanceTrends.labels?.length" class="py-4 text-sm text-zinc-500">No data for the selected range.</div>
          <div v-else class="space-y-2">
            <div v-for="(label, i) in attendanceTrends.labels" :key="label" class="flex items-center gap-2 text-sm">
              <span class="w-16 shrink-0">{{ label }}</span>
              <div class="flex flex-1 gap-1">
                <span class="rounded bg-green-200 px-2 py-0.5 dark:bg-green-900/40" :style="{ width: barWidth(attendanceTrends.present[i], attendanceMax) }">P {{ attendanceTrends.present[i] }}</span>
                <span class="rounded bg-red-200 px-2 py-0.5 dark:bg-red-900/40" :style="{ width: barWidth(attendanceTrends.absent[i], attendanceMax) }">A {{ attendanceTrends.absent[i] }}</span>
                <span class="rounded bg-amber-200 px-2 py-0.5 dark:bg-amber-900/40" :style="{ width: barWidth(attendanceTrends.late[i], attendanceMax) }">L {{ attendanceTrends.late[i] }}</span>
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Homework trends</h3>
          <div v-if="!homeworkTrends.labels?.length" class="py-4 text-sm text-zinc-500">No data.</div>
          <div v-else class="space-y-2">
            <div v-for="(label, i) in homeworkTrends.labels" :key="label" class="flex items-center gap-2 text-sm">
              <span class="w-16 shrink-0">{{ label }}</span>
              <span class="rounded bg-zinc-200 px-2 py-0.5 dark:bg-zinc-700">Count: {{ homeworkTrends.count[i] }}</span>
              <span v-if="homeworkTrends.overdue[i] > 0" class="rounded bg-amber-200 px-2 py-0.5 dark:bg-amber-900/40">Overdue: {{ homeworkTrends.overdue[i] }}</span>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Marks trends (by exam)</h3>
          <div v-if="!marksTrends.labels?.length" class="py-4 text-sm text-zinc-500">No data.</div>
          <div v-else class="flex flex-wrap gap-3">
            <div v-for="(label, i) in marksTrends.labels" :key="label" class="rounded-lg border border-zinc-200 px-4 py-2 dark:border-zinc-700">
              <span class="text-xs text-zinc-500">Exam {{ label }}</span>
              <p class="font-medium text-zinc-900 dark:text-zinc-100">Avg: {{ marksTrends.averages[i] }}</p>
            </div>
          </div>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="mb-3 text-lg font-semibold text-zinc-900 dark:text-zinc-100">Students needing attention (low attendance)</h3>
          <div v-if="!studentsNeedingAttention.length" class="py-4 text-sm text-zinc-500">No students below threshold.</div>
          <div v-else class="overflow-x-auto">
            <table class="w-full min-w-[400px] text-sm">
              <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Student</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Class · Section</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Attendance %</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="s in studentsNeedingAttention" :key="s.student_id" class="border-b border-zinc-100 dark:border-zinc-800/50">
                  <td class="px-3 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ s.student_name }}</td>
                  <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ s.class_name }} · {{ s.section_name }}</td>
                  <td class="px-3 py-2">{{ s.value }}%</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </template>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useAuthStore } from '../stores/authStore.js';
import PageContainer from '../components/PageContainer.vue';
import { getAttendanceTrends, getHomeworkTrends, getMarksTrends, getStudentsNeedingAttention } from '../services/analyticsService.js';

const authStore = useAuthStore();
const assignments = computed(() => authStore.assignments ?? []);

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
const subjectOptions = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    const k = a.subject_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  }).map((a) => ({ id: a.subject_id, name: a.subject_name }));
});

const loading = ref(true);
const filters = ref({
  class_id: '',
  section_id: '',
  subject_id: '',
  date_from: '',
  date_to: '',
});
const attendanceTrends = ref({ labels: [], present: [], absent: [], late: [] });
const homeworkTrends = ref({ labels: [], count: [], overdue: [] });
const marksTrends = ref({ labels: [], averages: [] });
const studentsNeedingAttention = ref([]);

const attendanceMax = computed(() => {
  const p = Math.max(0, ...(attendanceTrends.value.present || []));
  const a = Math.max(0, ...(attendanceTrends.value.absent || []));
  const l = Math.max(0, ...(attendanceTrends.value.late || []));
  return Math.max(p + a + l, 1);
});

function barWidth(val, max) {
  if (!max || val == null) return '0%';
  return Math.min(100, (val / max) * 100) + '%';
}

function buildParams() {
  const p = {};
  if (filters.value.class_id) p.class_id = filters.value.class_id;
  if (filters.value.section_id) p.section_id = filters.value.section_id;
  if (filters.value.subject_id) p.subject_id = filters.value.subject_id;
  if (filters.value.date_from) p.date_from = filters.value.date_from;
  if (filters.value.date_to) p.date_to = filters.value.date_to;
  return p;
}

async function loadAll() {
  loading.value = true;
  const params = buildParams();
  try {
    const [att, hw, marks, students] = await Promise.all([
      getAttendanceTrends(params),
      getHomeworkTrends(params),
      getMarksTrends(params),
      getStudentsNeedingAttention(params),
    ]);
    attendanceTrends.value = att || { labels: [], present: [], absent: [], late: [] };
    homeworkTrends.value = hw || { labels: [], count: [], overdue: [] };
    marksTrends.value = marks || { labels: [], averages: [] };
    studentsNeedingAttention.value = students?.data ?? [];
  } catch {
    attendanceTrends.value = { labels: [], present: [], absent: [], late: [] };
    homeworkTrends.value = { labels: [], count: [], overdue: [] };
    marksTrends.value = { labels: [], averages: [] };
    studentsNeedingAttention.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(loadAll);
</script>
