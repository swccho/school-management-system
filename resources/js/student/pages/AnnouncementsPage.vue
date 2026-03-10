<template>
  <PageContainer title="Announcements" description="Published notices and announcements from the school.">
    <LoadingSkeleton v-if="loading" variant="cards" />
    <EmptyState
      v-else-if="!notices.length"
      title="No announcements"
      description="No announcements at the moment. Check back later."
    />
    <div v-else class="space-y-4">
      <router-link
        v-for="n in notices"
        :key="n.id"
        :to="`/student/announcements/${n.id}`"
        class="block w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
      >
        <div class="flex items-start justify-between gap-2">
          <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</p>
          <span v-if="n.is_featured" class="shrink-0 rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200">Featured</span>
        </div>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ n.publish_date }}{{ n.category?.name ? ` · ${n.category.name}` : '' }}</p>
        <p v-if="n.excerpt" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ n.excerpt }}</p>
      </router-link>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getNotices } from '../services/noticeService.js';

const loading = ref(true);
const notices = ref([]);

onMounted(async () => {
  try {
    const res = await getNotices();
    notices.value = res?.data ?? [];
  } catch {
    notices.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
