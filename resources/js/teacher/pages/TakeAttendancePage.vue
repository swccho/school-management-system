<template>
  <PageContainer title="Take Attendance" description="Mark student attendance.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ error }}</div>
    <div v-else class="space-y-4">
      <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ session?.class_name }} · {{ session?.section_name }} — {{ session?.attendance_date }} ({{ session?.status }})</p>
      <div v-if="session?.status !== 'final'" class="flex flex-wrap gap-2">
        <button type="button" class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900" @click="saveDraft">Save draft</button>
        <button type="button" class="rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" @click="submit">Submit</button>
      </div>
      <div class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <table class="w-full min-w-[400px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Roll</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="r in records" :key="r.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
              <td class="px-4 py-3 text-sm">{{ r.roll_no }}</td>
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ r.student_name }}</td>
              <td class="px-4 py-3">
                <select v-model="r.attendance_status" class="rounded-lg border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
                  <option value="present">Present</option>
                  <option value="absent">Absent</option>
                  <option value="late">Late</option>
                  <option value="leave">Leave</option>
                </select>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getAttendanceSession, updateAttendanceSession, submitAttendanceSession } from '../services/attendanceService.js';
import { useToast } from '../../shared/composables/useToast.js';

const route = useRoute();
const toast = useToast();
const sessionId = ref(route.params.sessionId);
const loading = ref(true);
const error = ref(null);
const session = ref(null);
const records = ref([]);

async function load() {
  loading.value = true;
  error.value = null;
  try {
    const data = await getAttendanceSession(sessionId.value);
    session.value = data;
    records.value = (data?.records ?? []).map((r) => ({ ...r }));
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load attendance.';
  } finally {
    loading.value = false;
  }
}

async function saveDraft() {
  try {
    await updateAttendanceSession(sessionId.value, {
      records: records.value.map((r) => ({ student_id: r.student_id, attendance_status: r.attendance_status })),
    });
    toast.success('Draft saved.');
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to save.');
  }
}

async function submit() {
  try {
    await updateAttendanceSession(sessionId.value, {
      records: records.value.map((r) => ({ student_id: r.student_id, attendance_status: r.attendance_status })),
    });
    await submitAttendanceSession(sessionId.value);
    toast.success('Attendance submitted.');
    await load();
  } catch (e) {
    toast.error(e.response?.data?.message ?? 'Failed to submit.');
  }
}

watch(() => route.params.sessionId, (id) => {
  if (id) {
    sessionId.value = id;
    load();
  }
});

onMounted(() => load());
</script>
