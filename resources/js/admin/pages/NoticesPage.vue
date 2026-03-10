<template>
  <PageContainer
    title="Notices"
    description="Manage notices and announcements. Publish, unpublish, and attach files."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add Notice
      </button>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search notices…"
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
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-publish-from"
          v-model="filters.publish_date_from"
          label="Publish date from"
          placeholder="From"
          clearable
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-publish-to"
          v-model="filters.publish_date_to"
          label="Publish date to"
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
        Loading notices…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="notices.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No notices match your filters.' : 'No notices yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Publish date</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Expiry date</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="notice in notices"
              :key="notice.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ notice.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ notice.category_name ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ notice.publish_date_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ notice.expiry_date_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="statusClass(notice.status)"
                >
                  {{ notice.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ notice.is_featured ? 'Yes' : 'No' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(notice)"
                  >
                    Edit
                  </button>
                  <template v-if="notice.status === 'draft'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="publishNotice(notice)"
                    >
                      Publish
                    </button>
                  </template>
                  <template v-if="notice.status === 'published'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="unpublishNotice(notice)"
                    >
                      Unpublish
                    </button>
                  </template>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(notice)"
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

    <NoticeForm
      v-model="modalOpen"
      :notice="editingNotice"
      :categories="categories"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import NoticeForm from '../components/NoticeForm.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getNotices, deleteNotice, publishNotice as apiPublish, unpublishNotice as apiUnpublish } from '../services/noticeService.js';
import { getCategories } from '../services/noticeCategoryService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

function initialFilters() {
  return {
    search: '',
    category_id: null,
    status: '',
    publish_date_from: '',
    publish_date_to: '',
  };
}

const loading = ref(true);
const error = ref(null);
const notices = ref([]);
const categories = ref([]);
const modalOpen = ref(false);
const editingNotice = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(
    f.search?.trim() ||
    f.category_id ||
    f.status ||
    f.publish_date_from ||
    f.publish_date_to
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
  editingNotice.value = null;
  modalOpen.value = true;
}

function openEditModal(notice) {
  editingNotice.value = notice;
  modalOpen.value = true;
}

function onSaved() {
  fetchNotices();
  toast.success(editingNotice.value ? 'Notice updated successfully.' : 'Notice created successfully.');
}

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.category_id) params.category_id = f.category_id;
  if (f.status) params.status = f.status;
  if (f.publish_date_from) params.publish_date_from = f.publish_date_from;
  if (f.publish_date_to) params.publish_date_to = f.publish_date_to;
  return params;
}

function applyFilters() {
  fetchNotices();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchNotices();
}

async function fetchNotices() {
  loading.value = true;
  error.value = null;
  try {
    const params = buildParams();
    notices.value = await getNotices(params);
  } catch {
    error.value = 'Failed to load notices.';
    notices.value = [];
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

async function confirmDelete(notice) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this notice?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteNotice(notice.id);
    await fetchNotices();
    closeConfirmation();
    toast.success('Notice deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete notice.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function publishNotice(notice) {
  const confirmed = await openConfirmation({
    title: 'Publish notice',
    message: 'Publish this notice so it is visible to readers?',
    confirmLabel: 'Publish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiPublish(notice.id);
    await fetchNotices();
    closeConfirmation();
    toast.success('Notice published successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to publish notice.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function unpublishNotice(notice) {
  const confirmed = await openConfirmation({
    title: 'Unpublish notice',
    message: 'Unpublish this notice? It will no longer be visible to readers.',
    confirmLabel: 'Unpublish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiUnpublish(notice.id);
    await fetchNotices();
    closeConfirmation();
    toast.success('Notice unpublished successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to unpublish notice.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchCategories();
  fetchNotices();
});
</script>
