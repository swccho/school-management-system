<template>
  <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
    <p class="mb-4 text-sm font-medium text-zinc-700 dark:text-zinc-300">
      Routine context
    </p>
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <SearchableSelect
          id="routine-session"
          v-model="form.academic_session_id"
          label="Academic session"
          :options="sessionOptions"
          label-key="name"
          value-key="id"
          placeholder="Select session…"
          search-placeholder="Search sessions…"
          required
          clearable
          @update:model-value="emitUpdate"
        />
      </div>
      <div>
        <SearchableSelect
          id="routine-class"
          v-model="form.class_id"
          label="Class"
          :options="classOptions"
          label-key="name"
          value-key="id"
          placeholder="Select class…"
          search-placeholder="Search classes…"
          required
          clearable
          @update:model-value="onClassChange"
        />
      </div>
      <div>
        <SearchableSelect
          id="routine-section"
          v-model="form.section_id"
          label="Section"
          :options="sectionOptions"
          label-key="name"
          value-key="id"
          placeholder="Select section…"
          search-placeholder="Search sections…"
          :disabled="!form.class_id"
          required
          clearable
          @update:model-value="emitUpdate"
        />
      </div>
      <div>
        <label for="routine-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title (optional)</label>
        <input
          id="routine-title"
          v-model="form.title"
          type="text"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          placeholder="e.g. Regular Routine"
          @input="emitUpdate"
        />
      </div>
      <div>
        <DatePicker
          id="routine-effective-from"
          v-model="form.effective_from"
          label="Effective from"
          required
          @change="emitUpdate"
        />
      </div>
      <div>
        <DatePicker
          id="routine-effective-to"
          v-model="form.effective_to"
          label="Effective to (optional)"
          clearable
          @change="emitUpdate"
        />
      </div>
      <div>
        <label for="routine-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="routine-status"
          v-model="form.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="emitUpdate"
        >
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
          <option value="archived">Archived</option>
        </select>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getAcademicSessions, getClasses, getSections } from '../services/classRoutineService.js';

const props = defineProps({
  modelValue: { type: Object, default: () => ({}) },
  disabled: { type: Boolean, default: false },
});

const emit = defineEmits(['update:modelValue']);

const form = ref({
  academic_session_id: props.modelValue.academic_session_id ?? null,
  class_id: props.modelValue.class_id ?? null,
  section_id: props.modelValue.section_id ?? null,
  title: props.modelValue.title ?? '',
  effective_from: props.modelValue.effective_from ?? '',
  effective_to: props.modelValue.effective_to ?? '',
  status: props.modelValue.status ?? 'active',
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

function onClassChange() {
  form.value.section_id = null;
  loadSections(form.value.class_id);
  emitUpdate();
}

function emitUpdate() {
  emit('update:modelValue', { ...form.value });
}

watch(() => props.modelValue, (v) => {
  if (v) {
    form.value.academic_session_id = v.academic_session_id ?? null;
    form.value.class_id = v.class_id ?? null;
    form.value.section_id = v.section_id ?? null;
    form.value.title = v.title ?? '';
    form.value.effective_from = v.effective_from ?? '';
    form.value.effective_to = v.effective_to ?? '';
    form.value.status = v.status ?? 'active';
    if (form.value.class_id) loadSections(form.value.class_id);
  }
}, { deep: true });

onMounted(async () => {
  await Promise.all([loadSessions(), loadClasses()]);
  if (form.value.class_id) await loadSections(form.value.class_id);
});
</script>
