<template>
  <PageContainer
    title="Guides"
    description="Create and manage help documentation. Use Markdown in content."
  >
    <template #actions>
      <router-link
        :to="{ name: 'admin-guide-guides-create' }"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Add Guide
      </router-link>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search guides…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[200px]">
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
        </select>
      </div>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="applyFilters"
        >
          Apply
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
        Loading guides…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="guides.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No guides match your filters.' : 'No guides yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[700px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Category</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Updated At</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="g in guides"
              :key="g.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ g.title }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ g.category?.name ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2.5 py-1 text-xs font-medium capitalize"
                  :class="g.status === 'published' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-amber-100 text-amber-800 dark:bg-amber-900/40 dark:text-amber-300'"
                >
                  {{ g.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ formatDate(g.updated_at) }}
              </td>
              <td class="px-4 py-3">
                <div class="relative flex items-center gap-1">
                  <router-link
                    :to="{ name: 'admin-guide-guides-edit', params: { id: g.id } }"
                    class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 hover:text-zinc-900 dark:text-zinc-400 dark:hover:bg-zinc-800 dark:hover:text-zinc-100"
                  >
                    Edit
                  </router-link>
                  <button
                    type="button"
                    class="rounded-lg px-2.5 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 hover:text-red-700 dark:text-red-400 dark:hover:bg-red-900/20 dark:hover:text-red-300"
                    @click="confirmDelete(g)"
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
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getGuides, deleteGuide } from '../services/adminGuideService.js';
import { getCategories } from '../services/adminGuideCategoryService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const loading = ref(true);
const error = ref(null);
const guides = ref([]);
const categories = ref([]);
const filters = ref({
  search: '',
  category_id: null,
  status: '',
});

const hasActiveFilters = computed(
  () => filters.value.search !== '' || filters.value.category_id != null || filters.value.status !== ''
);

function applyFilters() {
  fetchGuides();
}

function resetFilters() {
  filters.value = { search: '', category_id: null, status: '' };
  fetchGuides();
}

function formatDate(iso) {
  if (!iso) return '—';
  try {
    const d = new Date(iso);
    return d.toLocaleDateString(undefined, { dateStyle: 'short' });
  } catch {
    return iso;
  }
}

async function fetchCategories() {
  try {
    categories.value = await getCategories();
  } catch {
    categories.value = [];
  }
}

async function fetchGuides() {
  loading.value = true;
  error.value = null;
  try {
    const params = {};
    if (filters.value.search) params.search = filters.value.search;
    if (filters.value.category_id != null) params.category_id = filters.value.category_id;
    if (filters.value.status) params.status = filters.value.status;
    guides.value = await getGuides(params);
  } catch {
    error.value = 'Failed to load guides.';
    guides.value = [];
  } finally {
    loading.value = false;
  }
}

async function confirmDelete(g) {
  const confirmed = await openConfirmation({
    title: 'Confirm deletion',
    message: 'Are you sure you want to delete this guide?',
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteGuide(g.id);
    await fetchGuides();
    closeConfirmation();
    toast.success('Guide deleted successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to delete guide.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchCategories();
  fetchGuides();
});
</script>
