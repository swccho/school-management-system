<template>
  <PageContainer
    title="Pages"
    description="Manage dynamic CMS pages. Publish and edit content."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Page
      </button>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search pages…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[120px]">
        <label for="filter-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="filter-status"
          v-model="filters.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="draft">Draft</option>
          <option value="published">Published</option>
          <option value="archived">Archived</option>
        </select>
      </div>
      <div class="min-w-[120px]">
        <label for="filter-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</label>
        <input
          id="filter-type"
          v-model="filters.page_type"
          type="text"
          placeholder="Type"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-published-from"
          v-model="filters.published_from"
          label="Published from"
          placeholder="From"
          clearable
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-published-to"
          v-model="filters.published_to"
          label="Published to"
          placeholder="To"
          clearable
        />
      </div>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="applyFilters"
        >
          Apply Filters
        </button>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading pages…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="pages.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No pages match your filters.' : 'No pages yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Type</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Published at</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="page in pages"
              :key="page.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ page.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ page.slug }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ page.page_type ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ page.published_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="statusClass(page.status)"
                >
                  {{ page.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(page)"
                  >
                    Edit
                  </button>
                  <template v-if="page.status === 'draft'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="publishPage(page)"
                    >
                      Publish
                    </button>
                  </template>
                  <template v-if="page.status === 'published'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="unpublishPage(page)"
                    >
                      Unpublish
                    </button>
                  </template>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(page)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <PageForm
      v-model="modalOpen"
      :page="editingPage"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import PageForm from '../components/PageForm.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getList,
  deletePage,
  publish as apiPublish,
  unpublish as apiUnpublish,
} from '../services/pageService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

function initialFilters() {
  return {
    search: '',
    status: '',
    page_type: '',
    published_from: '',
    published_to: '',
  };
}

const loading = ref(true);
const error = ref(null);
const pages = ref([]);
const modalOpen = ref(false);
const editingPage = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(
    f.search?.trim() ||
    f.status ||
    f.page_type?.trim() ||
    f.published_from ||
    f.published_to
  );
});

function statusClass(status) {
  switch (status) {
    case 'published':
      return 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300';
    case 'archived':
      return 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300';
    default:
      return 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300';
  }
}

function openCreateModal() {
  editingPage.value = null;
  modalOpen.value = true;
}

function openEditModal(page) {
  editingPage.value = page;
  modalOpen.value = true;
}

function onSaved() {
  fetchPages();
  toast.success(editingPage.value ? 'Page updated successfully.' : 'Page created successfully.');
}

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.status) params.status = f.status;
  if (f.page_type?.trim()) params.page_type = f.page_type.trim();
  if (f.published_from) params.published_from = f.published_from;
  if (f.published_to) params.published_to = f.published_to;
  return params;
}

function applyFilters() {
  fetchPages();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchPages();
}

async function fetchPages() {
  loading.value = true;
  error.value = null;
  try {
    const params = buildParams();
    pages.value = await getList(params);
  } catch {
    error.value = 'Failed to load pages.';
    pages.value = [];
  } finally {
    loading.value = false;
  }
}

async function confirmDelete(page) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this page?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deletePage(page.id);
    await fetchPages();
    closeConfirmation();
    toast.success('Page deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete page.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function publishPage(page) {
  const confirmed = await openConfirmation({
    title: 'Publish page',
    message: 'Publish this page so it is visible to visitors?',
    confirmLabel: 'Publish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiPublish(page.id);
    await fetchPages();
    closeConfirmation();
    toast.success('Page published successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to publish page.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function unpublishPage(page) {
  const confirmed = await openConfirmation({
    title: 'Unpublish page',
    message: 'Unpublish this page? It will no longer be visible.',
    confirmLabel: 'Unpublish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiUnpublish(page.id);
    await fetchPages();
    closeConfirmation();
    toast.success('Page unpublished successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to unpublish page.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchPages();
});
</script>
