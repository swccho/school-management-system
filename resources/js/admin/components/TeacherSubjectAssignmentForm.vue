<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative w-full max-w-lg rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[90vh] overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-assignment-title' : 'add-assignment-title'"
      >
        <h2
          :id="isEdit ? 'edit-assignment-title' : 'add-assignment-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit assignment' : 'Assign teacher' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <SearchableSelect
              id="assignment-session"
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
              id="assignment-teacher"
              v-model="form.teacher_id"
              label="Teacher"
              :options="teacherOptions"
              label-key="full_name"
              value-key="id"
              placeholder="Select teacher…"
              search-placeholder="Search teachers…"
              required
              clearable
            />
          </div>
          <div>
            <SearchableSelect
              id="assignment-class"
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
              id="assignment-section"
              v-model="form.section_id"
              label="Section (optional)"
              :options="sectionOptions"
              label-key="name"
              value-key="id"
              placeholder="Select section or leave blank…"
              search-placeholder="Search sections…"
              clearable
            />
            <p v-if="form.class_id && !sectionOptions.length && !sectionsLoading" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
              No sections for this class. Leave blank to assign for the whole class.
            </p>
          </div>
          <div>
            <SearchableSelect
              id="assignment-subject"
              v-model="form.subject_id"
              label="Subject"
              :options="subjectOptions"
              label-key="name"
              value-key="id"
              placeholder="Select subject…"
              search-placeholder="Search subjects…"
              required
              clearable
            />
          </div>
          <div>
            <label for="assignment-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="assignment-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div>
            <label for="assignment-remarks" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Remarks</label>
            <textarea
              id="assignment-remarks"
              v-model="form.remarks"
              rows="2"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              @click="emit('close')"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="saving"
            >
              <span v-if="saving">{{ isEdit ? 'Saving…' : 'Creating…' }}</span>
              <span v-else>{{ isEdit ? 'Save' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import {
  createAssignment,
  updateAssignment,
  getAcademicSessions,
  getTeachers,
  getClasses,
  getSections,
  getSubjects,
} from '../services/teacherSubjectAssignmentService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  assignment: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.assignment);

const form = reactive({
  academic_session_id: null,
  teacher_id: null,
  class_id: null,
  section_id: null,
  subject_id: null,
  status: 'active',
  remarks: '',
});

const saving = ref(false);
const formError = ref(null);
const sessionOptions = ref([]);
const teacherOptions = ref([]);
const classOptions = ref([]);
const sectionOptions = ref([]);
const subjectOptions = ref([]);
const sectionsLoading = ref(false);

async function loadOptions() {
  try {
    [sessionOptions.value, teacherOptions.value, classOptions.value, subjectOptions.value] = await Promise.all([
      getAcademicSessions(),
      getTeachers(),
      getClasses(),
      getSubjects(),
    ]);
  } catch {
    sessionOptions.value = [];
    teacherOptions.value = [];
    classOptions.value = [];
    subjectOptions.value = [];
  }
}

async function loadSectionsForClass(classId) {
  if (!classId) {
    sectionOptions.value = [];
    return;
  }
  sectionsLoading.value = true;
  try {
    sectionOptions.value = await getSections(classId);
  } catch {
    sectionOptions.value = [];
  } finally {
    sectionsLoading.value = false;
  }
}

function resetForm() {
  form.academic_session_id = null;
  form.teacher_id = null;
  form.class_id = null;
  form.section_id = null;
  form.subject_id = null;
  form.status = 'active';
  form.remarks = '';
  formError.value = null;
  sectionOptions.value = [];
}

function assign(a) {
  if (!a) {
    resetForm();
    return;
  }
  form.academic_session_id = a.academic_session_id ?? null;
  form.teacher_id = a.teacher_id ?? null;
  form.class_id = a.class_id ?? null;
  form.section_id = a.section_id ?? null;
  form.subject_id = a.subject_id ?? null;
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
  () => [props.modelValue, props.assignment],
  async () => {
    if (props.modelValue) {
      await loadOptions();
      assign(props.assignment);
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    academic_session_id: form.academic_session_id ? Number(form.academic_session_id) : null,
    teacher_id: form.teacher_id ? Number(form.teacher_id) : null,
    class_id: form.class_id ? Number(form.class_id) : null,
    section_id: form.section_id ? Number(form.section_id) : null,
    subject_id: form.subject_id ? Number(form.subject_id) : null,
    status: form.status,
    remarks: form.remarks || null,
  };
  try {
    if (isEdit.value) {
      await updateAssignment(props.assignment.id, payload);
    } else {
      await createAssignment(payload);
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
  } finally {
    saving.value = false;
  }
}
</script>
