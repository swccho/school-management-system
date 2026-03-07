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
        :aria-labelledby="isEdit ? 'edit-session-title' : 'add-session-title'"
      >
        <h2
          :id="isEdit ? 'edit-session-title' : 'add-session-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit session' : 'Add session' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="session-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
            <input
              id="session-name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="session-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</label>
            <div class="mt-1 flex gap-2">
              <input
                id="session-code"
                v-model="form.code"
                type="text"
                class="flex-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
              <button
                type="button"
                :disabled="generatingCode"
                class="shrink-0 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 disabled:opacity-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
                @click="handleGenerateCode"
              >
                {{ generatingCode ? 'Generating…' : 'Generate' }}
              </button>
            </div>
          </div>
          <div class="grid grid-cols-2 gap-4">
            <DatePicker
              id="session-start"
              v-model="form.start_date"
              label="Start date"
              required
            />
            <DatePicker
              id="session-end"
              v-model="form.end_date"
              label="End date"
              required
            />
          </div>
          <div>
            <label for="session-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="session-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div>
            <label for="session-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="session-description"
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
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { createSession, updateSession, generateCode } from '../services/academicSessionService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  session: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.session);

const form = reactive({
  name: '',
  code: '',
  start_date: '',
  end_date: '',
  status: 'active',
  description: '',
});

const saving = ref(false);
const generatingCode = ref(false);
const formError = ref(null);

function resetForm() {
  form.name = '';
  form.code = '';
  form.start_date = '';
  form.end_date = '';
  form.status = 'active';
  form.description = '';
  formError.value = null;
}

function assignSession(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.name = s.name ?? '';
  form.code = s.code ?? '';
  form.start_date = s.start_date ?? '';
  form.end_date = s.end_date ?? '';
  form.status = s.status ?? 'active';
  form.description = s.description ?? '';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.session],
  () => {
    if (props.modelValue) {
      assignSession(props.session);
    }
  },
  { immediate: true }
);

async function handleGenerateCode() {
  generatingCode.value = true;
  try {
    const payload = {
      name: form.name,
      start_date: form.start_date,
      end_date: form.end_date,
    };
    if (props.session?.id) payload.exclude_id = props.session.id;
    const result = await generateCode(payload);
    form.code = result?.data?.code ?? result?.code ?? '';
  } catch {
    // Silently ignore; user can enter code manually
  } finally {
    generatingCode.value = false;
  }
}

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  try {
    if (isEdit.value) {
      await updateSession(props.session.id, form);
    } else {
      await createSession(form);
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
