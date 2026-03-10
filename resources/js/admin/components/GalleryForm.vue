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
        :aria-labelledby="isEdit ? 'edit-gallery-title' : 'add-gallery-title'"
      >
        <h2
          :id="isEdit ? 'edit-gallery-title' : 'add-gallery-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit gallery' : 'Add gallery' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="gallery-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="gallery-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="gallery-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="gallery-description"
              v-model="form.description"
              rows="3"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="gallery-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
            <input
              id="gallery-type"
              v-model="form.gallery_type"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="e.g. Events, Campus (optional)"
            />
          </div>

          <div>
            <label for="gallery-cover" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Cover image</label>
            <input
              id="gallery-cover"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onCoverChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">JPEG, PNG, GIF, WebP. Max 5MB.</p>
            <p v-if="existingCoverUrl" class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
              Current: <a :href="existingCoverUrl" target="_blank" rel="noopener noreferrer" class="underline">View</a>
            </p>
          </div>

          <div>
            <DatePicker
              id="gallery-published-at"
              v-model="form.published_at"
              label="Published at"
              placeholder="Optional"
              clearable
            />
          </div>

          <div>
            <label for="gallery-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="gallery-status"
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
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { create, update, getOne } from '../services/galleryService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  gallery: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.gallery?.id);

const form = reactive({
  title: '',
  description: '',
  gallery_type: '',
  published_at: '',
  status: 'draft',
});

const saving = ref(false);
const formError = ref(null);
const coverFile = ref(null);
const existingCoverUrl = ref(null);

function resetForm() {
  form.title = '';
  form.description = '';
  form.gallery_type = '';
  form.published_at = '';
  form.status = 'draft';
  formError.value = null;
  coverFile.value = null;
  existingCoverUrl.value = null;
}

function assignGallery(g) {
  if (!g) {
    resetForm();
    return;
  }
  form.title = g.title ?? '';
  form.description = g.description ?? '';
  form.gallery_type = g.gallery_type ?? '';
  form.published_at = g.published_at ? g.published_at.slice(0, 10) : '';
  form.status = g.status ?? 'draft';
  formError.value = null;
  coverFile.value = null;
  existingCoverUrl.value = g.cover_image_url ?? null;
}

function onCoverChange(e) {
  const file = e.target.files?.[0];
  coverFile.value = file ?? null;
}

watch(
  () => [props.modelValue, props.gallery],
  async () => {
    if (props.modelValue) {
      if (props.gallery?.id) {
        try {
          const full = await getOne(props.gallery.id);
          assignGallery(full);
        } catch {
          assignGallery(props.gallery);
        }
      } else {
        assignGallery(null);
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
    gallery_type: form.gallery_type?.trim() || null,
    published_at: form.published_at || null,
    status: form.status,
  };
  if (coverFile.value) payload.cover_image = coverFile.value;

  try {
    if (isEdit.value) {
      await update(props.gallery.id, payload);
    } else {
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
