<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <p
      v-if="formError"
      class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ formError }}
    </p>

    <div class="grid gap-6 sm:grid-cols-2">
      <div>
        <label for="guide-category" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Category <span class="text-red-500">*</span></label>
        <SearchableSelect
          id="guide-category"
          v-model="form.category_id"
          :options="categories"
          label-key="name"
          value-key="id"
          placeholder="Select category"
          search-placeholder="Search categories…"
          required
        />
      </div>
      <div>
        <label for="guide-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="guide-status"
          v-model="form.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="draft">Draft</option>
          <option value="published">Published</option>
        </select>
      </div>
    </div>

    <div>
      <label for="guide-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
      <input
        id="guide-title"
        v-model="form.title"
        type="text"
        required
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        @input="maybeSlugFromTitle"
      />
    </div>

    <div>
      <label for="guide-slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug <span class="text-red-500">*</span></label>
      <input
        id="guide-slug"
        v-model="form.slug"
        type="text"
        required
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="e.g. how-academic-sessions-work"
      />
      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Lowercase letters, numbers, hyphens only. Used in Help Center URL.</p>
    </div>

    <div>
      <label for="guide-short-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Short description</label>
      <textarea
        id="guide-short-description"
        v-model="form.short_description"
        rows="2"
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        placeholder="Brief summary for search and listings"
      />
    </div>

    <div>
      <div class="flex items-center justify-between gap-2">
        <label for="guide-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Content <span class="text-red-500">*</span></label>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-2.5 py-1.5 text-xs font-medium text-zinc-600 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700"
          @click="showPreview = !showPreview"
        >
          {{ showPreview ? 'Hide preview' : 'Show preview' }}
        </button>
      </div>
      <textarea
        id="guide-content"
        v-model="form.content"
        rows="16"
        required
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 font-mono text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        :class="{ 'sm:rounded-b-none': showPreview }"
        placeholder="Write in Markdown: # Heading, **bold**, - list, ```code```"
      />
      <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Markdown supported. Use # for headings, ** for bold, - for lists, ``` for code blocks.</p>
      <div
        v-show="showPreview"
        class="guide-preview mt-0 rounded-b-lg border border-t-0 border-zinc-300 bg-zinc-50 p-4 dark:border-zinc-600 dark:bg-zinc-800/50"
      >
        <p class="mb-2 text-xs font-medium text-zinc-500 dark:text-zinc-400">Preview</p>
        <div
          class="guide-content prose prose-zinc max-h-80 overflow-y-auto text-sm dark:prose-invert"
          v-html="previewHtml"
        />
      </div>
    </div>

    <div class="max-w-xs">
      <label for="guide-sort-order" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</label>
      <input
        id="guide-sort-order"
        v-model.number="form.sort_order"
        type="number"
        min="0"
        class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
      />
    </div>

    <div class="flex flex-wrap gap-2">
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        :disabled="saving"
      >
        <span v-if="saving">{{ isEdit ? 'Saving…' : 'Creating…' }}</span>
        <span v-else>{{ isEdit ? 'Save' : 'Create guide' }}</span>
      </button>
      <router-link
        :to="{ name: 'admin-guide-guides' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Cancel
      </router-link>
    </div>
  </form>
</template>

<script setup>
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { marked } from 'marked';
import { useToast } from '../../shared/composables/useToast.js';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getCategories } from '../services/adminGuideCategoryService.js';
import { getGuide, createGuide, updateGuide } from '../services/adminGuideService.js';

const toast = useToast();
const showPreview = ref(false);

const props = defineProps({
  guideId: { type: [Number, String], default: null },
});

const emit = defineEmits(['saved']);

const isEdit = computed(() => props.guideId != null && props.guideId !== '');

const categories = ref([]);
const form = reactive({
  category_id: null,
  title: '',
  slug: '',
  short_description: '',
  content: '',
  status: 'draft',
  sort_order: 0,
});

const saving = ref(false);
const formError = ref(null);

const previewHtml = computed(() => {
  const raw = form.content?.trim() || '';
  if (!raw) return '<p class="text-zinc-500 dark:text-zinc-400">Nothing to preview yet.</p>';
  try {
    return marked(raw, { gfm: true });
  } catch {
    return raw;
  }
});

function slugify(s) {
  return String(s)
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');
}

function maybeSlugFromTitle() {
  if (!isEdit.value) form.slug = slugify(form.title);
}

function resetForm() {
  form.category_id = null;
  form.title = '';
  form.slug = '';
  form.short_description = '';
  form.content = '';
  form.status = 'draft';
  form.sort_order = 0;
  formError.value = null;
}

function assignGuide(g) {
  if (!g) {
    resetForm();
    return;
  }
  form.category_id = g.category_id ?? g.category?.id ?? null;
  form.title = g.title ?? '';
  form.slug = g.slug ?? '';
  form.short_description = g.short_description ?? '';
  form.content = g.content ?? '';
  form.status = g.status ?? 'draft';
  form.sort_order = g.sort_order ?? 0;
  formError.value = null;
}

onMounted(async () => {
  try {
    categories.value = await getCategories();
  } catch {
    categories.value = [];
  }
  if (isEdit.value) {
    try {
      const data = await getGuide(props.guideId);
      assignGuide(data.guide ?? data);
    } catch {
      formError.value = 'Failed to load guide.';
    }
  } else {
    resetForm();
  }
});

watch(
  () => props.guideId,
  async (id) => {
    if (id != null && id !== '') {
      try {
        const data = await getGuide(id);
        assignGuide(data.guide ?? data);
      } catch {
        formError.value = 'Failed to load guide.';
      }
    } else {
      resetForm();
    }
  }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    category_id: form.category_id,
    title: form.title.trim(),
    slug: form.slug.trim() || slugify(form.title),
    short_description: form.short_description?.trim() || null,
    content: form.content,
    status: form.status,
    sort_order: form.sort_order,
  };
  try {
    if (isEdit.value) {
      await updateGuide(props.guideId, payload);
    } else {
      await createGuide(payload);
    }
    emit('saved');
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

<style scoped>
.guide-content :deep(h1) { @apply mt-3 text-xl font-bold; }
.guide-content :deep(h2) { @apply mt-2 text-lg font-semibold; }
.guide-content :deep(h3) { @apply mt-2 text-base font-semibold; }
.guide-content :deep(p) { @apply mt-2 leading-relaxed; }
.guide-content :deep(ul) { @apply mt-2 list-disc pl-6 space-y-1; }
.guide-content :deep(ol) { @apply mt-2 list-decimal pl-6 space-y-1; }
.guide-content :deep(blockquote) { @apply mt-2 border-l-4 border-zinc-300 pl-4 italic text-zinc-600 dark:border-zinc-600 dark:text-zinc-400; }
.guide-content :deep(pre) { @apply mt-2 overflow-x-auto rounded-lg bg-zinc-100 p-4 text-sm dark:bg-zinc-800; }
.guide-content :deep(code) { @apply rounded bg-zinc-100 px-1.5 py-0.5 font-mono text-sm dark:bg-zinc-800; }
.guide-content :deep(pre code) { @apply bg-transparent p-0; }
.guide-content :deep(table) { @apply mt-2 w-full border-collapse text-sm; }
.guide-content :deep(th), .guide-content :deep(td) { @apply border border-zinc-200 px-3 py-2 text-left dark:border-zinc-700; }
.guide-content :deep(th) { @apply bg-zinc-50 font-semibold dark:bg-zinc-800; }
.guide-content :deep(a) { @apply text-zinc-700 underline dark:text-zinc-300; }
</style>
