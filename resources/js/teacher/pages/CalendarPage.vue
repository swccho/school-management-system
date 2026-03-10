<template>
  <PageContainer title="Calendar" description="View school events and important dates.">
    <div class="mb-4 flex flex-wrap gap-4">
      <input v-model="dateFrom" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
      <input v-model="dateTo" type="date" class="rounded-lg border border-zinc-300 px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800" />
      <button type="button" class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white dark:bg-zinc-100 dark:text-zinc-900" @click="load">Load events</button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="events.length === 0" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No events in this range.
    </div>
    <div v-else class="space-y-3">
      <div
        v-for="e in events"
        :key="e.id"
        class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900"
      >
        <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ e.title }}</p>
        <p class="mt-1 text-sm text-zinc-500">{{ formatDate(e.start_datetime) }} – {{ formatDate(e.end_datetime) }}</p>
        <p v-if="e.location" class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ e.location }}</p>
        <p v-if="e.summary" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ e.summary }}</p>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getEvents } from '../services/eventService.js';

const loading = ref(true);
const events = ref([]);
const dateFrom = ref('');
const dateTo = ref('');

function formatDate(iso) {
  if (!iso) return '—';
  const d = new Date(iso);
  return d.toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

async function load() {
  loading.value = true;
  try {
    const params = {};
    if (dateFrom.value) params.date_from = dateFrom.value;
    if (dateTo.value) params.date_to = dateTo.value;
    events.value = await getEvents(params);
  } catch {
    events.value = [];
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  const now = new Date();
  const start = new Date(now.getFullYear(), now.getMonth(), 1);
  const end = new Date(now.getFullYear(), now.getMonth() + 2, 0);
  dateFrom.value = start.toISOString().slice(0, 10);
  dateTo.value = end.toISOString().slice(0, 10);
  load();
});
</script>
