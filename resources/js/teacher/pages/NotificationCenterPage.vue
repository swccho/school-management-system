<template>
  <PageContainer title="Notifications" description="View and manage your notifications.">
    <div class="mb-4 flex justify-end">
      <button
        v-if="list.some((n) => !n.read_at)"
        type="button"
        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm dark:border-zinc-600"
        @click="markAllRead"
      >
        Mark all as read
      </button>
    </div>
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="!list.length" class="rounded-xl border border-zinc-200 bg-white p-8 text-center text-sm text-zinc-500 dark:border-zinc-800 dark:bg-zinc-900">
      No notifications.
    </div>
    <div v-else class="space-y-2">
      <div
        v-for="n in list"
        :key="n.id"
        class="rounded-xl border p-4 transition"
        :class="n.read_at ? 'border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900' : 'border-zinc-300 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/50'"
      >
        <div class="flex items-start justify-between gap-3">
          <div class="min-w-0 flex-1">
            <p class="font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</p>
            <p v-if="n.body" class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ n.body }}</p>
            <p class="mt-1 text-xs text-zinc-500">{{ formatDate(n.created_at) }}</p>
            <div class="mt-2 flex gap-2">
              <router-link
                v-if="n.action_url"
                :to="n.action_url"
                class="text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
              >
                View
              </router-link>
      <button
        v-if="!n.read_at"
        type="button"
        class="min-h-[44px] min-w-[44px] rounded-lg px-2 text-sm text-zinc-500 hover:bg-zinc-100 hover:text-zinc-700 dark:hover:text-zinc-300 dark:hover:bg-zinc-800"
        @click="markOneRead(n.id)"
      >
        Mark as read
      </button>
            </div>
          </div>
          <span v-if="!n.read_at" class="shrink-0 rounded-full bg-blue-600 p-1.5" aria-label="Unread"></span>
        </div>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getNotifications, markAsRead, markAllAsRead } from '../services/notificationService.js';

const loading = ref(true);
const list = ref([]);

function formatDate(iso) {
  if (!iso) return '—';
  return new Date(iso).toLocaleString(undefined, { dateStyle: 'short', timeStyle: 'short' });
}

async function load() {
  loading.value = true;
  try {
    const res = await getNotifications({ per_page: 50 });
    list.value = res.data ?? [];
  } catch {
    list.value = [];
  } finally {
    loading.value = false;
  }
}

async function markOneRead(id) {
  try {
    await markAsRead(id);
    const n = list.value.find((x) => x.id === id);
    if (n) n.read_at = new Date().toISOString();
  } catch {}
}

async function markAllRead() {
  try {
    await markAllAsRead();
    list.value.forEach((n) => { n.read_at = n.read_at || new Date().toISOString(); });
  } catch {}
}

onMounted(load);
</script>
