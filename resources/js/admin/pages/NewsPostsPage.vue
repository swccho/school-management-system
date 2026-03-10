<template>
  <PageContainer
    title="News"
    description="Manage news posts. Publish, unpublish, and feature articles."
  >
    <template #actions>
      <button
        type="button"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        @click="openCreateModal"
      >
        Add News Post
      </button>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search news…"
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
      <div class="min-w-[100px]">
        <label for="filter-featured" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured</label>
        <select
          id="filter-featured"
          v-model="filters.is_featured"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="1">Yes</option>
          <option value="0">No</option>
        </select>
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
        Loading news posts…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="posts.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No news posts match your filters.' : 'No news posts yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Published at</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="post in posts"
              :key="post.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ post.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ post.category_name ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ post.published_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="statusClass(post.status)"
                >
                  {{ post.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ post.is_featured ? 'Yes' : 'No' }}
              </td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-2">
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openEditModal(post)"
                  >
                    Edit
                  </button>
                  <template v-if="post.status === 'draft'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="publishPost(post)"
                    >
                      Publish
                    </button>
                  </template>
                  <template v-if="post.status === 'published'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="unpublishPost(post)"
                    >
                      Unpublish
                    </button>
                  </template>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(post)"
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

    <NewsPostForm
      v-model="modalOpen"
      :post="editingPost"
      :categories="categories"
      @saved="onSaved"
      @close="modalOpen = false"
    />
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import NewsPostForm from '../components/NewsPostForm.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getList, deletePost, publish as apiPublish, unpublish as apiUnpublish } from '../services/newsPostService.js';
import { getCategories } from '../services/newsCategoryService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

function initialFilters() {
  return {
    search: '',
    category_id: null,
    status: '',
    published_from: '',
    published_to: '',
    is_featured: '',
  };
}

const loading = ref(true);
const error = ref(null);
const posts = ref([]);
const categories = ref([]);
const modalOpen = ref(false);
const editingPost = ref(null);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(
    f.search?.trim() ||
    f.category_id ||
    f.status ||
    f.published_from ||
    f.published_to ||
    f.is_featured !== ''
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
  editingPost.value = null;
  modalOpen.value = true;
}

function openEditModal(post) {
  editingPost.value = post;
  modalOpen.value = true;
}

function onSaved() {
  fetchPosts();
  toast.success(editingPost.value ? 'News post updated successfully.' : 'News post created successfully.');
}

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.category_id) params.category_id = f.category_id;
  if (f.status) params.status = f.status;
  if (f.published_from) params.published_from = f.published_from;
  if (f.published_to) params.published_to = f.published_to;
  if (f.is_featured !== '') params.is_featured = f.is_featured;
  return params;
}

function applyFilters() {
  fetchPosts();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchPosts();
}

async function fetchPosts() {
  loading.value = true;
  error.value = null;
  try {
    const params = buildParams();
    posts.value = await getList(params);
  } catch {
    error.value = 'Failed to load news posts.';
    posts.value = [];
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

async function confirmDelete(post) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this news post?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deletePost(post.id);
    await fetchPosts();
    closeConfirmation();
    toast.success('News post deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete news post.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function publishPost(post) {
  const confirmed = await openConfirmation({
    title: 'Publish news post',
    message: 'Publish this post so it is visible to readers?',
    confirmLabel: 'Publish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiPublish(post.id);
    await fetchPosts();
    closeConfirmation();
    toast.success('News post published successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to publish news post.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function unpublishPost(post) {
  const confirmed = await openConfirmation({
    title: 'Unpublish news post',
    message: 'Unpublish this post? It will no longer be visible to readers.',
    confirmLabel: 'Unpublish',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await apiUnpublish(post.id);
    await fetchPosts();
    closeConfirmation();
    toast.success('News post unpublished successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to unpublish news post.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchCategories();
  fetchPosts();
});
</script>
