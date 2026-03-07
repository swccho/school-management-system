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
        :aria-labelledby="isEdit ? 'edit-class-title' : 'add-class-title'"
      >
        <h2
          :id="isEdit ? 'edit-class-title' : 'add-class-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit class' : 'Add class' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="class-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</label>
            <input
              id="class-name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="class-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</label>
            <input
              id="class-code"
              v-model="form.code"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="class-numeric-level" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Numeric level</label>
            <input
              id="class-numeric-level"
              v-model.number="form.numeric_level"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Optional. Used for sorting (e.g. 1, 2, 3…). Leave empty for Play, Nursery, KG.</p>
          </div>
          <div>
            <label for="class-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="class-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div>
            <label for="class-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="class-description"
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
import { createClass, updateClass } from '../services/classService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  classItem: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.classItem);

const form = reactive({
  name: '',
  code: '',
  numeric_level: null,
  status: 'active',
  description: '',
});

const saving = ref(false);
const formError = ref(null);

function resetForm() {
  form.name = '';
  form.code = '';
  form.numeric_level = null;
  form.status = 'active';
  form.description = '';
  formError.value = null;
}

function assignClass(c) {
  if (!c) {
    resetForm();
    return;
  }
  form.name = c.name ?? '';
  form.code = c.code ?? '';
  form.numeric_level = c.numeric_level ?? null;
  form.status = c.status ?? 'active';
  form.description = c.description ?? '';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.classItem],
  () => {
    if (props.modelValue) {
      assignClass(props.classItem);
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
    numeric_level: form.numeric_level === '' || form.numeric_level == null ? null : Number(form.numeric_level),
    status: form.status,
    description: form.description || null,
  };
  try {
    if (isEdit.value) {
      await updateClass(props.classItem.id, payload);
    } else {
      await createClass(payload);
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
