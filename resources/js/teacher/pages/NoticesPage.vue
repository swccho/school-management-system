<template>
  <PageContainer title="Notices" description="View published notices from the school.">
    <div class="mb-4 flex flex-wrap items-end gap-3">
      <div class="flex flex-col gap-1">
        <label for="filter-category" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Category</label>
        <select
          id="filter-category"
          v-model="filters.category_id"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option v-for="c in categoryOptions" :key="c.id" :value="c.id">{{ c.name }}</option>
        </select>
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-date-from" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">From</label>
        <input
          id="filter-date-from"
          v-model="filters.date_from"
          type="date"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        />
      </div>
      <div class="flex flex-col gap-1">
        <label for="filter-date-to" class="text-xs font-medium text-zinc-500 dark:text-zinc-400">To</label>
        <input
          id="filter-date-to"
          v-model="filters.date_to"
          type="date"
          class="rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        />
      </div>
      <button
        type="button"
        class="rounded-lg bg-zinc-800 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600"
        @click="applyFilters"
      >
        Apply
      </button>
      <button
        type="button"
        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-zinc-300"
        @click="clearFilters"
      >
        Clear
      </button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="notices.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No notices at the moment.
    </div>
    <div v-else class="space-y-4">
      <button
        v-for="n in notices"
        :key="n.id"
        type="button"
        class="w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
        @click="openNotice(n)"
      >
        <div class="flex items-start justify-between gap-2">
          <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</p>
          <span v-if="n.is_featured" class="shrink-0 rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Featured</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500">{{ n.publish_date }} · {{ n.category?.name ?? 'Uncategorized' }}</p>
        <p v-if="n.excerpt" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ n.excerpt }}</p>
      </button>
    </div>
    <div v-if="selectedNotice" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4" @click.self="selectedNotice = null">
      <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900" @click.stop>
        <h3 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ selectedNotice.title }}</h3>
        <p class="mt-1 text-sm text-zinc-500">{{ selectedNotice.publish_date }} · {{ selectedNotice.category?.name ?? '' }}</p>
        <div class="mt-4 prose prose-sm dark:prose-invert max-w-none" v-html="selectedNotice.content"></div>
        <button type="button" class="mt-4 rounded-lg border px-4 py-2 text-sm dark:border-zinc-600" @click="selectedNotice = null">Close</button>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getNotices, getNotice, getNoticeCategoryOptions } from '../services/noticeService.js';

const loading = ref(true);
const notices = ref([]);
const selectedNotice = ref(null);
const categoryOptions = ref([]);
const filters = ref({
  category_id: '',
  date_from: '',
  date_to: '',
});

async function loadCategories() {
  try {
    categoryOptions.value = await getNoticeCategoryOptions();
  } catch {
    categoryOptions.value = [];
  }
}

async function load() {
  loading.value = true;
  try {
    const params = {};
    if (filters.value.category_id) params.category_id = filters.value.category_id;
    if (filters.value.date_from) params.date_from = filters.value.date_from;
    if (filters.value.date_to) params.date_to = filters.value.date_to;
    notices.value = await getNotices(params);
  } catch {
    notices.value = [];
  } finally {
    loading.value = false;
  }
}

function applyFilters() {
  load();
}

function clearFilters() {
  filters.value = { category_id: '', date_from: '', date_to: '' };
  load();
}

async function openNotice(n) {
  try {
    const full = await getNotice(n.id);
    selectedNotice.value = full;
  } catch {
    selectedNotice.value = { ...n, content: n.excerpt ?? '' };
  }
}

onMounted(() => {
  loadCategories();
  load();
});
</script>
