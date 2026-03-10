<template>
  <PageContainer title="Class Routine" description="Your weekly schedule.">
    <div class="mb-4 flex flex-wrap gap-4">
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Day</label>
        <select v-model="day" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option v-for="d in days" :key="d" :value="d">{{ d }}</option>
        </select>
      </div>
      <div>
        <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Class</label>
        <select v-model="classId" class="mt-1 rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100">
          <option value="">All</option>
          <option v-for="a in uniqueClasses" :key="a.class_id" :value="a.class_id">{{ a.class_name }}</option>
        </select>
      </div>
      <button type="button" class="self-end rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900" @click="fetchRoutine">Apply</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="routine.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">No routine for this day.</div>
    <div v-else class="overflow-x-auto rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <table class="w-full min-w-[400px]">
        <thead>
          <tr class="border-b border-zinc-200 dark:border-zinc-800">
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Period</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Subject</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Class · Section</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Time</th>
            <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Room</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="r in routine" :key="r.id" class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0">
            <td class="px-4 py-3 text-sm">{{ r.period_no }}</td>
            <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ r.subject_name }}</td>
            <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ r.class_name }} · {{ r.section_name }}</td>
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
import { useAuthStore } from '../stores/authStore.js';
import api from '../services/api.js';

const authStore = useAuthStore();
const days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
const day = ref(new Date().toLocaleDateString('en-US', { weekday: 'long' }).toLowerCase());
const classId = ref('');
const loading = ref(false);
const routine = ref([]);

const uniqueClasses = computed(() => {
  const as = authStore.assignments ?? [];
  const seen = new Set();
  return as.filter((a) => {
    const k = a.class_id;
    if (seen.has(k)) return false;
    seen.add(k);
    return true;
  });
});

async function fetchRoutine() {
  loading.value = true;
  try {
    const { data } = await api.get('/routine', { params: { day_of_week: day.value, class_id: classId.value || undefined } });
    routine.value = data ?? [];
  } catch {
    routine.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => fetchRoutine());
</script>
