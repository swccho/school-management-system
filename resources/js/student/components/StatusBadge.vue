<template>
  <span
    class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium"
    :class="computedClass"
  >
    {{ displayLabel }}
  </span>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  status: { type: String, default: '' },
  type: { type: String, default: 'default' },
  label: { type: String, default: null },
});

const typeMap = {
  assignment: { pass: 'Pass', submitted: 'Submitted', pending: 'Pending', overdue: 'Overdue', late: 'Late', graded: 'Graded', closed: 'Closed', draft: 'Draft' },
  attendance: { present: 'Present', absent: 'Absent', late: 'Late', leave: 'Leave' },
  fee: { paid: 'Paid', unpaid: 'Unpaid', partially_paid: 'Partially paid', overdue: 'Overdue' },
  default: {},
};

const classMap = {
  pass: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
  submitted: 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
  pending: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
  overdue: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
  absent: 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
  present: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
  late: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
  leave: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
  paid: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
  unpaid: 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200',
  partially_paid: 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-200',
  draft: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200',
  graded: 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
  closed: 'bg-zinc-100 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200',
};

const displayLabel = computed(() => {
  if (props.label) return props.label;
  const map = typeMap[props.type] ?? typeMap.default;
  return map[props.status] ?? (props.status ? String(props.status) : '—');
});

const computedClass = computed(() => {
  const key = props.status?.toLowerCase().replace(/\s+/g, '_') ?? 'default';
  return classMap[key] ?? 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-200';
});
</script>
