<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative max-h-[90vh] w-full max-w-lg overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-category-title' : 'add-category-title'"
      >
        <h2
          :id="isEdit ? 'edit-category-title' : 'add-category-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit category' : 'Add category' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="cat-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Name <span class="text-red-500">*</span></label>
            <input
              id="cat-name"
              v-model="form.name"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              @input="maybeSlugFromName"
            />
          </div>

          <div>
            <label for="cat-slug" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Slug <span class="text-red-500">*</span></label>
            <input
              id="cat-slug"
              v-model="form.slug"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="e.g. getting-started"
            />
          </div>

          <div>
            <label for="cat-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="cat-description"
              v-model="form.description"
              rows="2"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="cat-icon" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Icon</label>
            <input
              id="cat-icon"
              v-model="form.icon"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="e.g. book, help"
            />
            <p v-if="form.icon" class="mt-2 flex items-center gap-2 text-xs text-zinc-500 dark:text-zinc-400">
              <span class="rounded border border-zinc-200 bg-zinc-50 px-2 py-1 dark:border-zinc-700 dark:bg-zinc-800">Preview:</span>
              <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ form.icon }}</span>
            </p>
          </div>

          <div>
            <label for="cat-sort-order" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</label>
            <input
              id="cat-sort-order"
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <label for="cat-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="cat-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
            <p class="mt-1.5 flex items-center gap-2 text-xs">
              <span
                class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                :class="form.status === 'active' ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300'"
              >
                {{ form.status === 'active' ? 'Active — shown in Help' : 'Inactive — hidden' }}
              </span>
            </p>
          </div>

          <div class="flex justify-end gap-2 pt-2">
            <button
              type="button"
              class="rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm font-medium text-zinc-700 hover:bg-zinc-50 dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-300 dark:hover:bg-zinc-700"
              @click="emit('close')"
            >
              Cancel
            </button>
            <button
              type="submit"
              class="rounded-lg border border-zinc-900 bg-zinc-900 px-4 py-2 text-sm font-medium text-white hover:bg-zinc-800 disabled:opacity-50 dark:border-zinc-100 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-200"
              :disabled="saving"
            >
              <span v-if="saving">{{ isEdit ? 'Saving…' : 'Creating…' }}</span>
              <span v-else>{{ isEdit ? 'Save' : 'Create' }}</span>
            </button>
          </div>
        </form>
      </div>
    </div>
  </Teleport>
</template>

<script setup>
import { computed, reactive, ref, watch } from 'vue';
import { useToast } from '../../shared/composables/useToast.js';
import { createCategory, updateCategory, getCategory } from '../services/adminGuideCategoryService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  category: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.category?.id);

const form = reactive({
  name: '',
  slug: '',
  description: '',
  icon: '',
  sort_order: 0,
  status: 'active',
});

const saving = ref(false);
const formError = ref(null);

function slugify(s) {
  return String(s)
    .trim()
    .toLowerCase()
    .replace(/[^a-z0-9]+/g, '-')
    .replace(/^-|-$/g, '');
}

function maybeSlugFromName() {
  if (!isEdit.value) form.slug = slugify(form.name);
}

function resetForm() {
  form.name = '';
  form.slug = '';
  form.description = '';
  form.icon = '';
  form.sort_order = 0;
  form.status = 'active';
  formError.value = null;
}

function assignCategory(c) {
  if (!c) {
    resetForm();
    return;
  }
  form.name = c.name ?? '';
  form.slug = c.slug ?? '';
  form.description = c.description ?? '';
  form.icon = c.icon ?? '';
  form.sort_order = c.sort_order ?? 0;
  form.status = c.status ?? 'active';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.category],
  async () => {
    if (props.modelValue) {
      if (props.category?.id) {
        try {
          const full = await getCategory(props.category.id);
          assignCategory(full);
        } catch {
          assignCategory(props.category);
        }
      } else {
        assignCategory(null);
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    name: form.name.trim(),
    slug: form.slug.trim() || slugify(form.name),
    description: form.description?.trim() || null,
    icon: form.icon?.trim() || null,
    sort_order: form.sort_order,
    status: form.status,
  };
  try {
    if (isEdit.value) {
      await updateCategory(props.category.id, payload);
    } else {
      await createCategory(payload);
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
    toast.error(formError.value);
  } finally {
    saving.value = false;
  }
}
</script>
