<template>
  <PageContainer
    title="Attendance"
    description="View and manage daily student attendance by session, class and section."
  >
    <template #actions>
      <router-link
        to="/admin/attendance/take"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Take Attendance
      </router-link>
    </template>

    <div class="space-y-4">
      <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <AttendanceFilterForm
          v-model="filters"
          :show-date="true"
        />
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            @click="fetch"
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
          Loading attendance…
        </div>
        <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ error }}
        </div>
        <div v-else-if="sessions.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No attendance records yet. Use “Take Attendance” to add daily attendance.
        </div>
        <div v-else class="sidenav-scroll overflow-x-auto">
          <table class="w-full min-w-[800px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Date</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Total</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Present</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Absent</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Late</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Leave</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Taken by</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="s in sessions"
                :key="s.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ s.attendance_date_formatted ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.academic_session_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.class_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.section_name ?? '—' }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.summary?.total ?? 0 }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.summary?.present ?? 0 }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.summary?.absent ?? 0 }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.summary?.late ?? 0 }}</td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.summary?.leave ?? 0 }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ s.taken_by_name ?? '—' }}</td>
                <td class="px-4 py-3">
                  <router-link
                    :to="`/admin/attendance/${s.id}`"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
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
import { ref, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import AttendanceFilterForm from '../components/AttendanceFilterForm.vue';
import { getAttendanceSessions } from '../services/attendanceService.js';

const loading = ref(true);
const error = ref(null);
const sessions = ref([]);
function initialFilters() {
  return { academic_session_id: null, class_id: null, section_id: null, attendance_date: '' };
}

const filters = ref(initialFilters());

function resetFilters() {
  filters.value = initialFilters();
  fetch();
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filters.value.academic_session_id) params.academic_session_id = filters.value.academic_session_id;
    if (filters.value.class_id) params.class_id = filters.value.class_id;
    if (filters.value.section_id) params.section_id = filters.value.section_id;
    if (filters.value.attendance_date) params.attendance_date = filters.value.attendance_date;
    sessions.value = await getAttendanceSessions(params);
  } catch {
    error.value = 'Failed to load attendance.';
    sessions.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetch());
</script>
