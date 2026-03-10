<template>
  <PageContainer
    title="Class Routine"
    :description="data?.class_name && data?.section_name ? `Routine for ${data.class_name} · ${data.section_name}` : 'Your weekly schedule.'"
  >
    <div class="mb-4 flex flex-wrap items-end gap-4">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Day</label>
        <select
          v-model="day"
          class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @change="fetchRoutine"
        >
          <option v-for="d in days" :key="d" :value="d">{{ capitalize(d) }}</option>
        </select>
      </div>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="!items.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
      No routine for this day.
    </div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Period</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Teacher</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Time</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Room</th>
          </tr>
        </thead>
        <tbody>
          <tr
            v-for="r in items"
            :key="r.id"
            class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
          >
            <td class="px-4 py-3 text-sm">{{ r.period_no }}</td>
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ r.subject_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.teacher_name || '—' }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.start_time }} – {{ r.end_time }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.room_label || '—' }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getRoutine } from '../services/routineService.js';

const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
const day = ref(new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase());
const loading = ref(false);
const data = ref(null);

const items = computed(() => data.value?.items ?? []);

function capitalize(s) {
  return s ? s.charAt(0).toUpperCase() + s.slice(1) : '';
}

async function fetchRoutine() {
  loading.value = true;
  try {
    data.value = await getRoutine({ day_of_week: day.value });
  } catch {
    data.value = { items: [] };
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetchRoutine());
</script>
