<template>
  <PageContainer title="Event" description="View event details.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="!event" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      Event not found.
    </div>
    <article v-else class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">{{ event.title }}</h1>
      <p class="mt-1 text-sm text-zinc-500">{{ event.category?.name ?? 'Uncategorized' }} · {{ formatDate(event.start_datetime) }} – {{ formatDate(event.end_datetime) }}</p>
      <p v-if="event.location" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
        <span class="font-medium">Location:</span> {{ event.location }}
      </p>
      <p v-if="event.summary" class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">{{ event.summary }}</p>
      <div v-if="event.description" class="mt-4 prose prose-sm dark:prose-invert max-w-none" v-html="event.description"></div>
      <router-link
        :to="{ name: 'teacher-calendar' }"
        class="mt-6 inline-block rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600 dark:text-zinc-300"
      >
        Back to Calendar
      </router-link>
    </article>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getEvent } from '../services/eventService.js';

const route = useRoute();
const loading = ref(true);
const event = ref(null);

const id = computed(() => route.params.id);

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

async function load() {
  if (!id.value) {
    loading.value = false;
    return;
  }
  try {
    event.value = await getEvent(id.value);
  } catch {
    event.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(load);
</script>
