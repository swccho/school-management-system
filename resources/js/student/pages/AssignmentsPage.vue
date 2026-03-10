<template>
  <PageContainer title="Assignments" description="View and submit assignments for your class.">
    <div class="mb-4 flex flex-wrap items-center gap-3">
      <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Filter:</span>
      <button
        v-for="opt in filterOptions"
        :key="opt.value"
        type="button"
        class="rounded-lg px-3 py-1.5 text-sm font-medium transition"
        :class="filter === opt.value
          ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
          : 'bg-zinc-100 text-zinc-700 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700'"
        @click="filter = opt.value; fetchAssignments()"
      >
        {{ opt.label }}
      </button>
    </div>
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!assignments.length"
      title="No assignments"
      description="No assignments at the moment. Check back later."
    >
      <template #icon>
        <ClipboardList class="h-12 w-12 text-zinc-400 dark:text-zinc-500" />
      </template>
    </EmptyState>
    <div v-else class="space-y-4">
      <router-link
        v-for="a in assignments"
        :key="a.id"
        :to="`/student/assignments/${a.id}`"
        class="block w-full rounded-xl border border-zinc-200 bg-white p-4 text-left transition hover:border-zinc-300 dark:border-zinc-800 dark:bg-zinc-900 dark:hover:border-zinc-700"
      >
        <div class="flex flex-wrap items-start justify-between gap-2">
          <div class="min-w-0 flex-1">
            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ a.title }}</p>
            <p class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">
              {{ a.subject_name }} · {{ a.teacher_name ? `By ${a.teacher_name}` : '' }}
            </p>
          </div>
          <StatusBadge :status="a.status" type="assignment" />
        </div>
        <div class="mt-2 flex flex-wrap items-center gap-3 text-sm text-zinc-600 dark:text-zinc-400">
          <span>Due: {{ a.due_date || '—' }}</span>
          <span v-if="a.has_attachment" class="inline-flex items-center gap-1">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
            </svg>
            Attachment
          </span>
        </div>
      </router-link>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import { ClipboardList } from 'lucide-vue-next';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import StatusBadge from '../components/StatusBadge.vue';
import { getAssignments } from '../services/homeworkService.js';

const loading = ref(true);
const error = ref(null);
const assignments = ref([]);
const filter = ref('all');

const filterOptions = [
  { value: 'all', label: 'All' },
  { value: 'pending', label: 'Pending' },
  { value: 'overdue', label: 'Overdue' },
  { value: 'submitted', label: 'Submitted' },
  { value: 'graded', label: 'Graded' },
  { value: 'closed', label: 'Closed' },
];

async function fetchAssignments() {
  loading.value = true;
  error.value = null;
  try {
    const res = await getAssignments({ status: filter.value });
    assignments.value = res?.data ?? [];
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load assignments.';
    assignments.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(fetchAssignments);
</script>
