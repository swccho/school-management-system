<template>
  <PageContainer
    title="Exam details"
    :description="exam ? exam.name : ''"
  >
    <template #actions>
      <router-link
        :to="`/admin/exams/${route.params.id}/edit`"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Edit exam
      </router-link>
      <router-link
        to="/admin/exams"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Back to list
      </router-link>
    </template>

    <div class="space-y-6">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">Loading…</div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">{{ error }}</div>
      <template v-else-if="exam">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            <span class="font-medium text-zinc-800 dark:text-zinc-200">Session:</span> {{ exam.academic_session_name ?? '—' }}
            · <span class="font-medium text-zinc-800 dark:text-zinc-200">Type:</span> {{ exam.exam_type_name ?? '—' }}
            · <span class="font-medium text-zinc-800 dark:text-zinc-200">Dates:</span> {{ exam.start_date_formatted ?? '—' }} – {{ exam.end_date_formatted ?? '—' }}
            · <span class="font-medium text-zinc-800 dark:text-zinc-200">Status:</span> {{ exam.status }}
          </p>
          <p v-if="exam.result_publish_date_formatted ?? exam.result_publish_date" class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
            Result publish date: {{ exam.result_publish_date_formatted ?? exam.result_publish_date ?? '—' }}
          </p>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Target classes</p>
          <ul class="list-inside list-disc text-sm text-zinc-600 dark:text-zinc-400">
            <li v-for="c in exam.class_configs" :key="c.id">
              {{ c.class_name }}{{ c.section_name ? ` – ${c.section_name}` : '' }}
            </li>
          </ul>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject configuration</p>
          <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
              <thead>
                <tr class="border-b border-zinc-200 dark:border-zinc-800">
                  <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Class</th>
                  <th class="px-4 py-2 text-left text-xs font-medium text-zinc-500 dark:text-zinc-400">Subject</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Full</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Pass</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Theory</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Practical</th>
                  <th class="px-4 py-2 text-right text-xs font-medium text-zinc-500 dark:text-zinc-400">Oral</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="s in exam.subject_configs"
                  :key="s.id"
                  class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
                >
                  <td class="px-4 py-2.5 text-sm text-zinc-900 dark:text-zinc-100">{{ s.class_name ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ s.subject_name ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.full_marks ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.pass_marks ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.theory_marks ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.practical_marks ?? '—' }}</td>
                  <td class="px-4 py-2.5 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ s.oral_marks ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getExam } from '../services/examService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const exam = ref(null);

onMounted(async () => {
  try {
    exam.value = await getExam(route.params.id);
  } catch {
    error.value = 'Failed to load exam.';
  } finally {
    loading.value = false;
  }
});
</script>
