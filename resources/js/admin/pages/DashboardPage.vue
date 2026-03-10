<template>
  <PageContainer title="Dashboard" description="Overview of your school management system.">
    <div v-if="loading" class="space-y-8">
      <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div
          v-for="i in 6"
          :key="i"
          class="h-24 animate-pulse rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800"
        />
      </div>
      <div class="grid gap-6 lg:grid-cols-3">
        <div class="h-32 animate-pulse rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800" />
        <div class="h-32 animate-pulse rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800" />
        <div class="h-32 animate-pulse rounded-xl border border-zinc-200 bg-zinc-100 dark:border-zinc-800 dark:bg-zinc-800" />
      </div>
    </div>

    <div v-else-if="error" class="rounded-xl border border-red-200 bg-red-50 p-6 dark:border-red-900/50 dark:bg-red-900/20">
      <p class="text-sm text-red-700 dark:text-red-400">{{ error }}</p>
      <button
        type="button"
        class="mt-3 rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-medium text-red-700 hover:bg-red-50 dark:border-red-800 dark:bg-red-900/30 dark:text-red-300 dark:hover:bg-red-900/40"
        @click="fetch"
      >
        Retry
      </button>
    </div>

    <div v-else class="space-y-8">
      <section>
        <h3 class="mb-4 text-sm font-medium text-zinc-700 dark:text-zinc-300">Summary</h3>
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
          <StatCard
            title="Total Students"
            :value="data?.summary_cards?.total_students ?? 0"
          />
          <StatCard
            title="Total Teachers"
            :value="data?.summary_cards?.total_teachers ?? 0"
          />
          <StatCard
            title="Total Staff"
            :value="data?.summary_cards?.total_staff ?? 0"
          />
          <StatCard
            title="Active Session"
            :value="data?.academic_overview?.current_session?.name ?? '—'"
          />
          <StatCard
            title="Today's Attendance"
            :value="todayAttendanceText"
            :supporting-text="data?.attendance_overview?.total_marked != null ? `${data.attendance_overview.total_marked} marked` : ''"
          />
          <StatCard
            title="Published Notices"
            :value="data?.summary_cards?.published_notices_count ?? 0"
          />
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Academic overview</h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Current session</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ data?.academic_overview?.current_session?.name ?? '—' }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Classes</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.summary_cards?.total_classes ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Sections</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.academic_overview?.total_sections ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Subjects</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.academic_overview?.total_subjects ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Teacher assignments</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.academic_overview?.teacher_assignments_count ?? 0 }}</dd>
            </div>
          </dl>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Attendance today</h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Present</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.attendance_overview?.present ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Absent</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.attendance_overview?.absent ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Late</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.attendance_overview?.late ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Leave</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.attendance_overview?.leave ?? 0 }}</dd>
            </div>
            <div class="flex justify-between border-t border-zinc-200 pt-2 dark:border-zinc-700">
              <dt class="text-zinc-500 dark:text-zinc-400">Total marked</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.attendance_overview?.total_marked ?? 0 }}</dd>
            </div>
          </dl>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Exams & results</h3>
          <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Active exams</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.exam_overview?.active_exams_count ?? 0 }}</dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Latest exam</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">
                {{ data?.exam_overview?.latest_exam?.name ?? '—' }}
              </dd>
            </div>
            <div class="flex justify-between">
              <dt class="text-zinc-500 dark:text-zinc-400">Results generated</dt>
              <dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ data?.exam_overview?.results_generated_count ?? 0 }}</dd>
            </div>
          </dl>
        </div>
      </section>

      <section class="grid gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Recent activity</h3>
          <div class="mt-3 space-y-4">
            <div>
              <h4 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Latest students</h4>
              <ul v-if="recentStudents.length" class="mt-1 space-y-1 text-sm">
                <li v-for="s in recentStudents" :key="s.id">
                  <router-link
                    :to="{ name: 'student-details', params: { id: s.id } }"
                    class="text-zinc-700 hover:underline dark:text-zinc-300"
                  >
                    {{ s.label }}
                  </router-link>
                  <span class="ml-1 text-zinc-500 dark:text-zinc-400">{{ formatDate(s.created_at) }}</span>
                </li>
              </ul>
              <p v-else class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">No recent students.</p>
            </div>
            <div>
              <h4 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Latest notices</h4>
              <ul v-if="recentNotices.length" class="mt-1 space-y-1 text-sm">
                <li v-for="n in recentNotices" :key="n.id">
                  <router-link
                    :to="{ name: 'notices' }"
                    class="text-zinc-700 hover:underline dark:text-zinc-300"
                  >
                    {{ n.label }}
                  </router-link>
                  <span class="ml-1 text-zinc-500 dark:text-zinc-400">{{ formatDate(n.created_at) }}</span>
                </li>
              </ul>
              <p v-else class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">No recent notices.</p>
            </div>
            <div>
              <h4 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Latest exams</h4>
              <ul v-if="recentExams.length" class="mt-1 space-y-1 text-sm">
                <li v-for="e in recentExams" :key="e.id">
                  <router-link
                    :to="{ name: 'exam-details', params: { id: e.id } }"
                    class="text-zinc-700 hover:underline dark:text-zinc-300"
                  >
                    {{ e.label }}
                  </router-link>
                  <span class="ml-1 text-zinc-500 dark:text-zinc-400">{{ formatDate(e.created_at) }}</span>
                </li>
              </ul>
              <p v-else class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">No recent exams.</p>
            </div>
            <div>
              <h4 class="text-xs font-medium uppercase tracking-wide text-zinc-500 dark:text-zinc-400">Recent activity logs</h4>
              <ul v-if="recentLogs.length" class="mt-1 space-y-1 text-sm">
                <li v-for="l in recentLogs" :key="l.id" class="text-zinc-700 dark:text-zinc-300">
                  {{ l.label }}
                  <span v-if="l.user_name" class="text-zinc-500 dark:text-zinc-400"> — {{ l.user_name }}</span>
                  <span class="ml-1 text-zinc-500 dark:text-zinc-400">{{ formatDate(l.created_at) }}</span>
                </li>
              </ul>
              <p v-else class="mt-1 text-sm text-zinc-500 dark:text-zinc-400">No recent activity.</p>
            </div>
          </div>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-800 dark:bg-zinc-900">
          <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Quick actions</h3>
          <ul class="mt-3 space-y-2">
            <li>
              <router-link
                :to="{ name: 'students' }"
                class="block rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
              >
                Add Student
              </router-link>
            </li>
            <li>
              <router-link
                :to="{ name: 'take-attendance' }"
                class="block rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
              >
                Take Attendance
              </router-link>
            </li>
            <li>
              <router-link
                :to="{ name: 'exams-create' }"
                class="block rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
              >
                Create Exam
              </router-link>
            </li>
            <li>
              <router-link
                :to="{ name: 'notices' }"
                class="block rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
              >
                Create Notice
              </router-link>
            </li>
            <li>
              <router-link
                :to="{ name: 'academic-sessions' }"
                class="block rounded-lg border border-zinc-200 px-3 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-700 dark:text-zinc-300 dark:hover:bg-zinc-800"
              >
                Manage Sessions
              </router-link>
            </li>
          </ul>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import StatCard from '../components/StatCard.vue';
import { getDashboardData } from '../services/dashboardService.js';

const loading = ref(true);
const error = ref(null);
const data = ref(null);

const recentStudents = computed(() => data.value?.recent_activity?.latest_students ?? []);
const recentNotices = computed(() => data.value?.recent_activity?.latest_notices ?? []);
const recentExams = computed(() => data.value?.recent_activity?.latest_exams ?? []);
const recentLogs = computed(() => data.value?.recent_activity?.latest_activity_logs ?? []);

const todayAttendanceText = computed(() => {
  const o = data.value?.attendance_overview;
  if (!o || o.total_marked == null) return '—';
  if (o.total_marked === 0) return '0 marked';
  return `${o.present ?? 0} present`;
});

function formatDate(iso) {
  if (!iso) return '';
  try {
    return new Date(iso).toLocaleDateString(undefined, {
      year: 'numeric',
      month: 'short',
      day: 'numeric',
    });
  } catch {
    return iso;
  }
}

async function fetch() {
  loading.value = true;
  error.value = null;
  try {
    data.value = await getDashboardData();
  } catch (err) {
    error.value = err.response?.data?.message || 'Failed to load dashboard.';
    data.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(fetch);
</script>
