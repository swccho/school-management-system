<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900 max-h-[90vh] overflow-y-auto"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-subject-title' : 'add-subject-title'"
      >
        <h2
          :id="isEdit ? 'edit-subject-title' : 'add-subject-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit subject' : 'Add subject' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="subject-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
            <input
              id="subject-name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="subject-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</label>
              <input
                id="subject-code"
                v-model="form.code"
                type="text"
                placeholder="e.g. MATH"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="subject-short-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Short name</label>
              <input
                id="subject-short-name"
                v-model="form.short_name"
                type="text"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>
          <div>
            <label for="subject-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
            <select
              id="subject-type"
              v-model="form.type"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="">—</option>
              <option value="general">General</option>
              <option value="elective">Elective</option>
              <option value="practical">Practical</option>
            </select>
          </div>
          <div class="flex gap-6">
            <label class="flex items-center gap-2">
              <input
                v-model="form.is_optional"
                type="checkbox"
                class="rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
              />
              <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Optional subject</span>
            </label>
            <label class="flex items-center gap-2">
              <input
                v-model="form.has_practical"
                type="checkbox"
                class="rounded border-zinc-300 dark:border-zinc-600 dark:bg-zinc-800"
              />
              <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Has practical</span>
            </label>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="subject-full-marks" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Full marks</label>
              <input
                id="subject-full-marks"
                v-model.number="form.full_marks"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="subject-pass-marks" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pass marks</label>
              <input
                id="subject-pass-marks"
                v-model.number="form.pass_marks"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>
          <div>
            <label for="subject-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="subject-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div>
            <label for="subject-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="subject-description"
              v-model="form.description"
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
import { createSubject, updateSubject } from '../services/subjectService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  subject: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.subject);

const form = reactive({
  name: '',
  code: '',
  short_name: '',
  type: '',
  is_optional: false,
  has_practical: false,
  full_marks: null,
  pass_marks: null,
  status: 'active',
  description: '',
});

const saving = ref(false);
const formError = ref(null);

function resetForm() {
  form.name = '';
  form.code = '';
  form.short_name = '';
  form.type = '';
  form.is_optional = false;
  form.has_practical = false;
  form.full_marks = null;
  form.pass_marks = null;
  form.status = 'active';
  form.description = '';
  formError.value = null;
}

function assignSubject(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.name = s.name ?? '';
  form.code = s.code ?? '';
  form.short_name = s.short_name ?? '';
  form.type = s.type ?? '';
  form.is_optional = !!s.is_optional;
  form.has_practical = !!s.has_practical;
  form.full_marks = s.full_marks ?? null;
  form.pass_marks = s.pass_marks ?? null;
  form.status = s.status ?? 'active';
  form.description = s.description ?? '';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.subject],
  () => {
    if (props.modelValue) {
      assignSubject(props.subject);
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    name: form.name,
    code: form.code || null,
    short_name: form.short_name || null,
    type: form.type || null,
    is_optional: form.is_optional,
    has_practical: form.has_practical,
    full_marks: form.full_marks === '' || form.full_marks == null ? null : Number(form.full_marks),
    pass_marks: form.pass_marks === '' || form.pass_marks == null ? null : Number(form.pass_marks),
    status: form.status,
    description: form.description || null,
  };
  try {
    if (isEdit.value) {
      await updateSubject(props.subject.id, payload);
    } else {
      await createSubject(payload);
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
