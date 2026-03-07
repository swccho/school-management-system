<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="relative w-full max-w-md rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
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
            <SearchableSelect
              id="section-class"
              v-model="form.class_id"
              label="Class"
              :options="classes"
              label-key="name"
              value-key="id"
              placeholder="Select class"
              search-placeholder="Search classes…"
              required
              :loading="classesLoading"
              loading-text="Loading classes…"
            />
            <p v-if="classes.length === 0 && !classesLoading" class="mt-1 text-xs text-amber-600 dark:text-amber-400">
              No classes found. Add classes first.
            </p>
          </div>
          <div>
            <label for="section-name" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Section name</label>
            <input
              id="section-name"
              v-model="form.name"
              type="text"
              required
              placeholder="e.g. A, B, Science"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div>
            <label for="section-code" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Code</label>
            <input
              id="section-code"
              v-model="form.code"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>
          <div class="grid grid-cols-2 gap-4">
            <div>
              <label for="section-room" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Room no.</label>
              <input
                id="section-room"
                v-model="form.room_no"
                type="text"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="section-capacity" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Capacity</label>
              <input
                id="section-capacity"
                v-model.number="form.capacity"
                type="number"
                min="0"
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>
          <div>
            <label for="section-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status</label>
            <select
              id="section-status"
              v-model="form.status"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="active">Active</option>
              <option value="inactive">Inactive</option>
              <option value="archived">Archived</option>
            </select>
          </div>
          <div>
            <label for="section-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description</label>
            <textarea
              id="section-description"
              v-model="form.description"
              rows="2"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
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
              :disabled="saving || classes.length === 0"
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
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { getClasses } from '../services/classService.js';
import { createSection, updateSection } from '../services/sectionService.js';

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  section: { type: Object, default: null },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.section);

const form = reactive({
  class_id: '',
  name: '',
  code: '',
  room_no: '',
  capacity: null,
  status: 'active',
  description: '',
});

const saving = ref(false);
const formError = ref(null);
const classes = ref([]);
const classesLoading = ref(false);

async function loadClasses() {
  classesLoading.value = true;
  try {
    classes.value = await getClasses();
  } catch {
    classes.value = [];
  } finally {
    classesLoading.value = false;
  }
}

function resetForm() {
  form.class_id = '';
  form.name = '';
  form.code = '';
  form.room_no = '';
  form.capacity = null;
  form.status = 'active';
  form.description = '';
  formError.value = null;
}

function assignSection(s) {
  if (!s) {
    resetForm();
    return;
  }
  form.class_id = s.class_id ?? '';
  form.name = s.name ?? '';
  form.code = s.code ?? '';
  form.room_no = s.room_no ?? '';
  form.capacity = s.capacity ?? null;
  form.status = s.status ?? 'active';
  form.description = s.description ?? '';
  formError.value = null;
}

watch(
  () => [props.modelValue, props.section],
  async () => {
    if (props.modelValue) {
      await loadClasses();
      assignSection(props.section);
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    class_id: Number(form.class_id),
    name: form.name,
    code: form.code || null,
    room_no: form.room_no || null,
    capacity: form.capacity === '' || form.capacity == null ? null : Number(form.capacity),
    status: form.status,
    description: form.description || null,
  };
  try {
    if (isEdit.value) {
      await updateSection(props.section.id, payload);
    } else {
      await createSection(payload);
    }
    emit('saved');
    emit('update:modelValue', false);
    emit('close');
  } catch (err) {
    const msg = err.response?.data?.message;
    const errors = err.response?.data?.errors;
    formError.value = msg || (errors ? Object.values(errors).flat().join(' ') : 'Something went wrong.');
  } finally {
    saving.value = false;
  }
}
</script>
