<template>
  <PageContainer title="Attendance" description="View your attendance summary and history.">
    <div class="mb-4 flex flex-wrap items-center gap-3">
      <label class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Month:</label>
      <select
        v-model="month"
        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @change="fetchAttendance"
      >
        <option value="">All</option>
        <option v-for="m in 12" :key="m" :value="m">{{ monthName(m) }}</option>
      </select>
      <select
        v-model="year"
        class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @change="fetchAttendance"
      >
        <option v-for="y in yearOptions" :key="y" :value="y">{{ y }}</option>
      </select>
    </div>
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Attendance %</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ summary.percentage != null ? `${summary.percentage}%` : '—' }}
          </p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Present</h3>
          <p class="mt-1 text-2xl font-semibold text-green-700 dark:text-green-400">{{ summary.present ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Absent</h3>
          <p class="mt-1 text-2xl font-semibold text-red-700 dark:text-red-400">{{ summary.absent ?? 0 }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Late</h3>
          <p class="mt-1 text-2xl font-semibold text-amber-700 dark:text-amber-400">{{ summary.late ?? 0 }}</p>
        </div>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="border-b border-zinc-200 px-4 py-3 text-sm font-semibold text-zinc-900 dark:border-zinc-800 dark:text-zinc-100">Attendance history</h3>
        <div v-if="!records.length" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No attendance records found.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[300px] text-sm">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Date</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left font-medium text-zinc-700 dark:text-zinc-300">In / Out</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="r in records"
                :key="r.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-zinc-900 dark:text-zinc-100">{{ r.attendance_date }}</td>
                <td class="px-4 py-3">
                  <span
                    class="rounded px-2 py-0.5 text-xs font-medium"
                    :class="statusBadgeClass(r.attendance_status)"
                  >
                    {{ r.attendance_status }}{{ r.is_late ? ' (Late)' : '' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ r.in_time ?? '—' }} / {{ r.out_time ?? '—' }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getAttendance } from '../services/attendanceService.js';

const loading = ref(true);
const error = ref(null);
const data = ref(null);
const month = ref('');
const year = ref(new Date().getFullYear());

const yearOptions = computed(() => {
  const y = new Date().getFullYear();
  return [y, y - 1, y - 2];
});

const summary = computed(() => data.value?.summary ?? { present: 0, absent: 0, late: 0, leave: 0, total: 0, percentage: null });
const records = computed(() => data.value?.records ?? []);

function monthName(m) {
  const names = ['', 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
  return names[m] ?? m;
}

function statusBadgeClass(status) {
  const classes = {
    present: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
    absent: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
    late: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
    leave: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
  };
  return classes[status] ?? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300';
}

async function fetchAttendance() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (month.value) params.month = month.value;
    if (year.value) params.year = year.value;
    data.value = await getAttendance(params);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load attendance.';
    data.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAttendance);
</script>
