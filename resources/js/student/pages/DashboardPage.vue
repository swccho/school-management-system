<template>
  <PageContainer
    title="Dashboard"
    :description="data?.welcome ? `${data.welcome} ${data.school_name ? `— ${data.school_name}` : ''}` : 'Your overview.'"
  >
    <LoadingSkeleton v-if="loading" variant="cards" />
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Class</h3>
          <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ data?.student_summary?.class ?? '—' }} {{ data?.student_summary?.section ?? '' }}
          </p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Roll no.</h3>
          <p class="mt-1 text-lg font-semibold text-zinc-900 dark:text-zinc-100">
            {{ data?.student_summary?.roll_no ?? '—' }}
          </p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Today's classes</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ data?.today_routine?.length ?? 0 }}
          </p>
          <router-link
            v-if="(data?.today_routine?.length ?? 0) > 0"
            to="/student/routine"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            View routine →
          </router-link>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Recent announcements</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">
            {{ data?.recent_announcements?.length ?? 0 }}
          </p>
          <router-link
            to="/student/announcements"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            View all →
          </router-link>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Today's routine</h3>
          <ul v-if="data?.today_routine?.length" class="mt-3 space-y-2">
            <li
              v-for="(r, i) in data.today_routine"
              :key="i"
              class="flex items-center justify-between rounded-lg border border-zinc-100 py-2 px-3 dark:border-zinc-800"
            >
              <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ r.subject }}</span>
              <span class="text-sm text-zinc-500 dark:text-zinc-400">
                {{ r.start_time }}–{{ r.end_time }}{{ r.room_label ? ` · ${r.room_label}` : '' }}
              </span>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No classes scheduled for today.</p>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent announcements</h3>
          <ul v-if="data?.recent_announcements?.length" class="mt-3 space-y-2">
            <li
              v-for="n in data.recent_announcements"
              :key="n.id"
              class="rounded-lg border border-zinc-100 py-2 px-3 dark:border-zinc-800"
            >
              <router-link
                :to="`/student/announcements/${n.id}`"
                class="font-medium text-zinc-900 hover:underline dark:text-zinc-100"
              >
                {{ n.title }}
              </router-link>
              <p v-if="n.excerpt" class="mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">{{ n.excerpt }}</p>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ n.publish_date }}</p>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No recent announcements.</p>
        </section>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import LoadingSkeleton from '../components/LoadingSkeleton.vue';
import { getDashboard } from '../services/dashboardService.js';

const loading = ref(true);
const error = ref(null);
const data = ref(null);

onMounted(async () => {
  try {
    data.value = await getDashboard();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load dashboard.';
  } finally {
    loading.value = false;
  }
});
</script>
