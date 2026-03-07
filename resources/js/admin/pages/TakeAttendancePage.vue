<template>
  <PageContainer
    title="Take Attendance"
    description="Select session, class, section and date, then mark each student's attendance."
  >
    <div class="space-y-6">
      <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
          Select group and date
        </p>
        <AttendanceFilterForm
          v-model="filter"
          :show-date="true"
          @change="onFilterChange"
        />
        <div class="mt-3">
          <button
            type="button"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
            :disabled="!canLoadStudents"
            @click="loadStudents"
          >
            Load students
          </button>
        </div>
      </div>

      <div v-if="studentsLoading" class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading students…
      </div>
      <div v-else-if="students.length === 0 && hasAttemptedLoad" class="rounded-xl border border-zinc-200 bg-white p-8 text-center dark:border-zinc-800 dark:bg-zinc-900">
        <p class="text-sm text-zinc-500 dark:text-zinc-400">
          No students assigned to this session, class and section. Assign students first, then take attendance.
        </p>
      </div>
      <template v-else-if="students.length > 0">
        <div>
          <p class="mb-2 text-sm font-medium text-zinc-700 dark:text-zinc-300">
            Mark attendance
          </p>
          <AttendanceEntryTable
            :students="students"
            :initial-records="[]"
            @update:records="records = $event"
          />
        </div>
        <div v-if="submitError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
          {{ submitError }}
        </div>
        <div class="flex gap-2">
          <button
            type="button"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
            :disabled="submitting"
            @click="submit"
          >
            {{ submitting ? 'Saving…' : 'Save attendance' }}
          </button>
          <router-link
            to="/admin/attendance"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          >
            Cancel
          </router-link>
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import AttendanceFilterForm from '../components/AttendanceFilterForm.vue';
import AttendanceEntryTable from '../components/AttendanceEntryTable.vue';
import { getEligibleStudents, createAttendance } from '../services/attendanceService.js';

const router = useRouter();
const filter = ref({
  academic_session_id: null,
  class_id: null,
  section_id: null,
  attendance_date: '',
});
const students = ref([]);
const records = ref([]);
const studentsLoading = ref(false);
const hasAttemptedLoad = ref(false);
const submitting = ref(false);
const submitError = ref(null);

const canLoadStudents = computed(() =>
  filter.value.academic_session_id &&
  filter.value.class_id &&
  filter.value.section_id &&
  filter.value.attendance_date
);

function onFilterChange() {
  students.value = [];
  hasAttemptedLoad.value = false;
}

async function loadStudents() {
  if (!canLoadStudents.value) return;
  studentsLoading.value = true;
  hasAttemptedLoad.value = true;
  submitError.value = null;
  try {
    students.value = await getEligibleStudents(
      filter.value.academic_session_id,
      filter.value.class_id,
      filter.value.section_id
    );
  } catch {
    students.value = [];
  } finally {
    studentsLoading.value = false;
  }
}

async function submit() {
  if (records.value.length === 0) return;
  submitting.value = true;
  submitError.value = null;
  try {
    await createAttendance({
      academic_session_id: filter.value.academic_session_id,
      class_id: filter.value.class_id,
      section_id: filter.value.section_id,
      attendance_date: filter.value.attendance_date,
      records: records.value,
    });
    router.push('/admin/attendance');
  } catch (err) {
    submitError.value = err.response?.data?.message ?? 'Failed to save attendance.';
  } finally {
    submitting.value = false;
  }
}
</script>
