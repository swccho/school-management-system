<template>
  <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Context</p>
    <div class="flex flex-wrap items-end gap-4">
      <div class="min-w-[180px]">
        <SearchableSelect
          id="gen-exam"
          v-model="form.exam_id"
          label="Exam"
          :options="examOptions"
          label-key="name"
          value-key="id"
          placeholder="Select exam…"
          search-placeholder="Search…"
          clearable
          @update:model-value="emitChange"
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="gen-class"
          v-model="form.class_id"
          label="Class (optional)"
          :options="classOptions"
          label-key="name"
          value-key="id"
          placeholder="All classes…"
          search-placeholder="Search…"
          clearable
          @update:model-value="onClassChange"
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="gen-section"
          v-model="form.section_id"
          label="Section (optional)"
          :options="sectionOptions"
          label-key="name"
          value-key="id"
          placeholder="All sections…"
          search-placeholder="Search…"
          :disabled="!form.class_id"
          clearable
          @update:model-value="emitChange"
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="gen-scale"
          v-model="form.grade_scale_id"
          label="Grade scale (optional)"
          :options="gradeScaleOptions"
          label-key="name"
          value-key="id"
          placeholder="Use default…"
          search-placeholder="Search…"
          clearable
          @update:model-value="emitChange"
        />
      </div>
    </div>
    <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">
      Results are generated from saved marks. Leave class/section empty to include all students with marks for this exam.
    </p>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getExams, getClasses, getSections } from '../services/marksEntryService.js';
import { getGradeScales } from '../services/gradeScaleService.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['update:modelValue', 'change']);

const form = ref({
  exam_id: props.modelValue.exam_id ?? null,
  class_id: props.modelValue.class_id ?? null,
  section_id: props.modelValue.section_id ?? null,
  grade_scale_id: props.modelValue.grade_scale_id ?? null,
});

const examOptions = ref([]);
const classOptions = ref([]);
const sectionOptions = ref([]);
const gradeScaleOptions = ref([]);

async function loadExams() {
  try {
    examOptions.value = await getExams();
  } catch {
    examOptions.value = [];
  }
}

async function loadClasses() {
  try {
    classOptions.value = await getClasses();
  } catch {
    classOptions.value = [];
  }
}

async function loadSections(classId) {
  if (!classId) {
    sectionOptions.value = [];
    return;
  }
  try {
    const data = await getSections(classId);
    sectionOptions.value = Array.isArray(data) ? data : [];
  } catch {
    sectionOptions.value = [];
  }
}

async function loadGradeScales() {
  try {
    gradeScaleOptions.value = await getGradeScales();
  } catch {
    gradeScaleOptions.value = [];
  }
}

function onClassChange() {
  form.value.section_id = null;
  loadSections(form.value.class_id);
  emitChange();
}

function emitChange() {
  emit('update:modelValue', { ...form.value });
  emit('change', { ...form.value });
}

watch(() => props.modelValue, (v) => {
  if (v) {
    form.value.exam_id = v.exam_id ?? null;
    form.value.class_id = v.class_id ?? null;
    form.value.section_id = v.section_id ?? null;
    form.value.grade_scale_id = v.grade_scale_id ?? null;
    if (form.value.class_id) loadSections(form.value.class_id);
  }
}, { deep: true });

onMounted(async () => {
  await Promise.all([loadExams(), loadClasses(), loadGradeScales()]);
  if (form.value.class_id) await loadSections(form.value.class_id);
});
</script>
