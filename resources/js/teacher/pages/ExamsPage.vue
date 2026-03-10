<template>
  <PageContainer title="Exams & Marks" description="View exams and enter marks for your assigned subjects.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="exams.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No exams in the current session for your assigned subjects.
    </div>
    <div v-else class="space-y-3">
      <div
        v-for="exam in exams"
        :key="exam.id"
        class="flex flex-wrap items-center justify-between gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <div>
          <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ exam.name }}</p>
          <p class="text-sm text-zinc-500">{{ exam.exam_type }} · {{ exam.start_date }} – {{ exam.end_date }} · {{ exam.subjects_count }} subject(s)</p>
        </div>
        <router-link
          :to="`/teacher/exams/${exam.id}/marks`"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900"
        >
          Enter marks
        </router-link>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getExams } from '../services/examService.js';

const loading = ref(true);
const exams = ref([]);

onMounted(async () => {
  try {
    exams.value = await getExams();
  } catch {
    exams.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
