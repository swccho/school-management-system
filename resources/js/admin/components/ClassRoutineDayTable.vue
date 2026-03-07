<template>
  <div class="rounded-lg border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between border-b border-zinc-200 px-4 py-2 dark:border-zinc-800">
      <h3 class="text-sm font-semibold text-zinc-800 dark:text-zinc-200">
        {{ dayLabel(dayOfWeek) }}
      </h3>
      <button
        type="button"
        class="rounded border border-zinc-300 bg-white px-2 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        @click="addRow"
      >
        + Add period
      </button>
    </div>
    <div class="sidenav-scroll overflow-x-auto">
      <table class="w-full min-w-[600px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Period</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Start</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">End</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Teacher</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Room</th>
            <th v-if="editable" class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Actions</th>
          </tr>
        </thead>
        <tbody>
          <ClassRoutineItemRow
            v-for="(item, idx) in dayItems"
            :key="item._key ?? idx"
            v-model="dayItems[idx]"
            :subject-options="subjectOptions"
            :teacher-options="teacherOptions"
            :row-id="`${dayOfWeek}-${idx}`"
            :show-remove="editable"
            @update:model-value="(val) => updateRow(idx, val)"
            @remove="requestRemove(idx)"
          />
        </tbody>
      </table>
    </div>
    <p v-if="dayItems.length === 0 && !editable" class="px-4 py-3 text-sm text-zinc-500 dark:text-zinc-400">
      No periods
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import ClassRoutineItemRow from './ClassRoutineItemRow.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { dayLabel } from '../services/classRoutineService.js';

const { openConfirmation, closeConfirmation } = useConfirmation();

const props = defineProps({
  dayOfWeek: { type: String, required: true },
  items: { type: Array, default: () => [] },
  subjectOptions: { type: Array, default: () => [] },
  teacherOptions: { type: Array, default: () => [] },
  editable: { type: Boolean, default: true },
});

const emit = defineEmits(['update:items']);

const dayItems = ref(
  props.items.length > 0
    ? props.items.map((i, idx) => ({ ...i, _key: `${props.dayOfWeek}-${idx}` }))
    : []
);

function addRow() {
  const nextPeriod = dayItems.value.length + 1;
  dayItems.value.push({
    day_of_week: props.dayOfWeek,
    period_no: nextPeriod,
    start_time: '09:00',
    end_time: '09:45',
    subject_id: null,
    teacher_id: null,
    room_label: '',
    remarks: '',
    _key: `${props.dayOfWeek}-${Date.now()}`,
  });
  emitItems();
}

function updateRow(idx, val) {
  dayItems.value[idx] = { ...val, _key: dayItems.value[idx]._key };
  emitItems();
}

async function requestRemove(idx) {
  const confirmed = await openConfirmation({
    title: 'Remove item',
    message: 'Remove this period from the routine?',
    confirmLabel: 'Remove',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (confirmed) {
    removeRow(idx);
    closeConfirmation();
  }
}

function removeRow(idx) {
  dayItems.value.splice(idx, 1);
  emitItems();
}

function emitItems() {
  const out = dayItems.value.map(({ _key, ...rest }) => ({
    ...rest,
    day_of_week: props.dayOfWeek,
  }));
  emit('update:items', out);
}

watch(() => props.items, (newItems) => {
  if (newItems.length !== dayItems.value.length || newItems.some((item, i) => item.period_no !== dayItems.value[i]?.period_no)) {
    dayItems.value = newItems.length > 0
      ? newItems.map((i, idx) => ({ ...i, _key: `${props.dayOfWeek}-${idx}` }))
      : [];
  }
}, { deep: true });
</script>
