<template>
  <PageContainer title="Notice" :description="notice?.publish_date ? `${notice.publish_date}${notice.category?.name ? ` · ${notice.category.name}` : ''}` : ''">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else-if="notice" class="space-y-4">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-start justify-between gap-2">
          <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ notice.title }}</h2>
          <span v-if="notice.is_featured" class="shrink-0 rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Featured</span>
        </div>
        <div class="prose prose-sm mt-4 dark:prose-invert max-w-none" v-html="notice.content ?? ''"></div>
        <div v-if="notice.attachments?.length" class="mt-4 border-t border-zinc-200 pt-4 dark:border-zinc-800">
          <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Attachments</p>
          <ul class="mt-1 list-inside list-disc text-sm text-zinc-600 dark:text-zinc-400">
            <li v-for="a in notice.attachments" :key="a.id">{{ a.name }}</li>
          </ul>
        </div>
      </div>
      <router-link
        to="/student/announcements"
        class="inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
      >
        ← Back to announcements
      </router-link>
    </div>
    <div v-else class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      Notice not found.
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, watch } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getNotice } from '../services/noticeService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const notice = ref(null);

async function fetchNotice() {
  const id = route.params.id;
  if (!id) return;
  loading.value = true;
  error.value = null;
  try {
    notice.value = await getNotice(id);
  } catch (e) {
    error.value = e.response?.status === 404 ? 'Notice not found.' : (e.response?.data?.message ?? 'Failed to load notice.');
    notice.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(fetchNotice);
watch(() => route.params.id, fetchNotice);
</script>
