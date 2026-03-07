<template>
  <div class="flex flex-wrap items-end gap-4">
    <div class="min-w-[180px]">
      <SearchableSelect
        id="attendance-session"
        v-model="form.academic_session_id"
        label="Academic session"
        :options="sessionOptions"
        label-key="name"
        value-key="id"
        placeholder="Select session…"
        search-placeholder="Search sessions…"
        clearable
        @update:model-value="onSessionChange"
      />
    </div>
    <div class="min-w-[180px]">
      <SearchableSelect
        id="attendance-class"
        v-model="form.class_id"
        label="Class"
        :options="classOptions"
        label-key="name"
        value-key="id"
        placeholder="Select class…"
        search-placeholder="Search classes…"
        clearable
        @update:model-value="onClassChange"
      />
    </div>
    <div class="min-w-[180px]">
      <SearchableSelect
        id="attendance-section"
        v-model="form.section_id"
        label="Section"
        :options="sectionOptions"
        label-key="name"
        value-key="id"
        placeholder="Select section…"
        search-placeholder="Search sections…"
        :disabled="!form.class_id"
        clearable
      />
    </div>
    <div v-if="showDate" class="min-w-[160px]">
      <DatePicker
        id="attendance-date"
        v-model="form.attendance_date"
        label="Date"
        clearable
        @change="emitChange"
      />
    </div>
  </div>
</template>

<script setup>
import { watch, ref, onMounted } from 'vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getAcademicSessions, getClasses, getSections } from '../services/attendanceService.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  showDate: { type: Boolean, default: true },
});

const emit = defineEmits(['update:modelValue', 'change']);

const form = ref({
  academic_session_id: props.modelValue.academic_session_id ?? null,
  class_id: props.modelValue.class_id ?? null,
  section_id: props.modelValue.section_id ?? null,
  attendance_date: props.modelValue.attendance_date ?? '',
});

const sessionOptions = ref([]);
const classOptions = ref([]);
const sectionOptions = ref([]);

async function loadSessions() {
  try {
    sessionOptions.value = await getAcademicSessions();
  } catch {
    sessionOptions.value = [];
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

function onSessionChange() {
  emitChange();
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
    form.value.academic_session_id = v.academic_session_id ?? null;
    form.value.class_id = v.class_id ?? null;
    form.value.section_id = v.section_id ?? null;
    form.value.attendance_date = v.attendance_date ?? '';
    if (form.value.class_id) loadSections(form.value.class_id);
  }
}, { deep: true });

onMounted(async () => {
  await Promise.all([loadSessions(), loadClasses()]);
  if (form.value.class_id) {
    await loadSections(form.value.class_id);
  }
});
</script>
