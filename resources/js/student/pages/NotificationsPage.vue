<template>
  <PageContainer title="Notifications" description="Your recent notifications and alerts.">
    <div class="mb-4 flex items-center justify-between">
      <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ total }} notification(s)</span>
      <button
        v-if="notifications.some(n => !n.read_at)"
        type="button"
        class="rounded-lg px-3 py-1.5 text-sm font-medium text-zinc-600 hover:bg-zinc-100 dark:text-zinc-400 dark:hover:bg-zinc-800"
        :disabled="markAllLoading"
        @click="handleMarkAllRead"
      >
        {{ markAllLoading ? '…' : 'Mark all as read' }}
      </button>
    </div>
    <LoadingSkeleton v-if="loading" variant="table" :rows="8" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <EmptyState
      v-else-if="!notifications.length"
      title="No notifications"
      description="You have no notifications yet. We'll notify you when something new arrives."
    />
    <div v-else class="space-y-2">
      <div
        v-for="n in notifications"
        :key="n.id"
        class="rounded-xl border bg-white dark:bg-zinc-900"
        :class="n.read_at ? 'border-zinc-200 dark:border-zinc-800' : 'border-zinc-200 bg-zinc-50 dark:border-zinc-800 dark:bg-zinc-800/50'"
      >
        <div class="flex items-start justify-between gap-3 p-4">
          <div class="min-w-0 flex-1">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="rounded px-2 py-0.5 text-xs font-medium"
                :class="typeBadgeClass(n.type)"
              >
                {{ typeLabel(n.type) }}
              </span>
              <span v-if="!n.read_at" class="rounded bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/40 dark:text-blue-200">New</span>
            </div>
            <h3 class="mt-1 font-medium text-zinc-900 dark:text-zinc-100">{{ n.title }}</h3>
            <p v-if="n.body" class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">{{ n.body }}</p>
            <p class="mt-2 text-xs text-zinc-500 dark:text-zinc-400">{{ formatDate(n.created_at) }}</p>
            <router-link
              v-if="n.action_url"
              :to="n.action_url"
              class="mt-2 inline-block text-sm font-medium text-blue-600 hover:underline dark:text-blue-400"
            >
              View →
            </router-link>
          </div>
          <button
            v-if="!n.read_at"
            type="button"
            class="shrink-0 rounded px-2 py-1 text-xs font-medium text-zinc-600 hover:bg-zinc-200 dark:text-zinc-400 dark:hover:bg-zinc-700"
            @click="markOneRead(n.id)"
          >
            Mark read
          </button>
        </div>
      </div>
    </div>
    <div v-if="hasMore" class="mt-4 text-center">
      <button
        type="button"
        class="rounded-lg border border-zinc-300 px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800"
        :disabled="loadingMore"
        @click="loadMore"
      >
        {{ loadingMore ? 'Loading…' : 'Load more' }}
      </button>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import EmptyState from '../components/EmptyState.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getNotifications, markAsRead, markAllAsRead } from '../services/notificationService.js';

const loading = ref(true);
const loadingMore = ref(false);
const markAllLoading = ref(false);
const error = ref(null);
const notifications = ref([]);
const meta = ref({});
const page = ref(1);

const total = computed(() => meta.value?.total ?? 0);
const hasMore = computed(() => meta.value?.current_page < meta.value?.last_page);

function typeLabel(type) {
  const labels = {
    notice_published: 'Announcement',
    new_assignment: 'Assignment',
    assignment_reminder: 'Reminder',
    result_published: 'Results',
    exam_schedule: 'Exam',
    fee_due: 'Fee',
  };
  return labels[type] ?? type;
}

function typeBadgeClass(type) {
  return 'bg-zinc-100 text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300';
}

function formatDate(iso) {
  if (!iso) return '';
  return new Date(iso).toLocaleString();
}

async function fetchNotifications(pageNum = 1, append = false) {
  if (append) loadingMore.value = true;
  else loading.value = true;
  error.value = null;
  try {
    const res = await getNotifications({ per_page: 20, page: pageNum });
    const list = res?.data ?? [];
    const newMeta = res?.meta ?? {};
    if (append) {
      notifications.value = [...notifications.value, ...list];
    } else {
      notifications.value = list;
    }
    meta.value = newMeta;
    page.value = pageNum;
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load notifications.';
    if (!append) notifications.value = [];
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
}

async function markOneRead(id) {
  try {
    await markAsRead(id);
    const n = notifications.value.find((x) => x.id === id);
    if (n) n.read_at = new Date().toISOString();
  } catch {
    // ignore
  }
}

async function handleMarkAllRead() {
  markAllLoading.value = true;
  try {
    await markAllAsRead();
    notifications.value.forEach((n) => { n.read_at = n.read_at || new Date().toISOString(); });
  } catch {
    // ignore
  } finally {
    markAllLoading.value = false;
  }
}

function loadMore() {
  if (!hasMore.value || loadingMore.value) return;
  fetchNotifications(page.value + 1, true);
}

onMounted(() => fetchNotifications());
</script>
