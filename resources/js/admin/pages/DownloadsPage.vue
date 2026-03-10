<template>
  <PageContainer
    title="Downloads"
    description="Manage downloadable files. Publish and organize by category."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Download
      </button>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search downloads…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[180px]">
        <SearchableSelect
          id="filter-category"
          v-model="filters.category_id"
          label="Category"
          :options="categories"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search categories…"
          clearable
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
        <label for="filter-access" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Access</label>
        <select
          id="filter-access"
          v-model="filters.access_type"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="public">Public</option>
          <option value="registered">Registered</option>
          <option value="private">Private</option>
        </select>
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
        Loading downloads…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="downloads.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No downloads match your filters.' : 'No downloads yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[800px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">File type</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">File size</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Access</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Published at</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="d in downloads"
              :key="d.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ d.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.category_name ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.file_type ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.file_size_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.access_type ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ d.published_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="statusClass(d.status)"
                >
                  {{ d.status }}
                </span>
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <a
                    v-if="d.file_url"
                    :href="d.file_url"
                    target="_blank"
                    rel="noopener noreferrer"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    Download
                  </a>
                  <template v-if="d.file_url">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  </template>
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(d)"
                  >
                    Edit
                  </button>
                  <template v-if="d.status === 'draft'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="publishDownload(d)"
                    >
                      Publish
                    </button>
                  </template>
                  <template v-if="d.status === 'published'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="unpublishDownload(d)"
                    >
                      Unpublish
                    </button>
                  </template>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(d)"
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

    <DownloadForm
      v-model="modalOpen"
      :download="editingDownload"
      :categories="categories"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import DownloadForm from '../components/DownloadForm.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import {
  getList,
  deleteDownload,
  publish as apiPublish,
  unpublish as apiUnpublish,
} from '../services/downloadService.js';
import { getCategories } from '../services/downloadCategoryService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

function initialFilters() {
  return {
    search: '',
    category_id: null,
    status: '',
    access_type: '',
    published_from: '',
    published_to: '',
  };
}

const loading = ref(true);
const error = ref(null);
const downloads = ref([]);
const categories = ref([]);
const modalOpen = ref(false);
const editingDownload = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(
    f.search?.trim() ||
    f.category_id ||
    f.status ||
    f.access_type ||
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
  editingDownload.value = null;
  modalOpen.value = true;
}

function openEditModal(d) {
  editingDownload.value = d;
  modalOpen.value = true;
}

function onSaved() {
  fetchDownloads();
  toast.success(editingDownload.value ? 'Download updated successfully.' : 'Download created successfully.');
}

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.category_id) params.category_id = f.category_id;
  if (f.status) params.status = f.status;
  if (f.access_type) params.access_type = f.access_type;
  if (f.published_from) params.published_from = f.published_from;
  if (f.published_to) params.published_to = f.published_to;
  return params;
}

function applyFilters() {
  fetchDownloads();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchDownloads();
}

async function fetchDownloads() {
  loading.value = true;
  error.value = null;
  try {
    const params = buildParams();
    downloads.value = await getList(params);
  } catch {
    error.value = 'Failed to load downloads.';
    downloads.value = [];
  } finally {
    loading.value = false;
  }
}

async function fetchCategories() {
  try {
    categories.value = await getCategories();
  } catch {
    categories.value = [];
  }
}

async function confirmDelete(d) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this download?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteDownload(d.id);
    await fetchDownloads();
    closeConfirmation();
    toast.success('Download deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete download.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function publishDownload(d) {
  const confirmed = await openConfirmation({
    title: 'Publish download',
    message: 'Publish this file so it is available to users?',
    confirmLabel: 'Publish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiPublish(d.id);
    await fetchDownloads();
    closeConfirmation();
    toast.success('Download published successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to publish download.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function unpublishDownload(d) {
  const confirmed = await openConfirmation({
    title: 'Unpublish download',
    message: 'Unpublish this file? It will no longer be available.',
    confirmLabel: 'Unpublish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiUnpublish(d.id);
    await fetchDownloads();
    closeConfirmation();
    toast.success('Download unpublished successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to unpublish download.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchCategories();
  fetchDownloads();
});
</script>
