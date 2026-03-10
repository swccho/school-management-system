<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <p
      v-if="formError"
      class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ formError }}
    </p>

    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Role</h3>
      <div class="mt-4 space-y-4">
        <div>
          <label for="role-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
          <input
            id="role-name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="role-slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug <span class="text-red-500">*</span></label>
          <input
            id="role-slug"
            v-model="form.slug"
            type="text"
            required
            :readonly="isSystemRole"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            :class="{ 'bg-zinc-100 dark:bg-zinc-800/50': isSystemRole }"
          />
          <p v-if="isSystemRole" class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">System role slug cannot be changed.</p>
        </div>
        <div>
          <label for="role-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
          <textarea
            id="role-description"
            v-model="form.description"
            rows="2"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="role-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
          <select
            id="role-status"
            v-model="form.status"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          >
            <option value="active">Active</option>
            <option value="inactive">Inactive</option>
          </select>
        </div>
      </div>
    </section>

    <div class="flex justify-end gap-2">
      <router-link
        :to="{ name: 'roles' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Cancel
      </router-link>
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        :disabled="submitting"
      >
        {{ submitting ? 'Saving…' : (role ? 'Update role' : 'Create role') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { createRole, updateRole } from '../services/rolePermissionService.js';

const props = defineProps({
  role: { type: Object, default: null },
});

const emit = defineEmits(['saved']);

const isSystemRole = computed(() => !!props.role?.is_system);

const form = ref({
  name: '',
  slug: '',
  description: '',
  status: 'active',
});
const formError = ref(null);
const submitting = ref(false);

watch(() => props.role, (r) => {
  if (r) {
    form.value = {
      name: r.name ?? '',
      slug: r.slug ?? '',
      description: r.description ?? '',
      status: r.status ?? 'active',
    };
  } else {
    form.value = { name: '', slug: '', description: '', status: 'active' };
  }
  formError.value = null;
}, { immediate: true });

async function handleSubmit() {
  formError.value = null;
  submitting.value = true;
  try {
    if (props.role) {
      await updateRole(props.role.id, form.value);
    } else {
      await createRole(form.value);
    }
    emit('saved');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = errors ? Object.values(errors).flat()[0] : msg || 'Failed to save role.';
  } finally {
    submitting.value = false;
  }
}
</script>
