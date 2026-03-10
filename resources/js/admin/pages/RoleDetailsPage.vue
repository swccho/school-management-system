<template>
  <PageContainer
    title="Role details"
    description="View role and assigned permissions."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading role…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <router-link
          :to="{ name: 'roles' }"
          class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        >
          Back to roles
        </router-link>
        <div class="flex gap-2">
          <router-link
            v-if="!role.is_system"
            :to="{ name: 'roles-edit', params: { id: role.id } }"
            class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          >
            Edit role
          </router-link>
          <router-link
            :to="{ name: 'role-permissions', params: { id: role.id } }"
            class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
          >
            Manage permissions
          </router-link>
        </div>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Role</h3>
        <dl class="mt-4 space-y-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Name</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ role.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Slug</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ role.slug }}</dd>
          </div>
          <div v-if="role.description">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ role.description }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Status</dt>
            <dd>
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="role.status === 'active'
                  ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                  : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
              >
                {{ role.status }}
              </span>
            </dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">System role</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ role.is_system ? 'Yes' : 'No' }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Updated</dt>
            <dd class="text-sm text-zinc-600 dark:text-zinc-400">{{ role.updated_at_formatted ?? '—' }}</dd>
          </div>
        </dl>
      </section>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Permissions ({{ (role.permissions || []).length }})</h3>
        <div class="mt-2 flex flex-wrap gap-2">
          <span
            v-for="p in (role.permissions || [])"
            :key="p.id"
            class="inline-flex rounded-full bg-zinc-100 px-3 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-700 dark:text-zinc-300"
          >
            {{ p.module }} / {{ p.name }}
          </span>
          <span v-if="!(role.permissions || []).length" class="text-sm text-zinc-500 dark:text-zinc-400">No permissions assigned.</span>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getRole } from '../services/rolePermissionService.js';

const route = useRoute();
const role = ref(null);
const loading = ref(true);
const error = ref(null);

const roleId = computed(() => route.params.id);

async function loadRole() {
  if (!roleId.value) return;
  loading.value = true;
  error.value = null;
  try {
    role.value = await getRole(roleId.value);
  } catch {
    error.value = 'Failed to load role.';
    role.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadRole();
});
</script>
