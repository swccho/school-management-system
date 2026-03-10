<template>
  <PageContainer :title="exam?.name ?? 'Performance Summary'" description="Subject-wise performance for your assigned contexts.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ error }}</div>
    <div v-else-if="summary.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No summary data for your assigned contexts.
    </div>
    <div v-else class="space-y-6">
      <div
        v-for="(ctx, idx) in summary"
        :key="idx"
        class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <h2 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ ctx.class_name }} · {{ ctx.section_name ?? '—' }} · {{ ctx.subject_name }}</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-5">
          <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Students</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ ctx.students_count }}</p>
          </div>
          <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Average</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ ctx.average_marks }}</p>
          </div>
          <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Highest</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ ctx.highest_marks }}</p>
          </div>
          <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Lowest</p>
            <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ ctx.lowest_marks }}</p>
          </div>
          <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
            <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Failed</p>
            <p class="mt-1 text-lg font-semibold" :class="ctx.failed_count > 0 ? 'text-red-600 dark:text-red-400' : 'text-zinc-900 dark:text-zinc-100'">{{ ctx.failed_count }}</p>
          </div>
        </div>
        <div v-if="ctx.failed_students?.length" class="mt-4">
          <p class="mb-2 text-xs font-medium text-zinc-600 dark:text-zinc-400">Failed students (obtained &lt; pass {{ ctx.pass_marks }})</p>
          <div class="overflow-x-auto rounded-lg border border-zinc-200 dark:border-zinc-700">
            <table class="w-full min-w-[300px] text-sm">
              <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-700">
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Name</th>
                  <th class="px-3 py-2 text-right font-medium text-zinc-700 dark:text-zinc-300">Obtained</th>
                  <th class="px-3 py-2 text-right font-medium text-zinc-700 dark:text-zinc-300">Pass</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="s in ctx.failed_students"
                  :key="s.student_id"
                  class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                >
                  <td class="px-3 py-2 text-zinc-900 dark:text-zinc-100">{{ s.full_name }}</td>
                  <td class="px-3 py-2 text-right text-red-600 dark:text-red-400">{{ s.obtained_marks }}</td>
                  <td class="px-3 py-2 text-right text-zinc-600 dark:text-zinc-400">{{ s.pass_marks }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getExamSummary } from '../services/examService.js';

const route = useRoute();
const examId = computed(() => route.params.examId);
const loading = ref(true);
const error = ref(null);
const exam = ref(null);
const summary = ref([]);

onMounted(async () => {
  if (!examId.value) return;
  loading.value = true;
  error.value = null;
  try {
    const data = await getExamSummary(examId.value);
    exam.value = data.exam ?? null;
    summary.value = data.summary ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load summary.';
    summary.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
