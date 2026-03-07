<template>
  <PageContainer
    title="Attendance details"
    :description="session ? `${session.class_name} – ${session.section_name} on ${session.attendance_date_formatted ?? session.attendance_date ?? '—'}` : ''"
  >
    <template #actions>
      <router-link
        to="/admin/attendance"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to list
      </router-link>
    </template>

    <div class="space-y-6">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <template v-else-if="session">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
            Summary
          </p>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Total: {{ session.summary?.total ?? 0 }} · Present: {{ session.summary?.present ?? 0 }} · Absent: {{ session.summary?.absent ?? 0 }} · Late: {{ session.summary?.late ?? 0 }} · Leave: {{ session.summary?.leave ?? 0 }}
          </p>
          <p v-if="session.taken_by_name" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
            Taken by {{ session.taken_by_name }}
          </p>
        </div>

        <div v-if="isEditing" class="space-y-4">
          <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
            Edit attendance
          </p>
          <AttendanceEntryTable
            :students="studentsFromRecords"
            :initial-records="session.records"
            @update:records="editRecords = $event"
          />
          <div v-if="updateError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
            {{ updateError }}
          </div>
          <div class="flex gap-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="updating"
              @click="saveUpdate"
            >
              {{ updating ? 'Saving…' : 'Save changes' }}
            </button>
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              @click="isEditing = false"
            >
              Cancel
            </button>
          </div>
        </div>
        <div v-else>
          <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
            <div class="overflow-x-auto">
              <table class="w-full min-w-[500px]">
                <thead>
                  <tr class="border-b border-zinc-200 dark:border-zinc-800">
                    <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission / Roll</th>
                    <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr
                    v-for="r in session.records"
                    :key="r.student_id"
                    class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                  >
                    <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ r.student_name ?? '—' }}</td>
                    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">{{ r.admission_no ?? r.roll_no ?? '—' }}</td>
                    <td class="px-4 py-2.5">
                      <span
                        class="inline-flex rounded px-2 py-0.5 text-xs font-medium"
                        :class="statusClass(r.attendance_status)"
                      >
                        {{ r.attendance_status }}
                      </span>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="mt-4">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              @click="startEdit"
            >
              Edit attendance
            </button>
          </div>
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import AttendanceEntryTable from '../components/AttendanceEntryTable.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getAttendanceSession, updateAttendance } from '../services/attendanceService.js';

const toast = useToast();

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const session = ref(null);
const isEditing = ref(false);
const editRecords = ref([]);
const updating = ref(false);
const updateError = ref(null);

const studentsFromRecords = computed(() => {
  if (!session.value?.records) return [];
  return session.value.records.map((r) => ({
    student_id: r.student_id,
    student_academic_assignment_id: null,
    full_name: r.student_name,
    admission_no: r.admission_no,
    roll_no: r.roll_no,
  }));
});

function statusClass(status) {
  const map = {
    present: 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300',
    absent: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300',
    late: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300',
    leave: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300',
  };
  return map[status] ?? 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300';
}

function startEdit() {
  editRecords.value = (session.value?.records ?? []).map((r) => ({
    student_id: r.student_id,
    student_academic_assignment_id: null,
    attendance_status: r.attendance_status,
    reason: r.reason,
    remarks: r.remarks,
  }));
  isEditing.value = true;
}

async function saveUpdate() {
  updating.value = true;
  updateError.value = null;
  try {
    const updated = await updateAttendance(route.params.id, {
      records: editRecords.value,
    });
    session.value = updated.attendance_session;
    isEditing.value = false;
    toast.success('Attendance updated successfully.');
  } catch (err) {
    const msg = err.response?.data?.message ?? 'Failed to update attendance.';
    updateError.value = msg;
    toast.error(msg);
  } finally {
    updating.value = false;
  }
}

onMounted(async () => {
  try {
    session.value = await getAttendanceSession(route.params.id);
  } catch {
    error.value = 'Failed to load attendance.';
  } finally {
    loading.value = false;
  }
});
</script>
