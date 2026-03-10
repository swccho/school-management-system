<template>
  <PageContainer title="Notices" description="View published notices from the school.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="notices.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No notices at the moment.
    </div>
    <div v-else class="space-y-3">
      <button
        v-for="n in notices"
        :key="n.id"
        type="button"
        class="w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
        @click="openNotice(n)"
      >
        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</p>
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
import { getNotices, getNotice } from '../services/noticeService.js';

const loading = ref(true);
const notices = ref([]);
const selectedNotice = ref(null);

async function load() {
  try {
    notices.value = await getNotices();
  } catch {
    notices.value = [];
  } finally {
    loading.value = false;
  }
}

async function openNotice(n) {
  try {
    const full = await getNotice(n.id);
    selectedNotice.value = full;
  } catch {
    selectedNotice.value = { ...n, content: n.excerpt ?? '' };
  }
}

onMounted(load);
</script>
