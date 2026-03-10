<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-download-title' : 'add-download-title'"
      >
        <h2
          :id="isEdit ? 'edit-download-title' : 'add-download-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit download' : 'Add download' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="download-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="download-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <SearchableSelect
              id="download-category"
              v-model="form.category_id"
              label="Category"
              :options="categories"
              label-key="name"
              value-key="id"
              placeholder="Select category (optional)"
              search-placeholder="Search categories…"
              clearable
            />
          </div>

          <div>
            <label for="download-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="download-description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="download-file" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">
              File <span v-if="!isEdit" class="text-red-500">*</span>
            </label>
            <input
              id="download-file"
              type="file"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onFileChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Max 50MB. Leave empty on edit to keep current file.</p>
            <p v-if="isEdit && existingFileName" class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
              Current file: {{ existingFileName }}
            </p>
          </div>

          <div>
            <label for="download-access" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Access type</label>
            <select
              id="download-access"
              v-model="form.access_type"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="public">Public</option>
              <option value="registered">Registered</option>
              <option value="private">Private</option>
            </select>
          </div>

          <div>
            <DatePicker
              id="download-published-at"
              v-model="form.published_at"
              label="Published at"
              placeholder="Optional"
              clearable
            />
          </div>

          <div>
            <label for="download-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="download-status"
              v-model="form.status"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
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
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { create, update, getOne } from '../services/downloadService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  download: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.download?.id);

const form = reactive({
  title: '',
  description: '',
  category_id: null,
  access_type: 'public',
  published_at: '',
  status: 'draft',
});

const saving = ref(false);
const formError = ref(null);
const file = ref(null);
const existingFileName = ref(null);

function resetForm() {
  form.title = '';
  form.description = '';
  form.category_id = null;
  form.access_type = 'public';
  form.published_at = '';
  form.status = 'draft';
  formError.value = null;
  file.value = null;
  existingFileName.value = null;
}

function assignDownload(d) {
  if (!d) {
    resetForm();
    return;
  }
  form.title = d.title ?? '';
  form.description = d.description ?? '';
  form.category_id = d.category_id ?? null;
  form.access_type = d.access_type ?? 'public';
  form.published_at = d.published_at ? d.published_at.slice(0, 10) : '';
  form.status = d.status ?? 'draft';
  formError.value = null;
  file.value = null;
  existingFileName.value = d.file_name ?? null;
}

function onFileChange(e) {
  const f = e.target.files?.[0];
  file.value = f ?? null;
}

watch(
  () => [props.modelValue, props.download],
  async () => {
    if (props.modelValue) {
      if (props.download?.id) {
        try {
          const full = await getOne(props.download.id);
          assignDownload(full);
        } catch {
          assignDownload(props.download);
        }
      } else {
        assignDownload(null);
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    title: form.title.trim(),
    description: form.description?.trim() || null,
    category_id: form.category_id || null,
    access_type: form.access_type,
    published_at: form.published_at || null,
    status: form.status,
  };
  if (file.value) payload.file = file.value;

  try {
    if (isEdit.value) {
      await update(props.download.id, payload);
    } else {
      if (!payload.file) {
        formError.value = 'File is required for new downloads.';
        toast.error(formError.value);
        saving.value = false;
        return;
      }
      await create(payload);
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
    toast.error(formError.value);
  } finally {
    saving.value = false;
  }
}
</script>
