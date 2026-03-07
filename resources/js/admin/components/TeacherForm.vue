<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-teacher-title' : 'add-teacher-title'"
      >
        <h2
          :id="isEdit ? 'edit-teacher-title' : 'add-teacher-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit teacher' : 'Add teacher' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div v-if="!isEdit">
            <SearchableSelect
              id="teacher-staff"
              v-model="form.staff_id"
              label="Staff"
              :options="staffOptions"
              label-key="label"
              value-key="id"
              placeholder="Select staff…"
              search-placeholder="Search staff…"
              required
              :loading="staffsLoading"
              loading-text="Loading staff…"
            />
            <p v-if="staffsForTeacher.length === 0 && !staffsLoading" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
              No staff available. Add staff first, then add them as teachers.
            </p>
          </div>
          <div v-else class="rounded-lg bg-zinc-100 px-3 py-2 text-sm text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
            {{ editingStaffName }}
          </div>

          <div>
            <label for="teacher-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Teacher code</label>
            <input
              id="teacher-code"
              v-model="form.teacher_code"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="teacher-qualification" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Qualification</label>
            <input
              id="teacher-qualification"
              v-model="form.qualification"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="teacher-specialization" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Specialization</label>
            <input
              id="teacher-specialization"
              v-model="form.specialization"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="teacher-experience" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Experience (years)</label>
            <input
              id="teacher-experience"
              v-model.number="form.experience_years"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_class_teacher"
                type="checkbox"
                class="rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
              />
              <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Class teacher</span>
            </label>
          </div>
          <div>
            <label for="teacher-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="teacher-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
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
              :disabled="saving || (!isEdit && staffsForTeacher.length === 0)"
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
import { getStaffsForTeacher } from '../services/staffService.js';
import { createTeacher, updateTeacher } from '../services/teacherService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  teacher: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.teacher);

const form = reactive({
  staff_id: '',
  teacher_code: '',
  qualification: '',
  specialization: '',
  experience_years: null,
  is_class_teacher: false,
  status: 'active',
});

const saving = ref(false);
const formError = ref(null);
const staffsForTeacher = ref([]);
const staffsLoading = ref(false);

const editingStaffName = computed(() => {
  if (!props.teacher?.full_name && !props.teacher?.employee_id) return '—';
  return `${props.teacher.employee_id ?? ''} — ${props.teacher.full_name ?? ''}`.trim();
});

const staffOptions = computed(() =>
  staffsForTeacher.value.map((s) => ({
    ...s,
    label: `${s.employee_id ?? ''} — ${s.full_name ?? ''}`.trim(),
  }))
);

async function loadStaffs() {
  staffsLoading.value = true;
  try {
    staffsForTeacher.value = await getStaffsForTeacher();
  } catch {
    staffsForTeacher.value = [];
  } finally {
    staffsLoading.value = false;
  }
}

function resetForm() {
  form.staff_id = '';
  form.teacher_code = '';
  form.qualification = '';
  form.specialization = '';
  form.experience_years = null;
  form.is_class_teacher = false;
  form.status = 'active';
  formError.value = null;
}

function assign(t) {
  if (!t) {
    resetForm();
    return;
  }
  form.teacher_code = t.teacher_code ?? '';
  form.qualification = t.qualification ?? '';
  form.specialization = t.specialization ?? '';
  form.experience_years = t.experience_years ?? null;
  form.is_class_teacher = !!t.is_class_teacher;
  form.status = t.status ?? 'active';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.teacher],
  async () => {
    if (props.modelValue) {
      if (!isEdit.value) await loadStaffs();
      assign(props.teacher);
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    teacher_code: form.teacher_code || null,
    qualification: form.qualification || null,
    specialization: form.specialization || null,
    experience_years: form.experience_years === '' || form.experience_years == null ? null : Number(form.experience_years),
    is_class_teacher: form.is_class_teacher,
    status: form.status,
  };
  try {
    if (isEdit.value) {
      await updateTeacher(props.teacher.id, payload);
    } else {
      await createTeacher({ staff_id: Number(form.staff_id), ...payload });
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
