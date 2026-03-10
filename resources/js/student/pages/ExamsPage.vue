<template>
  <PageContainer title="Exam Schedule" description="View upcoming and published exam schedules for your class.">
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!exams.length"
      title="No exam schedule"
      description="No exam schedule has been published yet. Check back later."
    />
    <div v-else class="space-y-4">
      <div
        v-for="(exam, i) in exams"
        :key="exam.id"
        class="rounded-xl border bg-white dark:bg-zinc-900"
        :class="isUpcoming(exam) && i === 0 ? 'border-amber-300 dark:border-amber-600' : 'border-zinc-200 dark:border-zinc-800'"
      >
        <div class="flex flex-wrap items-start justify-between gap-2 border-b border-zinc-200 p-4 dark:border-zinc-800">
          <div>
            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ exam.name }}</h3>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
              {{ exam.exam_type?.name ?? '' }} · {{ exam.start_date }} – {{ exam.end_date }}
            </p>
          </div>
          <span
            v-if="isUpcoming(exam) && i === 0"
            class="rounded bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-800 dark:bg-amber-900/40 dark:text-amber-200"
          >
            Upcoming
          </span>
        </div>
        <div class="p-4">
          <p v-if="exam.description" class="mb-3 text-sm text-zinc-600 dark:text-zinc-400">{{ exam.description }}</p>
          <div class="flex flex-wrap gap-2">
            <span
              v-for="(s, j) in exam.subjects"
              :key="j"
              class="rounded bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
            >
              {{ s.subject_name }}{{ s.full_marks != null ? ` (${s.full_marks})` : '' }}
            </span>
          </div>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getExams } from '../services/examService.js';

const loading = ref(true);
const error = ref(null);
const exams = ref([]);

function isUpcoming(exam) {
  if (!exam.end_date) return false;
  return new Date(exam.end_date) >= new Date();
}

onMounted(async () => {
  try {
    const res = await getExams();
    exams.value = res?.data ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load exam schedule.';
    exams.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
