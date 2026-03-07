<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between">
      <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
        Subject configuration (marks per class)
      </p>
      <button
        type="button"
        class="rounded border border-zinc-300 bg-white px-2 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        @click="addRow"
      >
        + Add subject
      </button>
    </div>
    <div class="sidenav-scroll mt-3 overflow-x-auto">
      <table class="w-full min-w-[800px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Full</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Pass</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Theory</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Practical</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Oral</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Has practical</th>
            <th class="px-2 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400"></th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="(row, idx) in configs"
            :key="row._key"
            class="border-b border-zinc-100 dark:border-zinc-800/50"
          >
            <td class="px-2 py-1.5 min-w-[120px]">
              <SearchableSelect
                :id="`subj-class-${idx}`"
                v-model="row.class_id"
                :options="classOptions"
                label-key="name"
                value-key="id"
                placeholder="Class"
                @update:model-value="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5 min-w-[120px]">
              <SearchableSelect
                :id="`subj-subject-${idx}`"
                v-model="row.subject_id"
                :options="subjectOptions"
                label-key="name"
                value-key="id"
                placeholder="Subject"
                @update:model-value="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model.number="row.full_marks"
                type="number"
                min="0"
                class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model.number="row.pass_marks"
                type="number"
                min="0"
                class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model.number="row.theory_marks"
                type="number"
                min="0"
                class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                placeholder="—"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model.number="row.practical_marks"
                type="number"
                min="0"
                class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                placeholder="—"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model.number="row.oral_marks"
                type="number"
                min="0"
                class="w-16 rounded border border-zinc-300 bg-white px-2 py-1 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
                placeholder="—"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <input
                v-model="row.has_practical"
                type="checkbox"
                class="rounded border-zinc-300"
                @change="emitUpdate"
              />
            </td>
            <td class="px-2 py-1.5">
              <button
                type="button"
                class="rounded p-1 text-zinc-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
                aria-label="Remove"
                @click="removeRow(idx)"
              >
                ×
              </button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
  classOptions: { type: Array, default: () => [] },
  subjectOptions: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const configs = ref(
  (props.modelValue.length ? props.modelValue : []).map((c, i) => ({
    ...c,
    full_marks: c.full_marks ?? 100,
    pass_marks: c.pass_marks ?? 33,
    theory_marks: c.theory_marks ?? null,
    practical_marks: c.practical_marks ?? null,
    oral_marks: c.oral_marks ?? null,
    has_practical: c.has_practical ?? false,
    sort_order: c.sort_order ?? null,
    mark_components: c.mark_components ?? [],
    _key: c._key || `subj-${i}-${Date.now()}`,
  }))
);

function addRow() {
  configs.value.push({
    class_id: null,
    subject_id: null,
    full_marks: 100,
    pass_marks: 33,
    theory_marks: null,
    practical_marks: null,
    oral_marks: null,
    has_practical: false,
    sort_order: null,
    mark_components: [],
    _key: `subj-${Date.now()}`,
  });
  emitUpdate();
}

function removeRow(idx) {
  configs.value.splice(idx, 1);
  emitUpdate();
}

function emitUpdate() {
  const out = configs.value.map(({ _key, ...rest }) => ({
    ...rest,
    full_marks: rest.full_marks ?? 100,
    pass_marks: rest.pass_marks ?? 33,
  }));
  emit('update:modelValue', out);
}

watch(configs, () => emitUpdate(), { deep: true });

watch(() => props.modelValue, (v) => {
  if (v && v.length && configs.value.length !== v.length) {
    configs.value = v.map((c, i) => ({
      ...c,
      full_marks: c.full_marks ?? 100,
      pass_marks: c.pass_marks ?? 33,
      _key: c._key || `subj-${i}-${Date.now()}`,
    }));
  }
}, { deep: true });
</script>
