<template>
  <PageContainer
    title="Users"
    description="Manage system users. Create, edit, assign roles, and control access."
  >
    <template #actions>
      <router-link
        :to="{ name: 'users-create' }"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
      >
        Add User
      </router-link>
    </template>

    <div class="mb-4 flex flex-wrap items-end gap-4 rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-800 dark:bg-zinc-900">
      <div class="min-w-[180px]">
        <label for="filter-search" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Search</label>
        <input
          id="filter-search"
          v-model="filters.search"
          type="text"
          placeholder="Search name, email, username…"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          @keyup.enter="applyFilters"
        />
      </div>
      <div class="min-w-[160px]">
        <SearchableSelect
          id="filter-role"
          v-model="filters.role_id"
          label="Role"
          :options="rolesOptions"
          label-key="name"
          value-key="id"
          placeholder="All"
          search-placeholder="Search roles…"
          clearable
        />
      </div>
      <div class="min-w-[120px]">
        <label for="filter-user-type" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">User type</label>
        <select
          id="filter-user-type"
          v-model="filters.user_type"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="admin">Admin</option>
          <option value="teacher">Teacher</option>
          <option value="staff">Staff</option>
          <option value="student">Student</option>
          <option value="guardian">Guardian</option>
        </select>
      </div>
      <div class="min-w-[120px]">
        <label for="filter-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
        <select
          id="filter-status"
          v-model="filters.status"
          class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
        >
          <option value="">All</option>
          <option value="active">Active</option>
          <option value="inactive">Inactive</option>
        </select>
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-created-from"
          v-model="filters.created_from"
          label="Created from"
          placeholder="From"
          clearable
        />
      </div>
      <div class="min-w-[140px]">
        <DatePicker
          id="filter-created-to"
          v-model="filters.created_to"
          label="Created to"
          placeholder="To"
          clearable
        />
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
        Loading users…
      </div>
      <div v-else-if="error" class="p-8 text-center text-sm text-red-600 dark:text-red-400">
        {{ error }}
      </div>
      <div v-else-if="users.length === 0" class="p-8 text-center text-sm text-zinc-500 dark:text-zinc-400">
        {{ hasActiveFilters ? 'No users match your filters.' : 'No users yet. Add one to get started.' }}
      </div>
      <div v-else class="sidenav-scroll overflow-x-auto">
        <table class="w-full min-w-[800px]">
          <thead>
            <tr class="border-b border-zinc-200 dark:border-zinc-800">
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Name</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Email / Username</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Role(s)</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">User type</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Last login</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Created</th>
              <th class="px-4 py-3 text-left text-sm font-medium text-zinc-700 dark:text-zinc-300">Actions</th>
            </tr>
          </thead>
          <tbody>
            <tr
              v-for="u in users"
              :key="u.id"
              class="border-b border-zinc-100 dark:border-zinc-800/50 last:border-0"
            >
              <td class="px-4 py-3 text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ u.name }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ u.email }}{{ u.username ? ` / ${u.username}` : '' }}
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">
                {{ (u.roles || []).map(r => r.name).join(', ') || '—' }}
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ u.user_type ?? '—' }}</td>
              <td class="px-4 py-3">
                <span
                  class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                  :class="u.status === 'active'
                    ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300'
                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
                >
                  {{ u.status }}
                </span>
              </td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ u.last_login_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3 text-sm text-zinc-600 dark:text-zinc-400">{{ u.created_at_formatted ?? '—' }}</td>
              <td class="px-4 py-3">
                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                  <router-link
                    :to="{ name: 'user-details', params: { id: u.id } }"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    View
                  </router-link>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <router-link
                    :to="{ name: 'users-edit', params: { id: u.id } }"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                  >
                    Edit
                  </router-link>
                  <template v-if="u.status === 'active' && u.id !== currentUserId">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="confirmDeactivate(u)"
                    >
                      Deactivate
                    </button>
                  </template>
                  <template v-if="u.status === 'inactive'">
                    <span class="text-zinc-300 dark:text-zinc-600">|</span>
                    <button
                      type="button"
                      class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                      @click="confirmActivate(u)"
                    >
                      Activate
                    </button>
                  </template>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    type="button"
                    class="text-sm font-medium text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100"
                    @click="openResetPassword(u)"
                  >
                    Reset password
                  </button>
                  <span class="text-zinc-300 dark:text-zinc-600">|</span>
                  <button
                    v-if="u.id !== currentUserId"
                    type="button"
                    class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                    @click="confirmDelete(u)"
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

    <Teleport to="body">
      <div
        v-if="resetPasswordUser"
        class="fixed inset-0 z-50 flex items-center justify-center p-4"
        role="dialog"
        aria-modal="true"
        @keydown.escape="resetPasswordUser = null"
      >
        <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" @click="resetPasswordUser = null" />
        <div
          class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
          @click.stop
        >
          <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Reset password</h2>
          <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
            Set a new password for {{ resetPasswordUser.name }}.
          </p>
          <form class="mt-4 space-y-4" @submit.prevent="submitResetPassword">
            <p v-if="resetPasswordError" class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400">
              {{ resetPasswordError }}
            </p>
            <div>
              <label for="reset-password" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">New password <span class="text-red-500">*</span></label>
              <input
                id="reset-password"
                v-model="resetPasswordForm.password"
                type="password"
                required
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="reset-password-confirm" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Confirm password <span class="text-red-500">*</span></label>
              <input
                id="reset-password-confirm"
                v-model="resetPasswordForm.password_confirmation"
                type="password"
                required
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div class="flex justify-end gap-2">
              <button
                type="button"
                class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300"
                @click="resetPasswordUser = null"
              >
                Cancel
              </button>
              <button
                type="submit"
                class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900"
                :disabled="resetPasswordSubmitting"
              >
                {{ resetPasswordSubmitting ? 'Saving…' : 'Reset password' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </Teleport>
  </PageContainer>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import PageContainer from '../components/PageContainer.vue';
import UserForm from '../components/UserForm.vue';
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import DatePicker from '../../shared/components/form/DatePicker.vue';
import { useConfirmation } from '../../shared/composables/useConfirmation.js';
import { useToast } from '../../shared/composables/useToast.js';
import { useAuthStore } from '../stores/authStore.js';
import {
  getUsers,
  deleteUser,
  activateUser,
  deactivateUser,
  resetUserPassword,
  getRolesOptions,
} from '../services/userService.js';

const authStore = useAuthStore();
const { openConfirmation, setConfirmationLoading, closeConfirmation } = useConfirmation();
const toast = useToast();

const currentUserId = computed(() => authStore.user?.id ?? null);

function initialFilters() {
  return {
    search: '',
    role_id: null,
    user_type: '',
    status: '',
    created_from: '',
    created_to: '',
  };
}

const loading = ref(true);
const error = ref(null);
const users = ref([]);
const rolesOptions = ref([]);
const filters = ref(initialFilters());
const resetPasswordUser = ref(null);
const resetPasswordForm = ref({ password: '', password_confirmation: '' });
const resetPasswordError = ref(null);
const resetPasswordSubmitting = ref(false);

const hasActiveFilters = computed(() => {
  const f = filters.value;
  return !!(f.search?.trim() || f.role_id || f.user_type || f.status || f.created_from || f.created_to);
});

function buildParams() {
  const f = filters.value;
  const params = {};
  if (f.search?.trim()) params.search = f.search.trim();
  if (f.role_id) params.role_id = f.role_id;
  if (f.user_type) params.user_type = f.user_type;
  if (f.status) params.status = f.status;
  if (f.created_from) params.created_from = f.created_from;
  if (f.created_to) params.created_to = f.created_to;
  return params;
}

function applyFilters() {
  fetchUsers();
}

function resetFilters() {
  filters.value = initialFilters();
  fetchUsers();
}

async function fetchUsers() {
  loading.value = true;
  error.value = null;
  try {
    users.value = await getUsers(buildParams());
  } catch {
    error.value = 'Failed to load users.';
    users.value = [];
  } finally {
    loading.value = false;
  }
}

async function loadRoles() {
  try {
    rolesOptions.value = await getRolesOptions();
  } catch {
    rolesOptions.value = [];
  }
}

async function confirmDelete(u) {
  const confirmed = await openConfirmation({
    title: 'Delete user',
    message: `Are you sure you want to delete "${u.name}"? This cannot be undone.`,
    confirmLabel: 'Delete',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deleteUser(u.id);
    await fetchUsers();
    closeConfirmation();
    toast.success('User deleted successfully.');
  } catch (err) {
    closeConfirmation();
    toast.error(err.response?.data?.message || 'Failed to delete user.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function confirmActivate(u) {
  const confirmed = await openConfirmation({
    title: 'Activate user',
    message: `Activate "${u.name}"? They will be able to log in again.`,
    confirmLabel: 'Activate',
    cancelLabel: 'Cancel',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await activateUser(u.id);
    await fetchUsers();
    closeConfirmation();
    toast.success('User activated successfully.');
  } catch {
    closeConfirmation();
    toast.error('Failed to activate user.');
  } finally {
    setConfirmationLoading(false);
  }
}

async function confirmDeactivate(u) {
  const confirmed = await openConfirmation({
    title: 'Deactivate user',
    message: `Deactivate "${u.name}"? They will not be able to log in until activated again.`,
    confirmLabel: 'Deactivate',
    cancelLabel: 'Cancel',
    variant: 'destructive',
  });
  if (!confirmed) return;
  setConfirmationLoading(true);
  try {
    await deactivateUser(u.id);
    await fetchUsers();
    closeConfirmation();
    toast.success('User deactivated successfully.');
  } catch (err) {
    closeConfirmation();
    toast.error(err.response?.data?.message || 'Failed to deactivate user.');
  } finally {
    setConfirmationLoading(false);
  }
}

function openResetPassword(u) {
  resetPasswordUser.value = u;
  resetPasswordForm.value = { password: '', password_confirmation: '' };
  resetPasswordError.value = null;
}

async function submitResetPassword() {
  if (!resetPasswordUser.value) return;
  if (resetPasswordForm.value.password !== resetPasswordForm.value.password_confirmation) {
    resetPasswordError.value = 'Passwords do not match.';
    return;
  }
  if (resetPasswordForm.value.password.length < 8) {
    resetPasswordError.value = 'Password must be at least 8 characters.';
    return;
  }
  resetPasswordSubmitting.value = true;
  resetPasswordError.value = null;
  try {
    await resetUserPassword(resetPasswordUser.value.id, resetPasswordForm.value);
    resetPasswordUser.value = null;
    toast.success('Password reset successfully.');
  } catch (err) {
    resetPasswordError.value = err.response?.data?.errors?.password?.[0] || err.response?.data?.message || 'Failed to reset password.';
  } finally {
    resetPasswordSubmitting.value = false;
  }
}

onMounted(() => {
  loadRoles();
  fetchUsers();
});
</script>
