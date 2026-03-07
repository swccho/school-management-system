<template>
  <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="sidenav-scroll overflow-x-auto">
      <table class="w-full min-w-[600px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Student
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Admission / Roll
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Status
            </th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, idx) in rows"
            :key="row.student_id"
            class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
          >
            <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">
              {{ row.full_name ?? '—' }}
            </td>
            <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">
              {{ row.admission_no ?? row.roll_no ?? '—' }}
            </td>
            <td class="px-4 py-2.5">
              <div class="flex flex-wrap gap-1">
                <button
                  v-for="status in statusOptions"
                  :key="status.value"
                  type="button"
                  class="rounded border px-2 py-1 text-xs font-medium transition-colors"
                  :class="row.attendance_status === status.value
                    ? status.activeClass
                    : 'border-zinc-200 bg-zinc-50 text-zinc-600 hover:bg-zinc-100 dark:border-zinc-700 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                  @click="setStatus(idx, status.value)"
                >
                  {{ status.label }}
                </button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
    <div v-if="rows.length > 0" class="border-t border-zinc-200 px-4 py-2 text-sm text-zinc-500 dark:border-zinc-800 dark:text-zinc-400">
      Present: {{ summary.present }} · Absent: {{ summary.absent }} · Late: {{ summary.late }} · Leave: {{ summary.leave }}
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue';

const props = defineProps({
  students: { type: Array, default: () => [] },
  initialRecords: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:records']);

const statusOptions = [
  { value: 'present', label: 'Present', activeClass: 'border-emerald-500 bg-emerald-50 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-300' },
  { value: 'absent', label: 'Absent', activeClass: 'border-red-500 bg-red-50 text-red-800 dark:bg-red-900/30 dark:text-red-300' },
  { value: 'late', label: 'Late', activeClass: 'border-amber-500 bg-amber-50 text-amber-800 dark:bg-amber-900/30 dark:text-amber-300' },
  { value: 'leave', label: 'Leave', activeClass: 'border-blue-500 bg-blue-50 text-blue-800 dark:bg-blue-900/30 dark:text-blue-300' },
];

const rows = ref([]);

function buildRows() {
  const initialMap = {};
  (props.initialRecords || []).forEach((r) => {
    initialMap[r.student_id] = r.attendance_status;
  });
  rows.value = props.students.map((s) => ({
    student_id: s.student_id,
    student_academic_assignment_id: s.student_academic_assignment_id ?? s.id,
    full_name: s.full_name,
    admission_no: s.admission_no,
    roll_no: s.roll_no,
    attendance_status: initialMap[s.student_id] ?? 'present',
  }));
}

const summary = computed(() => {
  let present = 0;
  let absent = 0;
  let late = 0;
  let leave = 0;
  rows.value.forEach((r) => {
    if (r.attendance_status === 'present') present++;
    else if (r.attendance_status === 'absent') absent++;
    else if (r.attendance_status === 'late') late++;
    else if (r.attendance_status === 'leave') leave++;
  });
  return { present, absent, late, leave };
});

function setStatus(idx, status) {
  const r = rows.value[idx];
  if (!r) return;
  r.attendance_status = status;
  emitRecords();
}

function emitRecords() {
  emit('update:records', rows.value.map((r) => ({
    student_id: r.student_id,
    student_academic_assignment_id: r.student_academic_assignment_id,
    attendance_status: r.attendance_status,
    reason: null,
    remarks: null,
  })));
}

watch(() => [props.students, props.initialRecords], () => {
  buildRows();
  emitRecords();
}, { deep: true });

watch(rows, () => emitRecords(), { deep: true });

onMounted(() => {
  buildRows();
  emitRecords();
});
</script>
