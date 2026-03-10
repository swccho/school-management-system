<template>
  <form class="space-y-6" @submit.prevent="handleSubmit">
    <p
      v-if="formError"
      class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
    >
      {{ formError }}
    </p>

    <section class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-800 dark:bg-zinc-900">
      <h3 class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">Permission</h3>
      <div class="mt-4 space-y-4">
        <div>
          <label for="perm-module" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Module <span class="text-red-500">*</span></label>
          <input
            id="perm-module"
            v-model="form.module"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="perm-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
          <input
            id="perm-name"
            v-model="form.name"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="perm-slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug <span class="text-red-500">*</span></label>
          <input
            id="perm-slug"
            v-model="form.slug"
            type="text"
            required
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
        <div>
          <label for="perm-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
          <textarea
            id="perm-description"
            v-model="form.description"
            rows="2"
            class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
          />
        </div>
      </div>
    </section>

    <div class="flex justify-end gap-2">
      <router-link
        :to="{ name: 'permissions' }"
        class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
      >
        Cancel
      </router-link>
      <button
        type="submit"
        class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
        :disabled="submitting"
      >
        {{ submitting ? 'Saving…' : (permission ? 'Update permission' : 'Create permission') }}
      </button>
    </div>
  </form>
</template>

<script setup>
import { ref, watch } from 'vue';
import { createPermission, updatePermission } from '../services/rolePermissionService.js';

const props = defineProps({
  permission: { type: Object, default: null },
});

const emit = defineEmits(['saved']);

const form = ref({
  module: '',
  name: '',
  slug: '',
  description: '',
});
const formError = ref(null);
const submitting = ref(false);

watch(() => props.permission, (p) => {
  if (p) {
    form.value = {
      module: p.module ?? '',
      name: p.name ?? '',
      slug: p.slug ?? '',
      description: p.description ?? '',
    };
  } else {
    form.value = { module: '', name: '', slug: '', description: '' };
  }
  formError.value = null;
}, { immediate: true });

async function handleSubmit() {
  formError.value = null;
  submitting.value = true;
  try {
    if (props.permission) {
      await updatePermission(props.permission.id, form.value);
    } else {
      await createPermission(form.value);
    }
    emit('saved');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = errors ? Object.values(errors).flat()[0] : msg || 'Failed to save permission.';
  } finally {
    submitting.value = false;
  }
}
</script>
