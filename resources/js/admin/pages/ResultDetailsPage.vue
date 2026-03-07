<template>
  <PageContainer
    title="Result details"
    description="View student result summary and subject-wise breakdown."
  >
    <template #actions>
      <router-link
        to="/admin/results"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        ← Back to results
      </router-link>
    </template>

    <div class="space-y-6">
      <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading…
      </div>
      <div v-else-if="error" class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-700 dark:border-red-900 dark:bg-red-900/20 dark:text-red-300">
        {{ error }}
      </div>
      <template v-else-if="detail">
        <div>
          <h3 class="mb-1 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Exam &amp; Student</h3>
          <p class="text-sm text-zinc-600 dark:text-zinc-400">
            {{ detail.exam?.name ?? '—' }} <span v-if="detail.exam?.academic_session_name">({{ detail.exam.academic_session_name }})</span>
            — {{ detail.student?.full_name ?? '—' }}
            <span v-if="detail.student?.roll_no">({{ detail.student.roll_no }})</span>
          </p>
        </div>
        <ResultSummaryCard
          :total-marks="detail.total_marks"
          :obtained-marks="detail.obtained_marks"
          :gpa="detail.gpa"
          :letter-grade="detail.letter_grade"
          :pass-status="detail.pass_status"
          :merit-position="detail.merit_position"
          :published-status="detail.published_status"
        />
        <div>
          <h3 class="mb-2 text-sm font-semibold text-zinc-800 dark:text-zinc-200">Subject details</h3>
          <ResultSubjectsTable :subject-details="detail.subject_details ?? []" />
        </div>
      </template>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import ResultSummaryCard from '../components/ResultSummaryCard.vue';
import ResultSubjectsTable from '../components/ResultSubjectsTable.vue';
import { getResultDetail } from '../services/resultService.js';

const route = useRoute();
const loading = ref(true);
const error = ref(null);
const detail = ref(null);

async function fetch() {
  const id = route.params.id;
  if (!id) {
    error.value = 'Invalid result id.';
    loading.value = false;
    return;
  }
  loading.value = true;
  error.value = null;
  try {
    detail.value = await getResultDetail(id);
  } catch {
    error.value = 'Failed to load result details.';
    detail.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetch());
</script>
