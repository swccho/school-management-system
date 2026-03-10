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
        :aria-labelledby="isEdit ? 'edit-section-title' : 'add-section-title'"
      >
        <h2
          :id="isEdit ? 'edit-section-title' : 'add-section-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit section' : 'Add section' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="section-key" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Section key <span class="text-red-500">*</span></label>
            <input
              id="section-key"
              v-model="form.section_key"
              type="text"
              required
              :readonly="isEdit"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="e.g. welcome, features"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">Unique identifier. Cannot be changed after create.</p>
          </div>

          <div>
            <label for="section-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title</label>
            <input
              id="section-title"
              v-model="form.title"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="section-subtitle" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Subtitle</label>
            <input
              id="section-subtitle"
              v-model="form.subtitle"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Optional"
            />
          </div>

          <div>
            <label for="section-content" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Content</label>
            <textarea
              id="section-content"
              v-model="form.content"
              rows="4"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Text or JSON (optional)"
            />
          </div>

          <div>
            <label for="section-sort-order" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Sort order</label>
            <input
              id="section-sort-order"
              v-model.number="form.sort_order"
              type="number"
              min="0"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div class="flex items-center gap-2">
            <input
              id="section-visible"
              v-model="form.is_visible"
              type="checkbox"
              class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
            />
            <label for="section-visible" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Visible on homepage</label>
          </div>

          <div>
            <label for="section-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="section-status"
              v-model="form.status"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
            </select>
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
import { create, update, getOne } from '../services/homepageSectionService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  section: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.section?.id);

const form = reactive({
  section_key: '',
  title: '',
  subtitle: '',
  content: '',
  sort_order: 0,
  is_visible: true,
  status: 'active',
});

const saving = ref(false);
const formError = ref(null);

function resetForm() {
  form.section_key = '';
  form.title = '';
  form.subtitle = '';
  form.content = '';
  form.sort_order = 0;
  form.is_visible = true;
  form.status = 'active';
  formError.value = null;
}

function assignSection(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.section_key = s.section_key ?? '';
  form.title = s.title ?? '';
  form.subtitle = s.subtitle ?? '';
  form.content = s.content ?? '';
  form.sort_order = s.sort_order ?? 0;
  form.is_visible = !!s.is_visible;
  form.status = s.status ?? 'active';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.section],
  async () => {
    if (props.modelValue) {
      if (props.section?.id) {
        try {
          const full = await getOne(props.section.id);
          assignSection(full);
        } catch {
          assignSection(props.section);
        }
      } else {
        assignSection(null);
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    section_key: form.section_key.trim(),
    title: form.title?.trim() || null,
    subtitle: form.subtitle?.trim() || null,
    content: form.content?.trim() || null,
    sort_order: form.sort_order,
    is_visible: form.is_visible,
    status: form.status,
  };

  try {
    if (isEdit.value) {
      await update(props.section.id, payload);
    } else {
      await create(payload);
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
