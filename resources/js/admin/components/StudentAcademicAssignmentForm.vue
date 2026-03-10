<template>
  <form class="space-y-4" @submit.prevent="handleSubmit">
    <p
      v-if="formError"
      class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ formError }}
    </p>

    <div>
      <SearchableSelect
        id="saa-session"
        v-model="form.academic_session_id"
        label="Academic session"
        :options="sessionOptions"
        label-key="name"
        value-key="id"
        placeholder="Select session…"
        search-placeholder="Search sessions…"
        required
        clearable
      />
    </div>
    <div>
      <SearchableSelect
        id="saa-class"
        v-model="form.class_id"
        label="Class"
        :options="classOptions"
        label-key="name"
        value-key="id"
        placeholder="Select class…"
        search-placeholder="Search classes…"
        required
        clearable
      />
    </div>
    <div>
      <SearchableSelect
        id="saa-section"
        v-model="form.section_id"
        label="Section"
        :options="sectionOptions"
        label-key="name"
        value-key="id"
        placeholder="Select section…"
        search-placeholder="Search sections…"
        required
        clearable
      />
      <p
        v-if="form.class_id && !sectionOptions.length && !sectionsLoading"
        class="mt-1 text-xs text-zinc-500 dark:text-zinc-400"
      >
        No sections for this class.
      </p>
    </div>
    <div>
      <label for="saa-roll_no" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
        Roll number
      </label>
      <input
        id="saa-roll_no"
        v-model="form.roll_no"
        type="text"
        maxlength="50"
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="Optional"
      />
    </div>
    <div>
      <label for="saa-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
        Status
      </label>
      <select
        id="saa-status"
        v-model="form.status"
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
      >
        <option value="active">Active</option>
        <option value="inactive">Inactive</option>
      </select>
    </div>
    <div>
      <label for="saa-remarks" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
        Remarks
      </label>
      <textarea
        id="saa-remarks"
        v-model="form.remarks"
        rows="2"
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="Optional"
      />
    </div>

    <div class="flex justify-end gap-2 pt-2">
      <button
        v-if="isEdit"
        type="button"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
        @click="emit('cancel')"
      >
        Cancel
      </button>
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        :disabled="saving"
      >
        <span v-if="saving">{{ isEdit ? 'Saving…' : 'Creating…' }}</span>
        <span v-else>{{ isEdit ? 'Save' : 'Assign' }}</span>
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getSessions } from '../services/academicSessionService.js';
import { getClasses } from '../services/classService.js';
import { getSections } from '../services/sectionService.js';
import { createAssignment, updateAssignment } from '../services/studentAcademicAssignmentService.js';

const props = defineProps({
  studentId: { type: [Number, String], required: true },
  assignment: { type: Object, default: null },
});

const emit = defineEmits(['saved', 'cancel']);

const isEdit = computed(() => !!props.assignment);

const form = reactive({
  academic_session_id: null,
  class_id: null,
  section_id: null,
  roll_no: '',
  status: 'active',
  remarks: '',
});

const saving = ref(false);
const formError = ref(null);
const sessionOptions = ref([]);
const classOptions = ref([]);
const sectionOptions = ref([]);
const sectionsLoading = ref(false);

async function loadSessionAndClassOptions() {
  try {
    [sessionOptions.value, classOptions.value] = await Promise.all([
      getSessions(),
      getClasses(),
    ]);
  } catch {
    sessionOptions.value = [];
    classOptions.value = [];
  }
}

async function loadSectionsForClass(classId) {
  if (!classId) {
    sectionOptions.value = [];
    return;
  }
  sectionsLoading.value = true;
  try {
    sectionOptions.value = await getSections({ class_id: classId });
  } catch {
    sectionOptions.value = [];
  } finally {
    sectionsLoading.value = false;
  }
}

function resetForm() {
  form.academic_session_id = null;
  form.class_id = null;
  form.section_id = null;
  form.roll_no = '';
  form.status = 'active';
  form.remarks = '';
  formError.value = null;
  sectionOptions.value = [];
}

function assignFromAssignment(a) {
  if (!a) {
    resetForm();
    return;
  }
  form.academic_session_id = a.academic_session_id ?? null;
  form.class_id = a.class_id ?? null;
  form.section_id = a.section_id ?? null;
  form.roll_no = a.roll_no ?? '';
  form.status = a.status ?? 'active';
  form.remarks = a.remarks ?? '';
  formError.value = null;
  if (a.class_id) {
    loadSectionsForClass(a.class_id);
  } else {
    sectionOptions.value = [];
  }
}

watch(
  () => form.class_id,
  (newClassId, oldClassId) => {
    if (newClassId !== oldClassId) {
      form.section_id = null;
      loadSectionsForClass(newClassId);
    }
  }
);

watch(
  () => [props.assignment],
  async () => {
    await loadSessionAndClassOptions();
    if (props.assignment) {
      assignFromAssignment(props.assignment);
    } else {
      resetForm();
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    academic_session_id: form.academic_session_id ? Number(form.academic_session_id) : null,
    class_id: form.class_id ? Number(form.class_id) : null,
    section_id: form.section_id ? Number(form.section_id) : null,
    roll_no: form.roll_no.trim() || null,
    status: form.status,
    remarks: form.remarks.trim() || null,
  };
  if (!isEdit.value) {
    payload.student_id = Number(props.studentId);
  }
  try {
    if (isEdit.value) {
      await updateAssignment(props.assignment.id, payload);
    } else {
      await createAssignment(payload);
    }
    emit('saved');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
  } finally {
    saving.value = false;
  }
}
</script>
