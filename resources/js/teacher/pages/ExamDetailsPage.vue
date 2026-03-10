<template>
  <PageContainer :title="exam?.name ?? 'Exam'" description="View exam details and enter marks or see performance summary.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ error }}</div>
    <div v-else-if="exam" class="space-y-6">
      <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="text-sm text-zinc-600 dark:text-zinc-400">{{ exam.exam_type }} · {{ exam.start_date }} – {{ exam.end_date }}</p>
        <p class="mt-2 flex flex-wrap items-center gap-2">
          <span
            v-if="exam.marks_entry_open"
            class="rounded bg-green-100 px-2 py-1 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-400"
          >
            Marks entry open
          </span>
          <span
            v-else
            class="rounded bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300"
          >
            Marks entry closed
          </span>
          <span class="rounded bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-300">{{ exam.status }}</span>
        </p>
      </div>

      <div>
        <h2 class="mb-3 text-sm font-semibold text-zinc-900 dark:text-zinc-100">Your contexts (class · section · subject)</h2>
        <ul class="space-y-2">
          <li
            v-for="(ctx, idx) in exam.contexts"
            :key="idx"
            class="flex flex-wrap items-center justify-between gap-4 rounded-lg border border-zinc-200 bg-white p-3 dark:border-zinc-800 dark:bg-zinc-900"
          >
            <span class="text-sm text-zinc-900 dark:text-zinc-100">{{ ctx.class_name }} · {{ ctx.section_name ?? '—' }} · {{ ctx.subject_name }}</span>
            <div class="flex gap-2">
              <router-link
                :to="`/teacher/exams/${examId}/marks`"
                class="rounded border border-zinc-900 bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900"
              >
                Enter marks
              </router-link>
              <router-link
                :to="`/teacher/exams/${examId}/summary`"
                class="rounded border border-zinc-300 bg-white px-3 py-1.5 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
              >
                Summary
              </router-link>
            </div>
          </li>
        </ul>
        <p v-if="!exam.contexts?.length" class="text-sm text-zinc-500 dark:text-zinc-400">No assigned contexts for this exam.</p>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getExam } from '../services/examService.js';

const route = useRoute();
const examId = computed(() => route.params.examId);
const loading = ref(true);
const error = ref(null);
const exam = ref(null);

onMounted(async () => {
  if (!examId.value) return;
  loading.value = true;
  error.value = null;
  try {
    exam.value = await getExam(examId.value);
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load exam.';
    exam.value = null;
  } finally {
    loading.value = false;
  }
});
</script>
