<template>
  <PageContainer title="Study Materials" description="Browse and download learning materials for your class.">
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!materials.length"
      title="No study materials"
      description="No study materials have been shared for your class yet."
    />
    <div v-else class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      <div
        v-for="m in materials"
        :key="m.id"
        class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <h3 class="font-medium text-zinc-900 dark:text-zinc-100">{{ m.title }}</h3>
        <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">{{ m.subject_name }}{{ m.teacher_name ? ` · ${m.teacher_name}` : '' }}</p>
        <p class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ m.publish_date }}</p>
        <p v-if="m.description" class="mt-2 line-clamp-2 text-sm text-zinc-600 dark:text-zinc-400">{{ m.description }}</p>
        <a
          v-if="m.file_url"
          :href="m.file_url"
          target="_blank"
          rel="noopener noreferrer"
          class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
        >
          <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
          </svg>
          Download
        </a>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getMaterials } from '../services/materialService.js';

const loading = ref(true);
const error = ref(null);
const materials = ref([]);

onMounted(async () => {
  try {
    const res = await getMaterials();
    materials.value = res?.data ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load materials.';
    materials.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
