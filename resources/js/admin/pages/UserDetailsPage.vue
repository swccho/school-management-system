<template>
  <PageContainer
    title="User details"
    description="View user profile and access information."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading user…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <router-link
          :to="{ name: 'users' }"
          class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        >
          Back to users
        </router-link>
        <router-link
          :to="{ name: 'users-edit', params: { id: user.id } }"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
          Edit user
        </router-link>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Profile</h3>
        <div class="mt-4 flex flex-wrap gap-6">
          <div v-if="user.avatar_url" class="shrink-0">
            <img
              :src="user.avatar_url"
              :alt="user.name"
              class="h-24 w-24 rounded-full object-cover"
            />
          </div>
          <div class="min-w-0 flex-1 space-y-2">
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Name:</span> {{ user.name }}
            </p>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Email:</span> {{ user.email }}
            </p>
            <p v-if="user.username" class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Username:</span> {{ user.username }}
            </p>
            <p v-if="user.phone" class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Phone:</span> {{ user.phone }}
            </p>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Status:</span>
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="user.status === 'active'
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
              >
                {{ user.status }}
              </span>
            </p>
            <p v-if="user.user_type" class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">User type:</span> {{ user.user_type }}
            </p>
            <p class="text-sm text-zinc-600 dark:text-zinc-400">
              <span class="font-medium text-zinc-700 dark:text-zinc-300">Last login:</span> {{ user.last_login_at_formatted ?? '—' }}
            </p>
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Roles</h3>
        <div class="mt-2 flex flex-wrap gap-2">
          <span
            v-for="r in (user.roles || [])"
            :key="r.id"
            class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
          >
            {{ r.name }}
          </span>
          <span v-if="!(user.roles || []).length" class="text-sm text-zinc-500 dark:text-zinc-400">No roles assigned.</span>
        </div>
      </section>

      <section v-if="user.staff || user.student || (user.student_guardians && user.student_guardians.length)" class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Linked entities</h3>
        <ul class="mt-2 list-inside list-disc space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
          <li v-if="user.staff">Staff: {{ user.staff.full_name }}</li>
          <li v-if="user.student">Student: {{ user.student.full_name }}</li>
          <li v-for="g in (user.student_guardians || [])" :key="g.id">Guardian: {{ g.name }}</li>
        </ul>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Metadata</h3>
        <p class="mt-2 text-sm text-zinc-600 dark:text-zinc-400">
          Created: {{ user.created_at_formatted ?? '—' }}
        </p>
        <p class="text-sm text-zinc-600 dark:text-zinc-400">
          Updated: {{ user.updated_at_formatted ?? '—' }}
        </p>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getUser } from '../services/userService.js';

const route = useRoute();
const user = ref(null);
const loading = ref(true);
const error = ref(null);

const userId = computed(() => route.params.id);

async function loadUser() {
  if (!userId.value) return;
  loading.value = true;
  error.value = null;
  try {
    user.value = await getUser(userId.value);
  } catch {
    error.value = 'Failed to load user.';
    user.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadUser();
});
</script>
