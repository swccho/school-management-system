<template>
  <PageContainer title="My Classes" description="Your assigned classes and subjects.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ error }}</div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Section</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Session</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="a in assignments"
            :key="a.id"
            class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
          >
            <td class="px-4 py-3 text-sm text-zinc-900 dark:text-zinc-100">{{ a.class_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.section_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.subject_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ a.academic_session_name }}</td>
          </tr>
        </tbody>
      </table>
      <p v-if="!assignments.length" class="p-6 text-center text-sm text-zinc-500 dark:text-zinc-400">No assignments for the current session.</p>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';

const authStore = useAuthStore();
const loading = ref(false);
const error = ref(null);

const assignments = computed(() => authStore.assignments ?? []);

onMounted(async () => {
  if (!authStore.assignments?.length) {
    loading.value = true;
    try {
      await authStore.fetchMe();
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Failed to load assignments.';
    } finally {
      loading.value = false;
    }
  }
});
</script>
