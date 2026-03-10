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
        :aria-labelledby="isEdit ? 'edit-page-title' : 'add-page-title'"
      >
        <h2
          :id="isEdit ? 'edit-page-title' : 'add-page-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit page' : 'Add page' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="page-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="page-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              @input="maybeSlugFromTitle"
            />
          </div>

          <div>
            <label for="page-slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug <span class="text-red-500">*</span></label>
            <input
              id="page-slug"
              v-model="form.slug"
              type="text"
              required
              pattern="[a-z0-9]+(?:-[a-z0-9]+)*"
              placeholder="e.g. about-us"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Lowercase letters, numbers, hyphens only.</p>
          </div>

          <div>
            <label for="page-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Page type</label>
            <input
              id="page-type"
              v-model="form.page_type"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="e.g. standard, landing (optional)"
            />
          </div>

          <div>
            <label for="page-meta-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Meta title</label>
            <input
              id="page-meta-title"
              v-model="form.meta_title"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="SEO title (optional)"
            />
          </div>

          <div>
            <label for="page-meta-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Meta description</label>
            <textarea
              id="page-meta-description"
              v-model="form.meta_description"
              rows="2"
              maxlength="500"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="SEO description (optional)"
            />
          </div>

          <div>
            <label for="page-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Content <span class="text-red-500">*</span></label>
            <textarea
              id="page-content"
              v-model="form.content"
              rows="6"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="page-featured-image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured image</label>
            <input
              id="page-featured-image"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onFeaturedImageChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">JPEG, PNG, GIF, WebP. Max 5MB.</p>
            <p v-if="existingImageUrl" class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
              Current: <a :href="existingImageUrl" target="_blank" rel="noopener noreferrer" class="underline">View</a>
            </p>
          </div>

          <div>
            <DatePicker
              id="page-published-at"
              v-model="form.published_at"
              label="Published at"
              placeholder="Optional"
              clearable
            />
          </div>

          <div>
            <label for="page-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="page-status"
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
import { create, update, getOne } from '../services/pageService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  page: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.page?.id);

const form = reactive({
  title: '',
  slug: '',
  page_type: '',
  meta_title: '',
  meta_description: '',
  content: '',
  published_at: '',
  status: 'draft',
});

const saving = ref(false);
const formError = ref(null);
const featuredImageFile = ref(null);
const existingImageUrl = ref(null);
const slugManuallyEdited = ref(false);

function slugify(text) {
  return text
    .toLowerCase()
    .trim()
    .replace(/[^\w\s-]/g, '')
    .replace(/[\s_-]+/g, '-')
    .replace(/^-+|-+$/g, '');
}

function maybeSlugFromTitle() {
  if (!slugManuallyEdited.value && !isEdit.value) {
    form.slug = slugify(form.title);
  }
}

function resetForm() {
  form.title = '';
  form.slug = '';
  form.page_type = '';
  form.meta_title = '';
  form.meta_description = '';
  form.content = '';
  form.published_at = '';
  form.status = 'draft';
  formError.value = null;
  featuredImageFile.value = null;
  existingImageUrl.value = null;
  slugManuallyEdited.value = false;
}

function assignPage(p) {
  if (!p) {
    resetForm();
    return;
  }
  form.title = p.title ?? '';
  form.slug = p.slug ?? '';
  form.page_type = p.page_type ?? '';
  form.meta_title = p.meta_title ?? '';
  form.meta_description = p.meta_description ?? '';
  form.content = p.content ?? '';
  form.published_at = p.published_at ? p.published_at.slice(0, 10) : '';
  form.status = p.status ?? 'draft';
  formError.value = null;
  featuredImageFile.value = null;
  existingImageUrl.value = p.featured_image_url ?? null;
  slugManuallyEdited.value = true;
}

function onFeaturedImageChange(e) {
  const file = e.target.files?.[0];
  featuredImageFile.value = file ?? null;
}

watch(
  () => [props.modelValue, props.page],
  async () => {
    if (props.modelValue) {
      if (props.page?.id) {
        try {
          const full = await getOne(props.page.id);
          assignPage(full);
        } catch {
          assignPage(props.page);
        }
      } else {
        assignPage(null);
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
    slug: form.slug.trim().toLowerCase().replace(/\s+/g, '-'),
    content: form.content.trim(),
    status: form.status,
    page_type: form.page_type?.trim() || null,
    meta_title: form.meta_title?.trim() || null,
    meta_description: form.meta_description?.trim() || null,
    published_at: form.published_at || null,
  };
  if (featuredImageFile.value) payload.featured_image = featuredImageFile.value;

  try {
    if (isEdit.value) {
      await update(props.page.id, payload);
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
