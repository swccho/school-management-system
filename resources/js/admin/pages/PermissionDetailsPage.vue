<template>
  <PageContainer
    title="Permission details"
    description="View permission details."
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading permission…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <router-link
          :to="{ name: 'permissions' }"
          class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        >
          Back to permissions
        </router-link>
        <router-link
          :to="{ name: 'permissions-edit', params: { id: permission.id } }"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        >
          Edit permission
        </router-link>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Permission</h3>
        <dl class="mt-4 space-y-2">
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Module</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ permission.module }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Name</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ permission.name }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Slug</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ permission.slug }}</dd>
          </div>
          <div v-if="permission.description">
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Description</dt>
            <dd class="text-sm text-zinc-900 dark:text-zinc-100">{{ permission.description }}</dd>
          </div>
          <div>
            <dt class="text-xs font-medium text-zinc-500 dark:text-zinc-400">Updated</dt>
            <dd class="text-sm text-zinc-600 dark:text-zinc-400">{{ permission.updated_at_formatted ?? '—' }}</dd>
          </div>
        </dl>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { getPermission } from '../services/rolePermissionService.js';

const route = useRoute();
const permission = ref(null);
const loading = ref(true);
const error = ref(null);

const permissionId = computed(() => route.params.id);

async function loadPermission() {
  if (!permissionId.value) return;
  loading.value = true;
  error.value = null;
  try {
    permission.value = await getPermission(permissionId.value);
  } catch {
    error.value = 'Failed to load permission.';
    permission.value = null;
  } finally {
    loading.value = false;
  }
}

onMounted(() => {
  loadPermission();
});
</script>
