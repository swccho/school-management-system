<template>
  <PageContainer
    title="Manage permissions"
    :description="role ? `Assign permissions to ${role.name}` : 'Assign permissions to role.'"
  >
    <div v-if="loading" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
      Loading…
    </div>
    <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
      {{ error }}
    </div>
    <div v-else class="space-y-6">
      <div class="flex flex-wrap items-center justify-between gap-4">
        <router-link
          :to="role ? { name: 'role-details', params: { id: role.id } } : { name: 'roles' }"
          class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
        >
          Back to {{ role ? 'role' : 'roles' }}
        </router-link>
        <button
          type="button"
          class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
          :disabled="saving"
          @click="save"
        >
          {{ saving ? 'Saving…' : 'Save permissions' }}
        </button>
      </div>

      <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
        <p v-if="saveError" class="mb-4 rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
          {{ saveError }}
        </p>
        <p v-if="saveSuccess" class="mb-4 rounded-lg bg-emerald-50 px-3 py-2 text-sm text-emerald-700 dark:bg-emerald-900/20 dark:text-emerald-400">
          {{ saveSuccess }}
        </p>

        <div class="space-y-6">
          <div
            v-for="group in permissionsByModule"
            :key="group.module"
            class="border-b border-zinc-100 pb-4 last:border-0 last:pb-0 dark:border-zinc-800"
          >
            <h3 class="mb-3 text-sm font-semibold uppercase tracking-wide text-zinc-500 dark:text-zinc-400">
              {{ group.module }}
            </h3>
            <div class="flex flex-wrap gap-x-6 gap-y-2">
              <label
                v-for="p in group.permissions"
                :key="p.id"
                class="inline-flex items-center gap-2"
              >
                <input
                  v-model="selectedIds"
                  type="checkbox"
                  :value="p.id"
                  class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
                />
                <span class="text-sm text-zinc-700 dark:text-zinc-300">{{ p.name }}</span>
              </label>
            </div>
          </div>
        </div>
      </section>
    </div>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { useRoute } from 'vue-router';
import PageContainer from '../components/PageContainer.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { getRole, getPermissions, getRolePermissions, syncRolePermissions } from '../services/rolePermissionService.js';

const route = useRoute();
const toast = useToast();
const role = ref(null);
const permissions = ref([]);
const permissionIds = ref([]);
const selectedIds = ref([]);
const loading = ref(true);
const error = ref(null);
const saving = ref(false);
const saveError = ref(null);
const saveSuccess = ref(null);

const roleId = computed(() => route.params.id);

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

async function loadRole() {
  if (!roleId.value) return null;
  const r = await getRole(roleId.value);
  role.value = r;
  return r;
}

async function loadPermissions() {
  permissions.value = await getPermissions();
}

async function loadRolePermissions() {
  if (!roleId.value) return;
  const { permission_ids } = await getRolePermissions(roleId.value);
  permissionIds.value = permission_ids || [];
  selectedIds.value = [...permissionIds.value];
}

async function load() {
  loading.value = true;
  error.value = null;
  try {
    await Promise.all([loadRole(), loadPermissions()]);
    await loadRolePermissions();
  } catch {
    error.value = 'Failed to load data.';
  } finally {
    loading.value = false;
  }
}

async function save() {
  saveError.value = null;
  saveSuccess.value = null;
  saving.value = true;
  try {
    await syncRolePermissions(roleId.value, selectedIds.value);
    saveSuccess.value = 'Permissions updated successfully.';
    toast.success('Permissions updated successfully.');
  } catch (err) {
    saveError.value = err.response?.data?.message || 'Failed to save permissions.';
    toast.error(saveError.value);
  } finally {
    saving.value = false;
  }
}

onMounted(() => {
  load();
});
</script>
