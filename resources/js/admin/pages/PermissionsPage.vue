<template>
  <PageContainer
    title="Permissions"
    description="View permissions grouped by module."
  >
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading permissions…
      </div>
      <div v-else-if="permissionsByModule.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        No permissions found.
      </div>
      <div v-else class="divide-y divide-zinc-200 dark:divide-zinc-800">
        <section
          v-for="group in permissionsByModule"
          :key="group.module"
          class="p-4 first:pt-4 last:pb-4"
        >
          <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
            {{ group.module }}
          </h3>
          <div class="sidenav-scroll overflow-x-auto">
            <table class="w-full min-w-[400px]">
              <thead>
                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                  <th class="px-3 py-2 text-left text-xs font-medium text-zinc-600 dark:text-zinc-400">Name</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-zinc-600 dark:text-zinc-400">Slug</th>
                  <th class="px-3 py-2 text-left text-xs font-medium text-zinc-600 dark:text-zinc-400">Description</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="p in group.permissions"
                  :key="p.id"
                  class="border-b border-zinc-50 dark:border-zinc-800/50 last:border-0"
                >
                  <td class="px-3 py-2 text-sm text-zinc-900 dark:text-zinc-100">{{ p.name }}</td>
                  <td class="px-3 py-2 text-sm text-zinc-600 dark:text-zinc-400">{{ p.slug }}</td>
                  <td class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-500">{{ p.description ?? '—' }}</td>
                </tr>
              </tbody>
            </table>
          </div>
        </section>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { getPermissions } from '../services/rolePermissionService.js';

const loading = ref(true);
const permissions = ref([]);

const permissionsByModule = computed(() => {
  const byModule = {};
  for (const p of permissions.value) {
    if (!byModule[p.module]) {
      byModule[p.module] = { module: p.module, permissions: [] };
    }
    byModule[p.module].permissions.push(p);
  }
  return Object.values(byModule).sort((a, b) => a.module.localeCompare(b.module));
});

onMounted(async () => {
  try {
    permissions.value = await getPermissions();
  } catch {
    permissions.value = [];
  } finally {
    loading.value = false;
  }
});
</script>
