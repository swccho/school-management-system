<template>
  <PageContainer
    title="Dashboard"
    description="Quick overview of your classes and tasks."
  >
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Today's classes</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ data?.todays_classes?.length ?? 0 }}</p>
          <router-link
            v-if="(data?.todays_classes?.length ?? 0) > 0"
            to="/teacher/routine"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            View routine →
          </router-link>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Assigned subjects</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ data?.assigned_subjects?.length ?? 0 }}</p>
          <router-link
            to="/teacher/my-classes"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            My classes →
          </router-link>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending attendance</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ pendingAttendanceCount }}</p>
          <router-link
            to="/teacher/attendance"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            Take attendance →
          </router-link>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Pending marks entry</h3>
          <p class="mt-1 text-2xl font-semibold text-zinc-900 dark:text-zinc-100">{{ data?.pending_marks_count ?? 0 }}</p>
          <router-link
            to="/teacher/exams"
            class="mt-2 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
          >
            Enter marks →
          </router-link>
        </div>
      </div>

      <div class="grid gap-6 lg:grid-cols-2">
        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Today's classes</h3>
          <ul v-if="data?.todays_classes?.length" class="mt-3 space-y-2">
            <li
              v-for="c in data.todays_classes"
              :key="c.id"
              class="flex items-center justify-between rounded-lg border border-zinc-100 py-2 px-3 dark:border-zinc-800"
            >
              <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ c.subject_name }}</span>
              <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ c.class_name }} {{ c.section_name }} · {{ c.start_time }}–{{ c.end_time }}</span>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No classes scheduled for today.</p>
        </section>

        <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent notices</h3>
          <ul v-if="data?.recent_notices?.length" class="mt-3 space-y-2">
            <li
              v-for="n in data.recent_notices"
              :key="n.id"
              class="rounded-lg border border-zinc-100 py-2 px-3 dark:border-zinc-800"
            >
              <router-link :to="'/teacher/notices'" class="font-medium text-zinc-900 hover:underline dark:text-zinc-100">
                {{ n.title }}
              </router-link>
              <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ n.publish_date }}</p>
            </li>
          </ul>
          <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No recent notices.</p>
        </section>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Upcoming events</h3>
        <ul v-if="data?.upcoming_events?.length" class="mt-3 space-y-2">
          <li
            v-for="e in data.upcoming_events"
            :key="e.id"
            class="flex items-center justify-between rounded-lg border border-zinc-100 py-2 px-3 dark:border-zinc-800"
          >
            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ e.title }}</span>
            <span class="text-sm text-zinc-500 dark:text-zinc-400">{{ formatDate(e.start_datetime) }}{{ e.location ? ` · ${e.location}` : '' }}</span>
          </li>
        </ul>
        <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No upcoming events.</p>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getDashboard } from '../services/dashboardService.js';

const loading = ref(true);
const error = ref(null);
const data = ref(null);

const pendingAttendanceCount = computed(() => {
  const list = data.value?.pending_attendance ?? [];
  return list.filter((p) => p.status === 'not_taken' || p.status === 'draft').length;
});

function formatDate(iso) {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleDateString(undefined, { dateStyle: 'short', timeStyle: 'short' });
  } catch {
    return iso;
  }
}

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
