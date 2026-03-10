<template>
  <PageContainer title="My Profile" description="View your profile and academic information.">
    <div v-if="loading" class="py-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="rounded-lg bg-red-50 p-4 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <div class="flex items-start gap-4">
          <div
            v-if="profile?.student?.photo_path"
            class="h-20 w-20 shrink-0 rounded-full bg-zinc-200 bg-cover bg-center dark:bg-zinc-700"
            :style="{ backgroundImage: `url(${profile.student.photo_path})` }"
          />
          <div
            v-else
            class="flex h-20 w-20 shrink-0 items-center justify-center rounded-full bg-zinc-200 text-2xl font-medium text-zinc-500 dark:bg-zinc-700 dark:text-zinc-400"
          >
            {{ (profile?.student?.full_name ?? 'S').charAt(0) }}
          </div>
          <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Basic information</h3>
            <dl class="mt-3 grid gap-x-4 gap-y-2 text-sm sm:grid-cols-2">
              <div><dt class="text-zinc-500 dark:text-zinc-400">Full name</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.full_name ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Admission no.</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.admission_no ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Gender</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.gender ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Date of birth</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.date_of_birth ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Blood group</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.blood_group ?? '—' }}</dd></div>
            </dl>
          </div>
        </div>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Academic information</h3>
        <dl class="mt-3 space-y-2 text-sm">
          <div><dt class="text-zinc-500 dark:text-zinc-400">Class</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.current_assignment?.class_name ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Section</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.current_assignment?.section_name ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Roll no.</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.current_assignment?.roll_no ?? profile?.student?.roll_no ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Academic session</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.current_assignment?.academic_session_name ?? '—' }}</dd></div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Contact information</h3>
        <dl class="mt-3 space-y-2 text-sm">
          <div><dt class="text-zinc-500 dark:text-zinc-400">Email</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.email ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Phone</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.phone ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Present address</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.present_address ?? '—' }}</dd></div>
          <div><dt class="text-zinc-500 dark:text-zinc-400">Permanent address</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ profile?.student?.permanent_address ?? '—' }}</dd></div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Guardian information</h3>
        <div v-if="profile?.guardians?.length" class="mt-3 space-y-4">
          <div
            v-for="(g, i) in profile.guardians"
            :key="i"
            class="rounded-lg border border-zinc-100 p-3 dark:border-zinc-800"
          >
            <dl class="space-y-1 text-sm">
              <div><dt class="text-zinc-500 dark:text-zinc-400">Name</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.name ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Relation</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.relation ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Phone</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.phone ?? '—' }}</dd></div>
              <div><dt class="text-zinc-500 dark:text-zinc-400">Email</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.email ?? '—' }}</dd></div>
              <div v-if="g.address"><dt class="text-zinc-500 dark:text-zinc-400">Address</dt><dd class="font-medium text-zinc-900 dark:text-zinc-100">{{ g.address }}</dd></div>
            </dl>
          </div>
        </div>
        <p v-else class="mt-3 text-sm text-zinc-500 dark:text-zinc-400">No guardian information available.</p>
      </section>

      <div class="flex items-center gap-3">
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          disabled
          title="Coming in a later phase"
        >
          Change password
        </button>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getProfile } from '../services/profileService.js';

const loading = ref(true);
const error = ref(null);
const profile = ref(null);

onMounted(async () => {
  try {
    profile.value = await getProfile();
  } catch (e) {
    error.value = e.response?.data?.message ?? 'Failed to load profile.';
  } finally {
    loading.value = false;
  }
});
</script>
