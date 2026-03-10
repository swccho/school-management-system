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
        :aria-labelledby="isEdit ? 'edit-notice-title' : 'add-notice-title'"
      >
        <h2
          :id="isEdit ? 'edit-notice-title' : 'add-notice-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit notice' : 'Add notice' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="notice-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="notice-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <SearchableSelect
              id="notice-category"
              v-model="form.category_id"
              label="Category"
              :options="categories"
              label-key="name"
              value-key="id"
              placeholder="Select category"
              search-placeholder="Search categories…"
              required
            />
          </div>

          <div>
            <label for="notice-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Content <span class="text-red-500">*</span></label>
            <textarea
              id="notice-content"
              v-model="form.content"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <DatePicker
                id="notice-publish-date"
                v-model="form.publish_date"
                label="Publish date"
                placeholder="Select date"
                required
              />
            </div>
            <div>
              <DatePicker
                id="notice-expiry-date"
                v-model="form.expiry_date"
                label="Expiry date"
                placeholder="Optional"
                clearable
              />
            </div>
          </div>

          <div>
            <label for="notice-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="notice-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input
              id="notice-featured"
              v-model="form.is_featured"
              type="checkbox"
              class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
            />
            <label for="notice-featured" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured</label>
          </div>

          <div v-if="isEdit && existingAttachments.length > 0" class="space-y-2">
            <span class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Existing attachments</span>
            <ul class="space-y-1 rounded-lg border border-zinc-200 bg-zinc-50 p-2 dark:border-zinc-700 dark:bg-zinc-800/50">
              <li
                v-for="att in existingAttachments"
                :key="att.id"
                class="flex items-center justify-between gap-2 text-sm"
              >
                <a
                  :href="att.url"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="truncate text-zinc-700 hover:underline dark:text-zinc-300"
                >
                  {{ att.file_name }}
                </a>
                <button
                  type="button"
                  class="shrink-0 text-red-600 hover:text-red-700 dark:text-red-400"
                  aria-label="Remove attachment"
                  @click="removeExistingAttachment(att.id)"
                >
                  Remove
                </button>
              </li>
            </ul>
          </div>

          <div>
            <label for="notice-attachments" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachments</label>
            <input
              id="notice-attachments"
              type="file"
              multiple
              accept=".pdf,.doc,.docx,image/jpeg,image/png,image/gif"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onFileChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">PDF, DOC, DOCX, JPEG, PNG, GIF. Max 5MB each.</p>
            <p v-if="newFileNames.length" class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
              New files: {{ newFileNames.join(', ') }}
            </p>
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
import { createNotice, updateNotice, getNotice } from '../services/noticeService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  notice: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.notice?.id);

const form = reactive({
  title: '',
  content: '',
  category_id: null,
  publish_date: '',
  expiry_date: '',
  status: 'draft',
  is_featured: false,
});

const saving = ref(false);
const formError = ref(null);
const newFiles = ref([]);
const removeAttachmentIds = ref([]);
const loadedAttachments = ref([]);

const existingAttachments = computed(() => {
  return loadedAttachments.value.filter((a) => !removeAttachmentIds.value.includes(a.id));
});

const newFileNames = computed(() => newFiles.value.map((f) => f.name));

function resetForm() {
  form.title = '';
  form.content = '';
  form.category_id = null;
  form.publish_date = '';
  form.expiry_date = '';
  form.status = 'draft';
  form.is_featured = false;
  formError.value = null;
  newFiles.value = [];
  removeAttachmentIds.value = [];
  loadedAttachments.value = [];
}

function assignNotice(n) {
  if (!n) {
    resetForm();
    return;
  }
  form.title = n.title ?? '';
  form.content = n.content ?? '';
  form.category_id = n.category_id ?? null;
  form.publish_date = n.publish_date ?? '';
  form.expiry_date = n.expiry_date ?? '';
  form.status = n.status ?? 'draft';
  form.is_featured = !!n.is_featured;
  formError.value = null;
  newFiles.value = [];
  removeAttachmentIds.value = [];
  loadedAttachments.value = n.attachments ?? [];
}

function removeExistingAttachment(id) {
  removeAttachmentIds.value = [...removeAttachmentIds.value, id];
}

function onFileChange(e) {
  const files = e.target.files;
  newFiles.value = files ? Array.from(files) : [];
}

watch(
  () => [props.modelValue, props.notice],
  async () => {
    if (props.modelValue) {
      if (props.notice?.id) {
        try {
          const full = await getNotice(props.notice.id);
          assignNotice(full);
        } catch {
          assignNotice(props.notice);
        }
      } else {
        assignNotice(null);
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
    content: form.content.trim(),
    category_id: form.category_id,
    publish_date: form.publish_date || null,
    expiry_date: form.expiry_date || null,
    status: form.status,
    is_featured: form.is_featured,
  };
  if (newFiles.value.length) payload.attachments = newFiles.value;
  if (isEdit.value && removeAttachmentIds.value.length) payload.remove_attachment_ids = removeAttachmentIds.value;

  try {
    if (isEdit.value) {
      await updateNotice(props.notice.id, payload);
    } else {
      await createNotice(payload);
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
