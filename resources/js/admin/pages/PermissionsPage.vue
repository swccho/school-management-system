<template>
  <PageContainer
    title="Permissions"
    description="View and manage permissions by module."
  >
    <template #actions>
      <router-link
        :to="{ name: 'permissions-create' }"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Add Permission
      </router-link>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search name, slug, module…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[160px]">
        <label for="filter-module" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Module</label>
        <select
          id="filter-module"
          v-model="filters.module"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option v-for="m in moduleOptions" :key="m" :value="m">{{ m }}</option>
        </select>
      </div>
      <div class="flex gap-2">
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          @click="applyFilters"
        >
          Apply Filters
        </button>
        <button
          type="button"
          class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
          @click="resetFilters"
        >
          Reset
        </button>
      </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-800 dark:bg-zinc-900">
      <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        Loading permissions…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="permissions.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No permissions match your filters.' : 'No permissions found.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[600px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Module</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="p in permissions"
              :key="p.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ p.module }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ p.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ p.slug }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ p.description ?? '—' }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                  <router-link
                    :to="{ name: 'permission-details', params: { id: p.id } }"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <router-link
                    :to="{ name: 'permissions-edit', params: { id: p.id } }"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    Edit
                  </router-link>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(p)"
                  >
                    Delete
                  </button>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { getPermissions, deletePermission } from '../services/rolePermissionService.js';

const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

function initialFilters() {
  return { search: '', module: '' };
}

const loading = ref(true);
const error = ref(null);
const permissions = ref([]);
const filters = ref(initialFilters());

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(f.search?.trim() || f.module);
});

const moduleOptions = computed(() => {
  const set = new Set(permissions.value.map((p) => p.module));
  return Array.from(set).sort();
});

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.module) params.module = f.module;
  return params;
}

function applyFilters() {
  fetchPermissions();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchPermissions();
}

async function fetchPermissions() {
  loading.value = true;
  error.value = null;
  try {
    permissions.value = await getPermissions(buildParams());
  } catch {
    error.value = 'Failed to load permissions.';
    permissions.value = [];
  } finally {
    loading.value = false;
  }
}

async function confirmDelete(p) {
  const confirmed = await openConfirmation({
    title: 'Delete permission',
    message: `Are you sure you want to delete "${p.name}"? This may affect role assignments.`,
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deletePermission(p.id);
    await fetchPermissions();
    closeConfirmation();
    toast.success('Permission deleted successfully.');
  } catch (err) {
    closeConfirmation();
    toast.error(err.response?.data?.message || 'Failed to delete permission.');
  } finally {
    setConfirmationLoading(false);
  }
}

onMounted(() => {
  fetchPermissions();
});
</script>
