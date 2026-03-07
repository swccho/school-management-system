<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <div class="flex items-center justify-between">
      <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">
        Target classes
      </p>
      <button
        type="button"
        class="rounded border border-zinc-300 bg-white px-2 py-1 text-xs font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        @click="addRow"
      >
        + Add class
      </button>
    </div>
    <div class="mt-3 space-y-2">
      <div
        v-for="(row, idx) in configs"
        :key="row._key"
        class="flex flex-wrap items-end gap-2"
      >
        <div class="min-w-[160px]">
          <SearchableSelect
            :id="`class-config-class-${idx}`"
            v-model="row.class_id"
            label="Class"
            :options="classOptionsList"
            label-key="name"
            value-key="id"
            placeholder="Class"
            @update:model-value="onClassChange(idx)"
          />
        </div>
        <div class="min-w-[160px]">
          <SearchableSelect
            :id="`class-config-section-${idx}`"
            v-model="row.section_id"
            label="Section (optional)"
            :options="sectionOptionsByClass[row.class_id] || []"
            label-key="name"
            value-key="id"
            placeholder="Section or whole class"
            clearable
            @update:model-value="emitUpdate"
          />
        </div>
        <button
          type="button"
          class="rounded p-1.5 text-zinc-500 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-900/20 dark:hover:text-red-400"
          aria-label="Remove"
          @click="removeRow(idx)"
        >
          ×
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getClasses, getSections } from '../services/examService.js';

const props = defineProps({
  modelValue: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue']);

const configs = ref(
  (props.modelValue.length ? props.modelValue : [{ class_id: null, section_id: null, status: 'active' }]).map((c, i) => ({
    ...c,
    _key: `cfg-${i}-${Date.now()}`,
  }))
);
const classOptionsList = ref([]);
const sectionOptionsByClass = ref({});

async function loadSectionsForClass(classId) {
  if (!classId) {
    return [];
  }
  try {
    const list = await getSections(classId);
    return list;
  } catch {
    return [];
  }
}

function onClassChange(idx) {
  configs.value[idx].section_id = null;
  const classId = configs.value[idx].class_id;
  loadSectionsForClass(classId).then((s) => {
    sectionOptionsByClass.value = { ...sectionOptionsByClass.value, [classId]: s };
  });
  emitUpdate();
}

function addRow() {
  configs.value.push({
    class_id: null,
    section_id: null,
    status: 'active',
    _key: `cfg-${Date.now()}`,
  });
  emitUpdate();
}

function removeRow(idx) {
  configs.value.splice(idx, 1);
  if (configs.value.length === 0) {
    configs.value.push({ class_id: null, section_id: null, status: 'active', _key: `cfg-${Date.now()}` });
  }
  emitUpdate();
}

function emitUpdate() {
  const out = configs.value.map(({ _key, ...rest }) => rest);
  emit('update:modelValue', out);
}

watch(() => props.modelValue, (v) => {
  if (v && v.length) {
    configs.value = v.map((c, i) => ({ ...c, _key: c._key || `cfg-${i}-${Date.now()}` }));
    configs.value.forEach((c) => {
      if (c.class_id && !sectionOptionsByClass.value[c.class_id]) {
        loadSectionsForClass(c.class_id).then((s) => {
          sectionOptionsByClass.value = { ...sectionOptionsByClass.value, [c.class_id]: s };
        });
      }
    });
  }
}, { deep: true });

onMounted(async () => {
  try {
    classOptionsList.value = await getClasses();
  } catch {
    classOptionsList.value = [];
  }
  configs.value.forEach((c) => {
    if (c.class_id) {
      loadSectionsForClass(c.class_id).then((s) => {
        sectionOptionsByClass.value = { ...sectionOptionsByClass.value, [c.class_id]: s };
      });
    }
  });
});
</script>
