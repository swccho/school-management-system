<template>
  <tr class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
    <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">
      {{ student.full_name ?? '—' }}
    </td>
    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">
      {{ student.admission_no ?? student.roll_no ?? '—' }}
    </td>
    <template v-if="markComponents.length > 0">
      <td
        v-for="comp in markComponents"
        :key="comp.id"
        class="px-2 py-2"
      >
        <input
          :ref="(el) => setInputRef(comp.id, el)"
          type="number"
          :min="0"
          :max="comp.marks"
          :value="getObtained(comp.id)"
          class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          :placeholder="`/ ${comp.marks}`"
          @input="(e) => setObtained(comp.id, e.target.value)"
        />
      </td>
    </template>
    <td v-else class="px-2 py-2">
      <input
        type="number"
        :min="0"
        :max="fullMarks"
        :value="getObtained(null)"
        class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        :placeholder="`/ ${fullMarks}`"
        @input="(e) => setObtained(null, e.target.value)"
      />
    </td>
    <td class="px-4 py-2.5 text-sm text-zinc-600 dark:text-zinc-400">
      {{ rowTotal }}
    </td>
  </tr>
</template>

<script setup>
import { computed } from 'vue';

const props = defineProps({
  student: { type: Object, required: true },
  markComponents: { type: Array, default: () => [] },
  fullMarks: { type: Number, default: 100 },
  items: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:items']);

const localItems = computed({
  get: () => props.items,
  set: (val) => emit('update:items', val),
});

function getObtained(componentId) {
  const item = (props.items || []).find((i) => (i.mark_component_id ?? null) === (componentId ?? null));
  return item ? item.obtained_marks : '';
}

function setObtained(componentId, value) {
  const num = value === '' ? 0 : parseInt(value, 10);
  const key = componentId ?? null;
  const current = props.items || [];
  const idx = current.findIndex((i) => (i.mark_component_id ?? null) === key);
  const next = [...current];
  if (idx >= 0) {
    next[idx] = { ...next[idx], obtained_marks: num };
  } else {
    next.push({ mark_component_id: key, obtained_marks: num });
  }
  emit('update:items', next);
}

function setInputRef() {}

const rowTotal = computed(() => {
  const items = props.items || [];
  return items.reduce((sum, i) => sum + (parseInt(i.obtained_marks, 10) || 0), 0);
});
</script>
