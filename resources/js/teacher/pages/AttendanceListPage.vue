<template>
  <PageContainer title="Attendance" description="Take and view attendance for your classes.">
    <div class="mb-4 flex flex-wrap gap-4">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Date from</label>
        <input v-model="dateFrom" type="date" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Date to</label>
        <input v-model="dateTo" type="date" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section</label>
        <select v-model="classSectionValue" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="a in uniqueClassSections" :key="a.class_id + '-' + a.section_id" :value="a.class_id + '-' + a.section_id">{{ a.class_name }} · {{ a.section_name }}</option>
        </select>
      </div>
      <button type="button" class="self-end rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900" @click="fetchSessions">Apply</button>
    </div>
    <div v-if="uniqueClassSections.length && createDate" class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Create attendance for date</label>
        <input v-model="createDate" type="date" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100" />
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section</label>
        <select v-model="createClassSectionValue" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">Select</option>
          <option v-for="a in uniqueClassSections" :key="'c-' + a.class_id + '-' + a.section_id" :value="a.class_id + '-' + a.section_id">{{ a.class_name }} · {{ a.section_name }}</option>
        </select>
      </div>
      <button type="button" class="rounded-lg border border-emerald-600 bg-emerald-600 px-4 py-2 text-sm font-medium text-white hover:bg-emerald-700" :disabled="creating || !createClassSectionValue" @click="createSession">
        {{ creating ? 'Creating…' : 'Create attendance' }}
      </button>
      <p v-if="createError" class="text-sm text-red-600 dark:text-red-400">{{ createError }}</p>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="sessions.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">No attendance sessions found.</div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Date</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Action</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="s in sessions" :key="s.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ s.attendance_date }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.class_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.section_name }}</td>
            <td class="px-4 py-3">
              <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium" :class="s.status === 'final' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'">{{ s.status }}</span>
            </td>
            <td class="px-4 py-3">
              <router-link :to="'/teacher/attendance/take/' + s.id" class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100">Take / View</router-link>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import { getAttendanceSessions, createAttendanceSession } from '../services/attendanceService.js';

const router = useRouter();
const authStore = useAuthStore();
const loading = ref(false);
const sessions = ref([]);
const dateFrom = ref(new Date().toISOString().slice(0, 10));
const dateTo = ref(new Date().toISOString().slice(0, 10));
const classSectionValue = ref('');
const createDate = ref(new Date().toISOString().slice(0, 10));
const createClassSectionValue = ref('');
const creating = ref(false);
const createError = ref('');

const assignments = computed(() => authStore.assignments ?? []);
const uniqueClassSections = computed(() => {
  const seen = new Set();
  return assignments.value.filter((a) => {
    const k = a.class_id + '-' + a.section_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  });
});

async function fetchSessions() {
  loading.value = true;
  try {
    const params = { date_from: dateFrom.value, date_to: dateTo.value };
    if (classSectionValue.value) {
      const [class_id, section_id] = classSectionValue.value.split('-').map(Number);
      params.class_id = class_id;
      params.section_id = section_id;
    }
    sessions.value = await getAttendanceSessions(params);
  } catch {
    sessions.value = [];
  } finally {
    loading.value = false;
  }
}

async function createSession() {
  if (!createClassSectionValue.value) return;
  const [class_id, section_id] = createClassSectionValue.value.split('-').map(Number);
  creating.value = true;
  createError.value = '';
  try {
    const res = await createAttendanceSession({
      class_id,
      section_id,
      attendance_date: createDate.value,
    });
    if (res?.session?.id) {
      router.push('/teacher/attendance/take/' + res.session.id);
    } else {
      fetchSessions();
    }
  } catch (e) {
    createError.value = e.response?.data?.message ?? 'Failed to create attendance.';
  } finally {
    creating.value = false;
  }
}

onMounted(() => fetchSessions());
</script>
