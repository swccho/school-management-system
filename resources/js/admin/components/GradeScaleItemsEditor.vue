<template>
  <div>
    <div class="mb-2 flex items-center justify-between">
      <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Grade ranges</label>
      <button
        type="button"
        class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        @click="addRow"
      >
        + Add range
      </button>
    </div>
    <div class="space-y-2">
      <div
        v-for="(item, idx) in localItems"
        :key="idx"
        class="flex flex-wrap items-center gap-2 rounded-lg border border-zinc-200 bg-zinc-50/50 p-2 dark:border-zinc-700 dark:bg-zinc-800/50"
      >
        <input
          v-model.number="item.min_mark"
          type="number"
          min="0"
          max="100"
          step="0.01"
          class="w-20 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="Min"
        />
        <span class="text-zinc-500">–</span>
        <input
          v-model.number="item.max_mark"
          type="number"
          min="0"
          max="100"
          step="0.01"
          class="w-20 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="Max"
        />
        <input
          v-model="item.letter_grade"
          type="text"
          class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="Grade"
          maxlength="10"
        />
        <input
          v-model.number="item.grade_point"
          type="number"
          min="0"
          max="5"
          step="0.01"
          class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="GPA"
        />
        <input
          v-model="item.remarks"
          type="text"
          class="min-w-[80px] flex-1 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="Remarks"
        />
        <button
          v-if="localItems.length > 1"
          type="button"
          class="rounded p-1 text-zinc-500 hover:bg-zinc-200 hover:text-zinc-800 dark:hover:bg-zinc-700 dark:hover:text-zinc-200"
          aria-label="Remove"
          @click="removeRow(idx)"
        >
          ×
        </button>
      </div>
    </div>
    <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
      Ranges use percentage (0–100). Min/max must not overlap.
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const localItems = ref(
  props.modelValue?.length
    ? props.modelValue.map((i) => ({ ...i }))
    : [{ min_mark: 80, max_mark: 100, letter_grade: 'A+', grade_point: 5, remarks: '' }]
);

function addRow() {
  localItems.value.push({ min_mark: 0, max_mark: 0, letter_grade: '', grade_point: 0, remarks: '' });
  emit('update:modelValue', [...localItems.value]);
}

function removeRow(idx) {
  localItems.value.splice(idx, 1);
  emit('update:modelValue', [...localItems.value]);
}

watch(localItems, () => emit('update:modelValue', [...localItems.value]), { deep: true });
watch(() => props.modelValue, (v) => {
  if (v?.length) localItems.value = v.map((i) => ({ ...i }));
}, { deep: true });
</script>
