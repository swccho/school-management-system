<template>
  <Teleport to="body">
    <div
      v-if="modelValue"
      class="fixed inset-0 z-50 flex items-center justify-center p-4"
      @click.self="emit('close')"
    >
      <div class="absolute inset-0 bg-zinc-900/50" aria-hidden="true" />
      <div
        class="sidenav-scroll relative max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-zinc-200 bg-white p-6 shadow-xl dark:border-zinc-800 dark:bg-zinc-900"
        role="dialog"
        aria-modal="true"
        :aria-labelledby="isEdit ? 'edit-event-title' : 'add-event-title'"
      >
        <h2
          :id="isEdit ? 'edit-event-title' : 'add-event-title'"
          class="text-lg font-semibold text-zinc-900 dark:text-zinc-100"
        >
          {{ isEdit ? 'Edit event' : 'Add event' }}
        </h2>

        <form class="mt-4 space-y-4" @submit.prevent="handleSubmit">
          <p
            v-if="formError"
            class="rounded-lg bg-red-50 px-3 py-2 text-sm text-red-700 dark:bg-red-900/20 dark:text-red-400"
          >
            {{ formError }}
          </p>

          <div>
            <label for="event-title" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Title <span class="text-red-500">*</span></label>
            <input
              id="event-title"
              v-model="form.title"
              type="text"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div>
            <SearchableSelect
              id="event-category"
              v-model="form.category_id"
              label="Category"
              :options="categories"
              label-key="name"
              value-key="id"
              placeholder="Select category (optional)"
              search-placeholder="Search categories…"
              clearable
            />
          </div>

          <div>
            <label for="event-summary" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Summary</label>
            <input
              id="event-summary"
              v-model="form.summary"
              type="text"
              maxlength="500"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Brief summary (optional)"
            />
          </div>

          <div>
            <label for="event-description" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Description <span class="text-red-500">*</span></label>
            <textarea
              id="event-description"
              v-model="form.description"
              rows="4"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            />
          </div>

          <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
              <label for="event-start" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Start date & time <span class="text-red-500">*</span></label>
              <input
                id="event-start"
                v-model="form.start_datetime"
                type="datetime-local"
                required
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
            <div>
              <label for="event-end" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">End date & time <span class="text-red-500">*</span></label>
              <input
                id="event-end"
                v-model="form.end_datetime"
                type="datetime-local"
                required
                class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              />
            </div>
          </div>

          <div>
            <label for="event-location" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Location</label>
            <input
              id="event-location"
              v-model="form.location"
              type="text"
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
              placeholder="Venue or address (optional)"
            />
          </div>

          <div>
            <label for="event-status" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Status <span class="text-red-500">*</span></label>
            <select
              id="event-status"
              v-model="form.status"
              required
              class="mt-1 block w-full rounded-lg border border-zinc-300 bg-white px-3 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-100"
            >
              <option value="draft">Draft</option>
              <option value="published">Published</option>
              <option value="archived">Archived</option>
            </select>
          </div>

          <div class="flex items-center gap-2">
            <input
              id="event-featured"
              v-model="form.is_featured"
              type="checkbox"
              class="h-4 w-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-500 dark:border-zinc-600 dark:bg-zinc-800"
            />
            <label for="event-featured" class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured</label>
          </div>

          <div>
            <label for="event-featured-image" class="block text-sm font-medium text-zinc-700 dark:text-zinc-300">Featured image</label>
            <input
              id="event-featured-image"
              type="file"
              accept="image/jpeg,image/png,image/gif,image/webp"
              class="mt-1 block w-full text-sm text-zinc-600 file:mr-2 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
              @change="onFeaturedImageChange"
            />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">JPEG, PNG, GIF, WebP. Max 5MB.</p>
            <p v-if="existingImageUrl" class="mt-1 text-xs text-zinc-600 dark:text-zinc-300">
              Current: <a :href="existingImageUrl" target="_blank" rel="noopener noreferrer" class="underline">View</a>
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
import SearchableSelect from '../../shared/components/form/SearchableSelect.vue';
import { useToast } from '../../shared/composables/useToast.js';
import { create, update, getOne } from '../services/eventService.js';

const toast = useToast();

const props = defineProps({
  modelValue: { type: Boolean, default: false },
  event: { type: Object, default: null },
  categories: { type: Array, default: () => [] },
});

const emit = defineEmits(['update:modelValue', 'close', 'saved']);

const isEdit = computed(() => !!props.event?.id);

const form = reactive({
  title: '',
  summary: '',
  description: '',
  category_id: null,
  start_datetime: '',
  end_datetime: '',
  location: '',
  status: 'draft',
  is_featured: false,
});

const saving = ref(false);
const formError = ref(null);
const featuredImageFile = ref(null);
const existingImageUrl = ref(null);

function toDatetimeLocal(iso) {
  if (!iso) return '';
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return '';
  const pad = (n) => String(n).padStart(2, '0');
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function resetForm() {
  form.title = '';
  form.summary = '';
  form.description = '';
  form.category_id = null;
  form.start_datetime = '';
  form.end_datetime = '';
  form.location = '';
  form.status = 'draft';
  form.is_featured = false;
  formError.value = null;
  featuredImageFile.value = null;
  existingImageUrl.value = null;
}

function assignEvent(e) {
  if (!e) {
    resetForm();
    return;
  }
  form.title = e.title ?? '';
  form.summary = e.summary ?? '';
  form.description = e.description ?? '';
  form.category_id = e.category_id ?? null;
  form.start_datetime = toDatetimeLocal(e.start_datetime);
  form.end_datetime = toDatetimeLocal(e.end_datetime);
  form.location = e.location ?? '';
  form.status = e.status ?? 'draft';
  form.is_featured = !!e.is_featured;
  formError.value = null;
  featuredImageFile.value = null;
  existingImageUrl.value = e.featured_image_url ?? null;
}

function onFeaturedImageChange(ev) {
  const file = ev.target.files?.[0];
  featuredImageFile.value = file ?? null;
}

watch(
  () => [props.modelValue, props.event],
  async () => {
    if (props.modelValue) {
      if (props.event?.id) {
        try {
          const full = await getOne(props.event.id);
          assignEvent(full);
        } catch {
          assignEvent(props.event);
        }
      } else {
        assignEvent(null);
      }
    }
  },
  { immediate: true }
);

async function handleSubmit() {
  saving.value = true;
  formError.value = null;
  const payload = {
    title: form.title.trim(),
    description: form.description.trim(),
    start_datetime: form.start_datetime || null,
    end_datetime: form.end_datetime || null,
    status: form.status,
    category_id: form.category_id || null,
    summary: form.summary?.trim() || null,
    location: form.location?.trim() || null,
    is_featured: form.is_featured,
  };
  if (featuredImageFile.value) payload.featured_image = featuredImageFile.value;

  try {
    if (isEdit.value) {
      await update(props.event.id, payload);
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
