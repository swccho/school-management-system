<template>
  <tr class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
    <td class="px-2 py-1.5">
      <input
        v-model.number="local.period_no"
        type="number"
        min="1"
        max="99"
        class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @change="emitUpdate"
      />
    </td>
    <td class="px-2 py-1.5">
      <input
        v-model="local.start_time"
        type="time"
        class="w-28 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @change="emitUpdate"
      />
    </td>
    <td class="px-2 py-1.5">
      <input
        v-model="local.end_time"
        type="time"
        class="w-28 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @change="emitUpdate"
      />
    </td>
    <td class="px-2 py-1.5 min-w-[140px]">
      <SearchableSelect
        :id="`subject-${rowId}`"
        v-model="local.subject_id"
        :options="subjectOptions"
        label-key="name"
        value-key="id"
        placeholder="Subject"
        search-placeholder="Search…"
        @update:model-value="emitUpdate"
      />
    </td>
    <td class="px-2 py-1.5 min-w-[140px]">
      <SearchableSelect
        :id="`teacher-${rowId}`"
        v-model="local.teacher_id"
        :options="teacherOptions"
        label-key="full_name"
        value-key="id"
        placeholder="Teacher"
        search-placeholder="Search…"
        clearable
        @update:model-value="emitUpdate"
      />
    </td>
    <td class="px-2 py-1.5">
      <input
        v-model="local.room_label"
        type="text"
        class="w-24 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="Room"
        maxlength="100"
        @input="emitUpdate"
      />
    </td>
    <td v-if="showRemove" class="px-2 py-1.5">
      <button
        type="button"
        class="rounded p-1 text-zinc-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
        aria-label="Remove period"
        @click="emit('remove')"
      >
        <span class="text-sm">×</span>
      </button>
    </td>
  </tr>
</template>

<script setup>
import { ref, watch } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';

const props = defineProps({
  modelValue: { type: Object, required: true },
  subjectOptions: { type: Array, default: () => [] },
  teacherOptions: { type: Array, default: () => [] },
  rowId: { type: [String, Number], default: '' },
  showRemove: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'remove']);

const local = ref({
  period_no: props.modelValue.period_no ?? 1,
  start_time: props.modelValue.start_time ?? '09:00',
  end_time: props.modelValue.end_time ?? '09:45',
  subject_id: props.modelValue.subject_id ?? null,
  teacher_id: props.modelValue.teacher_id ?? null,
  room_label: props.modelValue.room_label ?? '',
  remarks: props.modelValue.remarks ?? '',
});

function emitUpdate() {
  emit('update:modelValue', {
    ...props.modelValue,
    period_no: local.value.period_no,
    start_time: local.value.start_time,
    end_time: local.value.end_time,
    subject_id: local.value.subject_id,
    teacher_id: local.value.teacher_id,
    room_label: local.value.room_label,
    remarks: local.value.remarks,
  });
}

watch(() => props.modelValue, (v) => {
  if (v) {
    local.value.period_no = v.period_no ?? 1;
    local.value.start_time = v.start_time ?? '09:00';
    local.value.end_time = v.end_time ?? '09:45';
    local.value.subject_id = v.subject_id ?? null;
    local.value.teacher_id = v.teacher_id ?? null;
    local.value.room_label = v.room_label ?? '';
    local.value.remarks = v.remarks ?? '';
  }
}, { deep: true });
</script>
