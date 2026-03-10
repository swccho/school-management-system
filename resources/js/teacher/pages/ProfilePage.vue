<template>
  <PageContainer title="My Profile" description="View and update your profile.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500">Loading…</div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">{{ error }}</div>
    <div v-else class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Personal</h3>
        <dl class="mt-3 space-y-2 text-sm">
          <div><dt class="text-zinc-500 dark:text-zinc-400">Name</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.staff?.full_name ?? authStore.user?.name }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Email</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.user?.email }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Designation</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.staff?.designation?.name ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Department</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.staff?.department?.name ?? '—' }}</dd></div>
        </dl>
      </section>
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Teacher</h3>
        <dl class="mt-3 space-y-2 text-sm">
          <div><dt class="text-zinc-500 dark:text-zinc-400">Teacher code</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.teacher?.teacher_code ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Assigned classes</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ authStore.assignments?.length ?? 0 }} assignment(s)</dd></div>
        </dl>
        <router-link to="/teacher/my-classes" class="mt-3 inline-block text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400">View my classes →</router-link>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useAuthStore } from '../stores/authStore.js';
import api from '../services/api.js';

const authStore = useAuthStore();
const loading = ref(false);
const error = ref(null);

onMounted(async () => {
  if (!authStore.user) {
    loading.value = true;
    try {
      await authStore.fetchMe();
    } catch (e) {
      error.value = e.response?.data?.message ?? 'Failed to load profile.';
    } finally {
      loading.value = false;
    }
  }
});
</script>
