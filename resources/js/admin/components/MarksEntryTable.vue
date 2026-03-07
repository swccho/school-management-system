<template>
  <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="overflow-x-auto">
      <table class="w-full min-w-[500px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Admission / Roll</th>
            <template v-if="markComponents.length > 0">
              <th
                v-for="comp in markComponents"
                :key="comp.id"
                class="px-2 py-3 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400"
              >
                {{ comp.name }} ({{ comp.marks }})
              </th>
            </template>
            <th v-else class="px-2 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">
              Obtained (max {{ fullMarks }})
            </th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Total</th>
          </tr>
        </thead>
        <tbody>
          <MarksEntryRow
            v-for="(row, idx) in rows"
            :key="row.student_id"
            :student="row.student"
            :mark-components="markComponents"
            :full-marks="fullMarks"
            :items="row.items"
            @update:items="(items) => updateRowItems(idx, items)"
          />
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import MarksEntryRow from './MarksEntryRow.vue';

const props = defineProps({
  students: { type: Array, default: () => [] },
  markComponents: { type: Array, default: () => [] },
  fullMarks: { type: Number, default: 100 },
  existingEntries: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:entries']);

const rows = ref(
  props.students.map((s) => {
    const existing = props.existingEntries.find((e) => e.student_id === s.student_id);
    const items = existing?.items?.map((i) => ({
      mark_component_id: i.mark_component_id ?? null,
      obtained_marks: i.obtained_marks ?? 0,
    })) ?? (props.markComponents.length > 0
      ? props.markComponents.map((c) => ({ mark_component_id: c.id, obtained_marks: 0 }))
      : [{ mark_component_id: null, obtained_marks: 0 }]);
    return { student_id: s.student_id, student: s, items };
  })
);

function updateRowItems(idx, items) {
  if (rows.value[idx]) {
    rows.value[idx].items = items;
  }
  emitEntries();
}

function emitEntries() {
  emit('update:entries', rows.value.map((r) => ({
    student_id: r.student_id,
    items: r.items,
  })));
}

watch(rows, () => emitEntries(), { deep: true });

watch(() => [props.students, props.existingEntries], () => {
  rows.value = props.students.map((s) => {
    const existing = props.existingEntries.find((e) => e.student_id === s.student_id);
    const items = existing?.items?.map((i) => ({
      mark_component_id: i.mark_component_id ?? null,
      obtained_marks: i.obtained_marks ?? 0,
    })) ?? (props.markComponents.length > 0
      ? props.markComponents.map((c) => ({ mark_component_id: c.id, obtained_marks: 0 }))
      : [{ mark_component_id: null, obtained_marks: 0 }]);
    return { student_id: s.student_id, student: s, items };
  });
}, { deep: true });
</script>
