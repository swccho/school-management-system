<template>
  <PageContainer title="Results" description="View your exam results and academic performance.">
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!results.length"
      title="No results yet"
      description="No results have been published yet. Check back after exams."
    />
    <div v-else class="space-y-6">
      <div v-for="r in results" :key="r.id" class="rounded-xl border border-zinc-200 bg-white overflow-hidden dark:border-zinc-800 dark:bg-zinc-900">
        <div class="border-b border-zinc-200 bg-zinc-50 px-4 py-3 dark:border-zinc-800 dark:bg-zinc-800/50">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">{{ r.exam_name }}</h3>
            <span
              class="rounded px-2 py-0.5 text-xs font-medium"
              :class="r.pass_status === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'"
            >
              {{ r.pass_status === 'pass' ? 'Pass' : 'Fail' }}
            </span>
          </div>
          <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            {{ r.academic_session?.name ?? '' }}{{ r.exam_type?.name ? ` · ${r.exam_type.name}` : '' }}
          </p>
        </div>
        <div class="p-4">
          <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <div>
              <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Total / Obtained</p>
              <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ r.obtained_marks ?? '—' }} / {{ r.total_marks ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">GPA</p>
              <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ r.gpa ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Grade</p>
              <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ r.letter_grade ?? '—' }}</p>
            </div>
            <div>
              <p class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Position</p>
              <p class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ r.merit_position ?? '—' }}</p>
            </div>
          </div>
          <div class="mt-4 overflow-x-auto">
            <table class="w-full min-w-[300px] text-sm">
              <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-800">
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Obtained</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Full marks</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Grade</th>
                  <th class="px-3 py-2 text-left font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="(s, i) in r.subject_details"
                  :key="i"
                  class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                >
                  <td class="px-3 py-2 font-medium text-zinc-900 dark:text-zinc-100">{{ s.subject_name ?? '—' }}</td>
                  <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ s.obtained_marks ?? '—' }}</td>
                  <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ s.full_marks ?? '—' }}</td>
                  <td class="px-3 py-2 text-zinc-600 dark:text-zinc-400">{{ s.grade_letter ?? '—' }}</td>
                  <td class="px-3 py-2">
                    <span
                      class="rounded px-1.5 py-0.5 text-xs"
                      :class="s.pass_status === 'pass' ? 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200'"
                    >
                      {{ s.pass_status ?? '—' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
          <p v-if="r.remarks" class="mt-3 text-sm text-zinc-600 dark:text-zinc-400">Remarks: {{ r.remarks }}</p>
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
import { getResults } from '../services/resultService.js';

const loading = ref(true);
const error = ref(null);
const results = ref([]);

onMounted(async () => {
  try {
    const res = await getResults();
    results.value = res?.data ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load results.';
    results.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
