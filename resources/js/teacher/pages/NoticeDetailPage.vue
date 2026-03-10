<template>
  <PageContainer title="Notice" description="View notice details.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="!notice" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      Notice not found.
    </div>
    <article v-else class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="flex flex-wrap items-start justify-between gap-2">
        <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ notice.title }}</h1>
        <span v-if="notice.is_featured" class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Featured</span>
      </div>
      <p class="mt-1 text-sm text-zinc-500">{{ notice.publish_date }} · {{ notice.category?.name ?? 'Uncategorized' }}</p>
      <div class="mt-4 prose prose-sm dark:prose-invert max-w-none" v-html="notice.content"></div>
      <router-link
        :to="{ name: 'teacher-notices' }"
        class="mt-6 inline-block rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-zinc-300"
      >
        Back to Notices
      </router-link>
    </article>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getNotice } from '../services/noticeService.js';

const route = useRoute();
const loading = ref(true);
const notice = ref(null);

const id = computed(() => route.params.id);

async function load() {
  if (!id.value) {
    loading.value = false;
    return;
  }
  try {
    notice.value = await getNotice(id.value);
  } catch {
    notice.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
