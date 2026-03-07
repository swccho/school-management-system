<template>
  <div class="flex flex-wrap items-end gap-4">
    <div class="min-w-[180px]">
      <SearchableSelect
        id="me-exam"
        v-model="form.exam_id"
        label="Exam"
        :options="examOptions"
        label-key="name"
        value-key="id"
        placeholder="Select exam…"
        search-placeholder="Search…"
        clearable
        @update:model-value="onExamChange"
      />
    </div>
    <div class="min-w-[180px]">
      <SearchableSelect
        id="me-class"
        v-model="form.class_id"
        label="Class"
        :options="classOptions"
        label-key="name"
        value-key="id"
        placeholder="Select class…"
        search-placeholder="Search…"
        clearable
        @update:model-value="onClassChange"
      />
    </div>
    <div class="min-w-[180px]">
      <SearchableSelect
        id="me-section"
        v-model="form.section_id"
        label="Section (optional)"
        :options="sectionOptions"
        label-key="name"
        value-key="id"
        placeholder="Whole class or section…"
        search-placeholder="Search…"
        :disabled="!form.class_id"
        clearable
        @update:model-value="emitChange"
      />
    </div>
    <div v-if="showSubject" class="min-w-[180px]">
      <SearchableSelect
        id="me-subject"
        v-model="form.subject_id"
        label="Subject"
        :options="subjectOptions"
        label-key="name"
        value-key="id"
        placeholder="Select subject…"
        search-placeholder="Search…"
        :disabled="!form.exam_id || !form.class_id"
        clearable
        @update:model-value="emitChange"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getExams, getClasses, getSections, getSubjectsForContext } from '../services/marksEntryService.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  showSubject: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

const form = ref({
  exam_id: props.modelValue.exam_id ?? null,
  class_id: props.modelValue.class_id ?? null,
  section_id: props.modelValue.section_id ?? null,
  subject_id: props.modelValue.subject_id ?? null,
});

const examOptions = ref([]);
const classOptions = ref([]);
const sectionOptions = ref([]);
const subjectOptions = ref([]);

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
    sectionOptions.value = await getSections(classId);
  } catch {
    sectionOptions.value = [];
  }
}

async function loadSubjectsForContext() {
  if (!form.value.exam_id || !form.value.class_id) {
    subjectOptions.value = [];
    return;
  }
  try {
    subjectOptions.value = await getSubjectsForContext(form.value.exam_id, form.value.class_id);
  } catch {
    subjectOptions.value = [];
  }
}

function onExamChange() {
  form.value.subject_id = null;
  loadSubjectsForContext();
  emitChange();
}

function onClassChange() {
  form.value.section_id = null;
  form.value.subject_id = null;
  loadSections(form.value.class_id);
  loadSubjectsForContext();
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
    form.value.subject_id = v.subject_id ?? null;
    if (form.value.class_id) loadSections(form.value.class_id);
    if (form.value.exam_id && form.value.class_id) loadSubjectsForContext();
  }
}, { deep: true });

onMounted(async () => {
  await Promise.all([loadExams(), loadClasses()]);
  if (form.value.class_id) await loadSections(form.value.class_id);
  if (form.value.exam_id && form.value.class_id) await loadSubjectsForContext();
});
</script>
