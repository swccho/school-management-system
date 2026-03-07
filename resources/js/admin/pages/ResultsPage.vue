<template>
  <PageContainer
    title="Results"
    description="View and manage generated exam results."
  >
    <template #actions>
      <router-link
        to="/admin/results/generate"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Generate Results
      </router-link>
    </template>

    <div class="space-y-4">
      <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <p class="mb-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">Filters</p>
        <div class="flex flex-wrap items-end gap-4">
          <div class="min-w-[180px]">
            <SearchableSelect
              id="res-exam"
              v-model="filters.exam_id"
              label="Exam"
              :options="examOptions"
              label-key="name"
              value-key="id"
              placeholder="All exams…"
              search-placeholder="Search…"
              clearable
              @update:model-value="fetch"
            />
          </div>
          <div class="min-w-[140px]">
            <label for="res-pass" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Pass status</label>
            <select
              id="res-pass"
              v-model="filters.pass_status"
              class="mt-1 w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              @change="fetch"
            >
              <option value="">All</option>
              <option value="pass">Pass</option>
              <option value="fail">Fail</option>
            </select>
          </div>
        </div>
      </div>

      <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
        <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          Loading…
        </div>
        <div v-else-if="listError" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
          {{ listError }}
        </div>
        <div v-else-if="list.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
          No results yet. Generate results from an exam that has marks entered.
        </div>
        <div v-else class="overflow-x-auto">
          <table class="w-full min-w-[600px]">
            <thead>
              <tr class="border-b border-zinc-200 dark:border-zinc-800">
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Exam</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Student</th>
                <th class="px-4 py-3 text-right text-sm font-medium text-zinc-700 dark:text-zinc-300">Obtained</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">GPA</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Grade</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Merit</th>
                <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
              </tr>
            </thead>
            <tbody>
              <tr
                v-for="row in list"
                :key="row.id"
                class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
              >
                <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ row.exam_name ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ row.student_name ?? '—' }} <span v-if="row.roll_no" class="text-zinc-400">({{ row.roll_no }})</span></td>
                <td class="px-4 py-3 text-right text-sm text-zinc-600 dark:text-zinc-400">{{ row.obtained_marks ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ row.gpa ?? '—' }}</td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ row.letter_grade ?? '—' }}</td>
                <td class="px-4 py-3">
                  <span
                    class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                    :class="row.pass_status === 'pass' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300'"
                  >
                    {{ row.pass_status === 'pass' ? 'Pass' : 'Fail' }}
                  </span>
                </td>
                <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ row.merit_position ?? '—' }}</td>
                <td class="px-4 py-3">
                  <router-link
                    :to="`/admin/results/${row.id}`"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getResults } from '../services/resultService.js';
import { getExams } from '../services/marksEntryService.js';

const loading = ref(true);
const listError = ref(null);
const list = ref([]);
const examOptions = ref([]);
const filters = ref({
  exam_id: null,
  pass_status: '',
});

async function fetch() {
  loading.value = true;
  listError.value = null;
  try {
    const params = {};
    if (filters.value.exam_id) params.exam_id = filters.value.exam_id;
    if (filters.value.pass_status) params.pass_status = filters.value.pass_status;
    list.value = await getResults(params);
  } catch {
    listError.value = 'Failed to load results.';
    list.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(async () => {
  try {
    examOptions.value = await getExams();
  } catch {
    examOptions.value = [];
  }
  await fetch();
});
</script>
